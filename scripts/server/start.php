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
 * @version 	$Id$
 */

//Establece tipo de notificacion de errores
error_reporting(E_ALL | E_NOTICE | E_STRICT);

require 'public/index.config.php';
require KEF_ABS_PATH.'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
require KEF_ABS_PATH.'Library/Kumbia/Autoload.php';

/**
 * HurricaneServerDispatchException
 *
 * Excepción generada por HurricaneServerDispatch
 */
class HurricaneServerDispatchException extends CoreException {

}

/**
 * HurricaneProcess
 *
 * Crea un subproceso para hacer el dispatch de la peticiòn
 */
class HurricaneProcess {

	const STATE_RUNNING = 0;

	const STATE_SLEEPING = 1;

	private $_process;

	private $_state = 0;

	private $_pipes = array();

	private $_processPid;

	public function __construct($processPid){
		$descriptors = array(
		   0 => array('pipe', 'r'),
		   1 => array('pipe', 'w'),
		   2 => array('file', 'server'.$processPid.'.log', 'a')
		);
		$this->_processPid = $processPid;
		if(isset($_SERVER['_'])){
			$cwd = null;
			$phpPath = $_SERVER['_'];
		} else {
			$cwd = KEF_ABS_PATH;
			$phpPath = HURRICANE_PHP_PATH;
			unset($descriptors[2]);
		}
		$this->_process = @proc_open($phpPath, $descriptors, $this->_pipes, $cwd, array());
		if($this->_process===false){
			throw new HurricaneServerDispatchException('No se pudo iniciar el subproceso de PHP');
		}
		$this->_state = self::STATE_SLEEPING;
	}

	public function getPid(){
		return $this->_processPid;
	}

	/**
	 * Establece el estado del proceso
	 *
	 * @param int $state
	 */
	public function setState($state){
		$this->_state = $state;
	}

	/**
	 * Devuelve el estado del proceso
	 *
	 * @return int
	 */
	public function getState(){
		return $this->_state;
	}

	public function getPipes(){
		return $this->_pipes;
	}

	public function getStatus(){
		print_r(proc_get_status($this->_process));
	}

	public function close(){
		proc_close($this->_process);
	}

}

/**
 * HurricaneThread
 *
 * Corre un proceso paralelo de dispatch para peticiones
 *
 */
class HurricaneThread {

    protected $_callback;

    private $_pid;

    public function __construct($callback){
    	if(is_callable($callback)){
			$this->_callback = $callback;
    	} else {
			throw new HurricaneServerDispatchException('El callback del Thread no es valido');
    	}
    }

    public function getPid(){
        return $this->_pid;
    }

    public function isAlive(){
        $pid = pcntl_waitpid($this->_pid, $status, WNOHANG);
        return ($pid===0);
    }

    public function start(){
        $pid = @pcntl_fork();
        if($pid==-1){
            throw new Exception('No se pudo realizar el fork al proceso');
        }
        if($pid){
            $this->_pid = $pid;
        } else {
            pcntl_signal(SIGTERM, array($this, 'signalHandler'));
            $arguments = func_get_args();
			call_user_func_array($this->_callback, $arguments);
            exit(0);
        }
    }

    public function stop($signal = SIGKILL, $wait=false){
        if($this->isAlive()){
            posix_kill($this->pid, $signal );
            if($wait){
                pcntl_waitpid($this->pid, $status = 0);
            }
        }
    }

    protected function signalHandler($signal){
        switch($_signal){
            case SIGTERM:
                exit(0);
            break;
        }
    }
}

/**
 * HurricaneServerDispatch
 *
 * HurricaneServerDispatch está escrito en PHP y proporciona servicios básicos de HTTP
 * para probar y desarrollar en Kumbia Enterprise Framework
 *
 */
class HurricaneServerDispatch {

	/**
	 * Dirección donde va a escuchar el servidor
	 *
	 * @var string
	 */
	private static $_address = '0.0.0.0';

	/**
	 * Puerto TCP donde va a escuchar el servidor
	 *
	 * @var unknown_type
	 */
	private static $_port = 2000;

	/**
	 * Indica si el servidor está en modo debug
	 *
	 * @var boolean
	 */
	private static $_debug = false;

	/**
	 * Socket TCP del servidor
	 *
	 * @var resource
	 */
	private static $_socket;

	/**
	 * Autonumerico Id de conexión
	 *
	 * @var int
	 */
	private static $_connectionId = 0;

	/**
	 * Lista de sockets activos
	 *
	 * @var array
	 */
	private static $_connections = array();

	/**
	 * Inicializa el servidor web
	 *
	 * @param string $address
	 * @param int $port
	 * @param boolean $debug
	 */
	public static function initialize($address, $port, $debug){
		if($address){
			self::$_address = $address;
		}
		if($port){
			self::$_port = $port;
		}
		self::$_debug = $debug;
		self::_bindAddress();
		self::startServer();
	}

	/**
	 * Registra funciones de terminación y señales de cerrar
	 *
	 */
	private static function _registerShutdownSignals(){
		//Apagar servidor correctamente
		register_shutdown_function(array('HurricaneServerDispatch', 'shutdownServer'));
	}

	/**
	 * Abre el puerto y empieza a escuchar peticiones en él
	 *
	 * @access public
	 * @static
	 */
	private static function _bindAddress(){

		if(!extension_loaded('sockets')){
			throw new HurricaneServerDispatchException('Debe cargar la extensión de PHP llamada php_sockets');
		}

		self::$_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(self::$_socket===false){
			throw new HurricaneServerDispatchException('socket_create() failed: reason: '.socket_strerror(socket_last_error()));
		}

		if(!socket_set_option(self::$_socket, SOL_SOCKET, SO_REUSEADDR, 1)){
			throw new HurricaneServerDispatchException(socket_strerror(socket_last_error(self::$_socket)));
		}

		if(socket_bind(self::$_socket, self::$_address, self::$_port)===false){
			throw new HurricaneServerDispatchException('socket_bind() failed: reason: '.socket_strerror(socket_last_error(self::$_socket)));
		}

		if(socket_listen(self::$_socket, 128)===false){
			throw new HurricaneServerDispatchException('socket_listen() failed: reason: '.socket_strerror(socket_last_error(self::$_socket)));
		}

		socket_set_nonblock(self::$_socket);

		//Mensaje de bienvenida
		echo 'HurricaneServer listening on '.self::$_address.' '.self::$_port, PHP_EOL;

		//Registra SIGNAL handlers para la terminación correcta de procesos
		self::_registerShutdownSignals();

	}

	/**
	 * Inicia los sockets del servidor y sus subprocesos
	 *
	 */
	public static function startServer(){
		if(self::$_debug==true){
			echo 'Server ready for connections', PHP_EOL;
		}
		while(true){
			$clientSocket = @socket_accept(self::$_socket);
			if($clientSocket===false){
				usleep(100);
	        } else {
	        	if(self::$_debug==true){
	        		echo 'Acepted connection...', PHP_EOL;
	        	}
	        	self::serverAccept($clientSocket);
	        	socket_close($clientSocket);
	        }
		}
	}

	/**
	 * Apaga el servidor cerrando sockets abiertos de forma segura
	 *
	 * @access 	public
	 * @param 	int $signal
	 * @static
	 */
	public static function shutdownServer($signal=null){
		@socket_close(self::$_socket);
		//if(proc_close($process);)
	}

	/**
	 * Acepta peticiones en la cola leyendo el socket TCP estandar
	 *
	 * @param resource $socketClient
	 */
	public static function serverAccept($clientSocket){
		$i = 0;
		$line = '';
		$requestHeaders = array();
		while(true){
			unset($buffer);
			$buffer = socket_read($clientSocket, 1, PHP_BINARY_READ);
			if($buffer=="\n"){
				if($line=="\r"){
					break;
				}
				if($i==0){
					$requestHttpUri = explode(' ', $line);
					unset($fline);
				} else {
					$fline = explode(': ', $line, 2);
					if(count($fline)==2){
						$requestHeaders[$fline[0]] = substr($fline[1], 0, strlen($fline[1])-1);
					}
					unset($fline);
				}
				unset($line);
				$line = '';
				$i++;
			} else {
				$line.=$buffer;
			}
		}
		self::_dispatchServer($clientSocket, $requestHttpUri, $requestHeaders);
	}

	private static function _dispatchServer($clientSocket, $requestHttpUri, $requestHeaders){
		if(function_exists('pcntl_fork')){
			$thread = new HurricaneThread(array('HurricaneServerDispatch', 'dispatch'));
			$thread->start($clientSocket, $requestHttpUri, $requestHeaders);
		} else {
			self::dispatch($clientSocket, $requestHttpUri, $requestHeaders);
		}
	}

	public static function dispatch($clientSocket, $requestHttpUri, $requestHeaders){

		$request = array(
			'GET' => array(),
			'POST' => array()
		);

		//Leer cuerpo POST del mensaje
		if($requestHttpUri[0]=='POST'){
			$requestLength = $requestHeaders['Content-Length'];
			$postBody = '';
			for($i=0;$i<$requestLength;++$i){
				$postBody.=socket_read($clientSocket, 1, PHP_BINARY_READ);
			}
			foreach(explode('&', $postBody) as $variable){
				$pvariable = explode('=', $variable);
				if(isset($pvariable[1])){
					$request['POST'][$pvariable[0]] = urldecode($pvariable[1]);
					$request['REQUEST'][$pvariable[0]] = urldecode($pvariable[1]);
				} else {
					$request['POST'][$pvariable[0]] = null;
					$request['REQUEST'][$pvariable[0]] = null;
				}
			}
		}

		//Query-String
		$ppos = strpos($requestHttpUri[1], '?');
		if($ppos!==false){
			$uri = substr($requestHttpUri[1], 0, $ppos);
			$queryString = substr($requestHttpUri[1], $ppos+1);
			foreach(explode('&', $queryString) as $variable){
				$pvariable = explode('=', $variable);
				if(isset($pvariable[1])){
					$request['GET'][$pvariable[0]] = urldecode($pvariable[1]);
					$request['REQUEST'][$pvariable[0]] = urldecode($pvariable[1]);
				} else {
					$request['GET'][$pvariable[0]] = null;
					$request['REQUEST'][$pvariable[0]] = null;
				}
			}
		} else {
			$uri = $requestHttpUri[1];
		}

		$process = new HurricaneProcess(0);
		$pipes = $process->getPipes();

		if(self::$_debug==true){
			$time = microtime(true);
		}

		if(PHP_OS=='WINNT'){
			$program ='<?php chdir("'.str_replace('\\', '\\\\', KEF_ABS_PATH).'"); $rh = array();';
		} else {
			$program ='<?php chdir("'.KEF_ABS_PATH.'"); $rh = array();';
		}
		foreach($requestHeaders as $name => $value){
			$program.='$rh["'.$name.'"] = "'.$value.'";'.PHP_EOL;
		}
		$program.='$inp = '.var_export($request, true).';';
		$program.='$uri = array("'.$requestHttpUri[0].'", "'.$uri.'");';
		$program.='require "scripts/server/server-dispatch.php";?>';

		if(self::$_debug==true){
			echo 'Writing to net...', PHP_EOL;
			file_put_contents('program.php', $program);
		}

		fwrite($pipes[0], $program);
    	fclose($pipes[0]);

    	if(self::$_debug==true){
			echo 'Reading from net...', PHP_EOL;
		}

    	$contents = stream_get_contents($pipes[1]);
    	fclose($pipes[1]);

    	if(self::$_debug==true){
			echo 'Writing to socket...', PHP_EOL;
		}

    	socket_write($clientSocket, $contents, strlen($contents));

    	if(self::$_debug==true){
    		echo $requestHttpUri[0], ' ', $uri, ' ', LocaleMath::round(microtime(true)-$time, 5), PHP_EOL;
    	}

    	$process->close();

	}


}

class HurricaneScript extends Script {

	public function run(){

		$posibleParameters = array(
			'address=s' => "--address ip \tDirección IPv4 donde se aceptaran conexiones [opcional]",
			'port=i' => "--port number \tPuerto deonde se aceptaran conexiones [opcional]",
			'debug' => "--debug \t\tIndica si se debe mostrar información de debug [opcional]",
			'help' => "--help \t\t\tMuestra esta ayuda"
		);

		$this->parseParameters($posibleParameters);

		if($this->isReceivedOption('help')){
			$this->showHelp($posibleParameters);
			return;
		}

		$address = $this->getOption('address');
		$port = $this->getOption('port');
		$debug = $this->isReceivedOption('debug');

		try {
			HurricaneServerDispatch::initialize($address, $port, $debug);
		}
		catch(HurricaneServerDispatchException $e){
			echo $e;
		}

	}
}

try {
	$script = new HurricaneScript();
	$script->run();
}
catch(CoreException $e){
	ScriptColor::lookSupportedShell();
	echo ScriptColor::colorize(get_class($e).' : '.$e->getConsoleMessage()."\n", ScriptColor::LIGHT_RED);
	if($script->getOption('debug')=='yes'){
		echo $e->getTraceAsString()."\n";
	}
}
catch(Exception $e){
	echo 'Exception : '.$e->getMessage()."\n";
}
