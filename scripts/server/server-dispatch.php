<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kumbia@kumbia.org so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	Scripts
 * @subpackage 	Server
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: server-dispatch.php,v d8d377792e3b 2011/10/20 22:57:30 andres $
 */

/**
 * Excepción generada por HurricaneServer
 *
 */
class HurricaneServerException extends Exception {

}

class HurricaneServer {

	/**
	 * PHP5.3 Garbage Collector Enabled
	 *
	 * @var boolean
	 */
	private static $_gcEnabled = false;

	/**
	 * Ultima aplicacion en donde se ejecutó una petición
	 */
	private static $_lastApplication = '';

	/**
	 * Indica si ya se inicializó el framework
	 *
	 * @var boolean
	 */
	private static $_frameworkInitialized = false;

	/**
	 * HTTP Status General
	 *
	 * @var string
	 */
	private static $_responseStatus;

	/**
	 * Contenido y tamaño de la salida dinámica
	 *
	 * @var array
	 */
	private static $_dynamic = array();

	/**
	 * Encabezados HTTP de respuesta base
	 *
	 * @var array
	 */
	private static $_responseHeaders = array(
		'Server'  => 'HurricaneServer/0.1'
	);

	/**
	 * MIME types base
	 *
	 * @var array
	 */
	private static $_mimeTypes = array(
		'html' => 'text/html; charset=UTF-8',
		'htm' => 'text/html; charset=UTF-8',
		'css' => 'text/css',
		'js' => 'application/x-javascript',
		'gif' => 'image/gif',
		'png' => 'image/png',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'ico' => 'image/x-icon',
		'swf' => 'application/x-shockwave-flash',
		'xhtml' => 'application/xhtml+xml'
	);

	/**
	 * Asociación de encabezados de la petición con superglobal $_SERVER
	 *
	 * @var array
	 */
	private static $_serverRelationship = array(
		'User-Agent' => 'HTTP_USER_AGENT',
		'X-Requested-With' => 'HTTP_X_REQUESTED_WITH',
		'Accept' => 'HTTP_ACCEPT',
		'Accept-Encoding' => 'HTTP_ACCEPT_ENCODING',
		'Accept-Language' => 'HTTP_ACCEPT_LANGUAGE',
		'Referer' => 'HTTP_REFERER'
	);

	public static function initialize(){
		self::_setTimezone();
		self::_loadComponents();
	}

	/**
	 * Establece la zona horaria de la aplicación
	 *
	 */
	private static function _setTimezone(){
		date_default_timezone_set('America/Bogota');
	}

	/**
	 * Carga componentes del framework para que esten disponibles en cualquier momento
	 *
	 */
	private static function _loadComponents(){
		require 'public/index.config.php';
		require KEF_ABS_PATH.'Library/Kumbia/Autoload.php';
		require KEF_ABS_PATH.'Library/Kumbia/Object.php';
		require KEF_ABS_PATH.'Library/Kumbia/Core/Core.php';
		require KEF_ABS_PATH.'Library/Kumbia/Session/Session.php';
		require KEF_ABS_PATH.'Library/Kumbia/Config/Config.php';
		require KEF_ABS_PATH.'Library/Kumbia/Core/Config/CoreConfig.php';
		require KEF_ABS_PATH.'Library/Kumbia/Core/Type/CoreType.php';
		require KEF_ABS_PATH.'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
		require KEF_ABS_PATH.'Library/Kumbia/Router/Router.php';
		require KEF_ABS_PATH.'Library/Kumbia/Plugin/Plugin.php';
		require KEF_ABS_PATH.'Library/Kumbia/Registry/Memory/MemoryRegistry.php';
		require KEF_ABS_PATH.'Library/Kumbia/Config/Adapters/Ini.php';
		require KEF_ABS_PATH.'Library/Kumbia/Router/Interface.php';
		require KEF_ABS_PATH.'Library/Kumbia/Router/Adapters/Default.php';
		require KEF_ABS_PATH.'Library/Kumbia/CommonEvent/CommonEvent.php';
		require KEF_ABS_PATH.'Library/Kumbia/Dispatcher/Dispatcher.php';
		require KEF_ABS_PATH.'Library/Kumbia/EntityManager/EntityManager.php';
		require KEF_ABS_PATH.'Library/Kumbia/Transactions/TransactionManager.php';
		require KEF_ABS_PATH.'Library/Kumbia/Db/Loader/DbLoader.php';
		require KEF_ABS_PATH.'Library/Kumbia/Db/DbBase.php';
		require KEF_ABS_PATH.'Library/Kumbia/ActiveRecord/Base/ActiveRecordBase.php';
		require KEF_ABS_PATH.'Library/Kumbia/Security/Security.php';
		require KEF_ABS_PATH.'Library/Kumbia/Facility/Facility.php';
		require KEF_ABS_PATH.'Library/Kumbia/View/View.php';
		require KEF_ABS_PATH.'Library/Kumbia/i18n/i18n.php';
		require KEF_ABS_PATH.'Library/Kumbia/Controller/ControllerResponse.php';
		require KEF_ABS_PATH.'Library/Kumbia/Utils/Utils.php';
	}

	private static function getRfc22date($time){
		if(PHP_OS=='WINNT'){
			return date('r');
		} else {
			return gmstrftime("%a, %d %b %Y %T %Z", $time);
		}
	}

	public static function serveRequest($requestHeaders, $requestHttpUri, $requestInput){

		$requestTime = time();

		//Encabezados de salida
		self::$_responseHeaders['Date'] = self::getRfc22date($requestTime);

		$staticContent = false;
		$uri = $requestHttpUri[1];
		if($uri!='/'){
			$existsFile = file_exists(KEF_ABS_PATH.'public'.$uri);
			if($existsFile){
				if(preg_match('/\.([a-z0-9]+)$/', $uri, $matches)){
					if($matches[1]!='php'){
						self::$_responseHeaders['Accept-Ranges'] = 'bytes';
						self::$_responseHeaders['Content-Length'] = filesize(KEF_ABS_PATH.'public'.$uri);
						if(isset(self::$_mimeTypes[$matches[1]])){
							self::$_responseHeaders['Content-Type'] = self::$_mimeTypes[$matches[1]];
						} else {
							self::$_responseHeaders['Content-Type'] = 'text/plain';
						}
						$lastModified = filemtime(KEF_ABS_PATH.'public'.$uri);
						self::$_responseHeaders['Last-Modified'] = self::getRfc22date($lastModified);
						self::$_responseHeaders['Expires'] = self::getRfc22date($requestTime+1728000);
						self::$_responseHeaders['Etag'] = "ci-".dechex(crc32($uri.$lastModified));
						$staticContent = true;
					} else {
						self::_serveDynamicContent($uri);
					}
					unset($matches);
				}
			}
		}

		if($staticContent==false){

			$_GET['_url'] = substr($uri, 1);
			$_SERVER['SERVER_SOFTWARE'] = 'HurricaneServer/0.2';
			$_SERVER['REQUEST_METHOD'] = $requestHttpUri[0];
			$_SERVER['REQUEST_TIME'] = $requestTime;
			$_SERVER['DOCUMENT_ROOT'] = KEF_ABS_PATH;

			if(isset($requestInput['GET'])){
				foreach($requestInput['GET'] as $key => $value){
					$_GET[$key] = $value;
				}
			}

			if(isset($requestInput['POST'])){
				foreach($requestInput['POST'] as $key => $value){
					$_POST[$key] = $value;
				}
			}

			if(isset($requestInput['REQUEST'])){
				foreach($requestInput['REQUEST'] as $key => $value){
					$_REQUEST[$key] = $value;
				}
			}

			//Examine Cookies
			if(isset($requestHeaders['Cookie'])){
				$cookies = explode(';', $requestHeaders['Cookie']);
				foreach($cookies as $cookie){
					$cookiePart = explode('=', $cookie);
					$_COOKIE[$cookiePart[0]] = $cookiePart[1];
				}
			}

			if(isset($_COOKIE['PHPSESSID'])){
				$phpSessid = $_COOKIE['PHPSESSID'];
				$sessionPath = KEF_ABS_PATH.'scripts/server/tmp/'.$phpSessid;
				if(file_exists($sessionPath)){
					require $sessionPath;
					if(isset($session)){
						foreach($session as $key => $value){
							$_SESSION[$key] = $value;
						}
					}
				}
			} else {
				$_COOKIE['PHPSESSID'] = md5(microtime(true));
			}

			self::_serveAppRequest();
		}

		//Escribir estado HTTP
		if(isset($requestHeaders['If-Modified-Since'])){
			echo "HTTP/1.1 304 Not Modified\r\n";
		} else {
			echo "HTTP/1.1 200 OK\r\n";
		}

		//Escribir encabezados de la respuesta
		foreach(self::$_responseHeaders as $headerName => $headerValue){
			echo $headerName, ': ', $headerValue, "\r\n";
		}

		//Escribir Cookies
		$cookies = array();
		foreach($_COOKIE as $key => $value){
			$cookies[] = $key.'='.$value;
		}
		if(count($cookies)){
			echo 'Cookie: ', join(';', $cookies), "\r\n";
		}
		echo "\r\n";

		if($staticContent==false){
			if(isset(self::$_dynamic['content'])){
				echo self::$_dynamic['content'];
			}
			if(isset($_COOKIE['PHPSESSID'])){
				$phpSessid = $_COOKIE['PHPSESSID'];
				$sessionPath = KEF_ABS_PATH.'scripts/server/tmp/'.$phpSessid;
				file_put_contents($sessionPath, '<?php $session = '.var_export($_SESSION, true).';');
			}
		} else {
			if(!isset($requestHeaders['If-Modified-Since'])){
				self::_serveStaticContent($uri);
			}
		}

	}

	/**
	 * Sirve un archivo estático que se encuentra en el public de manera segura
	 *
	 * @param string $filePath
	 */
	private static function _serveStaticContent($filePath){
		readfile(KEF_ABS_PATH.'public'.$filePath);
	}

	/**
	 * Sirve un contenido dinámico en una caja de arena
	 *
	 * @param string $uri
	 */
	private static function _serveDynamicContent($uri){
		self::$_responseHeaders['Content-Type'] = 'text/html; charset=UTF-8';
		self::$_responseHeaders['Cache-Control'] = 'no-cache';
		ob_start();
		include 'public'.$uri;
		self::$_dynamic['content'] = ob_get_contents();
		self::$_responseHeaders['Content-Length'] = strlen(self::$_dynamic['content']);
		ob_end_clean();
	}

	/**
	 * Sirve una petición a una aplicación
	 *
	 * @return
	 */
	private static function _serveAppRequest(){
		self::$_responseHeaders['Content-Type'] = 'text/html; charset=UTF-8';
		self::$_responseHeaders['Cache-Control'] = 'no-cache';
		//self::$_responseHeaders['Keep-Alive'] =	'timeout=15, max=100';
		//self::$_responseHeaders['Connection'] =	'Keep-Alive';
		ob_start();
		try {

			Router::handleRouterParameters();
			PluginManager::loadApplicationPlugins();
			Core::setIsHurricane(true);
			Core::setTimeZone();
			Core::fastMain();

		}
		catch(CoreException $e){
			try {
				Session::startSession();
				$exceptionHandler = Core::determineExceptionHandler();
				call_user_func_array($exceptionHandler, array($e, null));
			}
			catch(Exception $e){
				ob_start();
				echo get_class($e).': '.$e->getMessage()." ".$e->getFile()."(".$e->getLine().")";
				echo 'Backtrace', "\n";
				foreach($e->getTrace() as $debug){
					echo $debug['file'].' ('.$debug['line'].") <br/>\n";
				}
				View::setContent(ob_get_contents());
				ob_end_clean();
				View::xhtmlTemplate('white');
			}
		}
		catch(Exception $e){
			echo 'Exception: '.$e->getMessage();
			foreach(debug_backtrace() as $debug){
				echo $debug['file'].' ('.$debug['line'].") <br>\n";
			}
		}

		//Eliminar salidas que no se hayan terminado
		$obStatus = ob_get_status(true);
		$numObStatus = count($obStatus);
		if($numObStatus>1){
			for($i=0;$i<($numObStatus-1);$i++){
				ob_end_flush();
			}
		}

		//Cargar salida para su envio al socket
		self::$_dynamic['content'] = ob_get_contents();
		self::$_responseHeaders['Content-Length'] = strlen(self::$_dynamic['content']);
		ob_end_clean();

	}

	/**
	 * Cambia un encabezado de la salida externamente
	 *
	 * @param string $name
	 * @param string $value
	 */
	public static function setHeader($name, $value){
		self::$_responseHeaders[$name] = $value;
	}

	/**
	 * Devuelve un encabezado previamente definido
	 *
	 * @param	string $name
	 * @return	null
	 */
	public static function getHeader($name){
		if(isset(self::$_responseHeaders[$name])){
			return self::$_responseHeaders[$name];
		} else {
			return null;
		}
	}

}

HurricaneServer::initialize();
HurricaneServer::serveRequest($rh, $uri, $inp);
