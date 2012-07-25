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
 * @package		Db
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: DbException.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * DbException
 *
 * Clase que administra las excepciones generados en el componente Db
 *
 * @category	Kumbia
 * @package		Db
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class DbException extends CoreException {

	/**
	 * Conexion que generó la excepcion
	 *
	 * @var DbBase
	 */
	private $_dbBase;

	/**
	 * Constructor de la excepcion
	 *
	 * @param string $message
	 * @param int $code
	 * @param boolean $showTrace
	 * @param DbBase $dbBase
	 */
	public function __construct($message, $code, $showTrace=true, $dbBase=null){
		$this->_dbBase = $dbBase;
		parent::__construct($message, $code, $showTrace);
	}

	/**
	 * Genera informacion extra de la excepcion
	 *
	 * @return string
	 */
	public function getExceptionInformation(){
		if($this->_dbBase!=null){
			$htmlCode = "
			<div class='exceptionExtra'>
				<b>Datos de la Conexión Activa:</b>
				<table class='exceptionExtra' cellspacing='0' width='100%' align='center'>
					<tr>
						<td align='right' class='exceptionLeftColumn' width='150'><b>Solo Lectura:</b></td>
						<td align='left' class='exceptionRightColumn'>".($this->_dbBase->isReadOnly() ? "<b>SI</b>" : "NO")."</td>
					</tr>
					<tr>
						<td align='right' class='exceptionLeftColumn' width='150'><b>Autocommit Activado:</b></td>
						<td align='left' class='exceptionRightColumn'>".($this->_dbBase->getHaveAutoCommit() ? "<b>SI</b>" : "NO")."</b></td>
					</tr>
					<tr>
						<td align='right' class='exceptionLeftColumn' width='150'><b>Bajo una Transacción:</b></td>
						<td align='left' class='exceptionRightColumn'>".($this->_dbBase->isUnderTransaction() ? "<b>SI</b>" : "NO")."</b></td>
					</tr>
					<tr>
						<td align='right' class='exceptionLeftColumn' width='150'><b>Traza:</b></td>
						<td align='left' class='exceptionRightColumn'>".($this->_dbBase->isTracing() ? "<b>SI</b>" : "NO")."</b></td>
					</tr>";
					if($this->_dbBase->isTracing()==true){
						$htmlCode.= "<tr>
							<td align='right' class='exceptionLeftColumn' width='150' valign='top'><b>Contenido de la Traza:</b></td>
							<td align='left' class='exceptionCode'>";
							$i = 1;
							foreach (array_reverse($this->_dbBase->getTracedSQL()) as $trace){
								$htmlCode.= sprintf("%02s", $i).". $trace<br>";
								++$i;
							}
							$htmlCode.= "</td>
						</tr>";
					}
				$htmlCode.="</table>
			</div>";
			return $htmlCode;
		} else {
			return "";
		}
	}

}
