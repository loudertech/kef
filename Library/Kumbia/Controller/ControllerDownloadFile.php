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
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category	Kumbia
 * @package		Controller
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * ControllerUploadFile
 *
 * Esta clase permite forzar la descarga de archivos desde el servidor
 *
 * @category	Kumbia
 * @package		Controller
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class ControllerDownloadFile extends Object {

	/**
	 * Tipos MIME conocidos
	 *
	 * @var array
	 */
	private static $_mimeTypes = array(
	 	'pdf' => 'application/pdf',
	 	'txt' => 'text/plain',
	 	'html' => 'text/html',
	 	'htm' => 'text/html',
		'exe' => 'application/octet-stream',
		'zip' => 'application/zip',
		'doc' => 'application/msword',
		'xls' => 'application/vnd.ms-excel',
		'ppt' => 'application/vnd.ms-powerpoint',
		'gif' => 'image/gif',
		'png' => 'image/png',
		'jpeg'=> 'image/jpg',
		'jpg' =>  'image/jpg',
		'php' => 'text/plain'
 	);

 	/**
 	 * Mime Type del archivo a descargar
 	 *
 	 * @var string
 	 */
 	private $_mimeType;

 	/**
 	 * PATH al archivo a descargar
 	 *
 	 * @var string
 	 */
 	private $_pathToFile;

 	/**
 	 * Tamaño del archivo
 	 *
 	 * @var int
 	 */
 	private $_fileSize;

 	/**
 	 * Constructor de ControllerDownloadFile
 	 *
 	 * @param 	string $pathToFile
 	 * @param 	string $mimeType
 	 */
	public function __construct($pathToFile, $mimeType=''){
		$this->_pathToFile = $pathToFile;
		$this->_mimeType = $mimeType;
		if(file_exists($this->_pathToFile)==false){
			throw new ControllerException('El archivo a descargar no existe');
		}
		$this->_fileSize = filesize($this->_pathToFile);
	}

	/**
	 * Detectar el MIME del archivo
	 *
	 * @param	string $pathToFile
	 * @return	string
	 */
	private function _detectMimeType($pathToFile=''){
		if(function_exists('finfo_open')){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			return finfo_file($finfo, $pathToFile);
		} else {
			if(preg_match('/\.([a-zA-Z0-9]+)$/', $pathToFile, $matches)){
				$fileExtension = $matches[1];
				if(isset(self::$_mimeTypes[$fileExtension])){
					return self::$_mimeTypes[$fileExtension];
				}
				return 'application/octet-stream';
			}
		}
	}

	/**
	 * Envia el archivo al cliente para su descarga
	 *
	 */
	public function disposeToClient(){
		if($this->_mimeType==''){
			$this->_mimeType = $this->_detectMimeType($this->_pathToFile);
		}

		$controllerResponse = ControllerResponse::getInstance();
		$controllerResponse->setHeader('Content-Type', $this->_mimeType);
		$controllerResponse->setHeader('Content-Disposition', 'attachment; filename="'.basename($this->_pathToFile).'"');
		$controllerResponse->setHeader('Content-Transfer-Encoding', 'binary');
		$controllerResponse->setHeader('Accept-Ranges', 'bytes');

		$controllerResponse->setHeader('Pragma', 'private');
		$controllerResponse->setHeader('Cache-control', 'private');
		$controllerResponse->setHeader('Expires', 'Tue, 02 Feb 1999 10:00:00 GMT');

		if(isset($_SERVER['HTTP_RANGE'])){

			$httpRangeParts = explode('=', $_SERVER['HTTP_RANGE'], 2);
			$selectedRange = explode(',', $range, 2);
			$selectedRange = explode('-', $selectedRange[0]);
			$selectedRange[0] = (int)($selectedRange[0]);

			if(!$selectedRange[1]){
				$selectedRange[1] = $this->_fileSize-1;
			} else {
				$selectedRange[1] = (int)$selectedRange[1];
			}

			$downloadLength = $selectedRange[1]-$selectedRange[0]+1;
			$controllerResponse->setHeader('HTTP/1.1 206 Partial Content');
			$controllerResponse->setHeader('Content-Length', $downloadLength);
			$controllerResponse->setHeader('Content-Range', 'bytes '.$selectedRange[0].'-'.$selectedRange[1].'/'.$this->_fileSize);
		} else {
			$downloadLength = $this->_fileSize;
			$controllerResponse->setHeader('Content-Length', $this->_fileSize);
		}

		if(isset($_SERVER['HTTP_RANGE'])){
			$bytesSend = 0;
			$fp = fopen($this->_pathToFile, 'r');
			fseek($fp, $selectedRange[0]);
			while(!feof($fp)&&$bytesSend<$newLength){
				$buffer = fread($fp, 8192);
				echo $buffer;
				$bytesSend += strlen($buffer);
				if(connection_aborted()){
					throw new ControllerException('Se ha cancelado la descarga');
				}
			}
			fclose($fp);
		} else {
			readfile($this->_pathToFile);
		}

	}
}

