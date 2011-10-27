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
 * @package		Core
 * @subpackage	CoreInfo
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: CoreInfo.php 94 2009-09-24 00:44:18Z gutierrezandresfelipe $
 */

/**
 * CoreInfo
 *
 * Consulta información del framework y genera la pantalla de bienvenida
 *
 * @category	Kumbia
 * @package		Core
 * @subpackage	CoreInfo
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access 		public
 * @abstract
 */
abstract class CoreInfo {

	/**
	 * Muestra la pantalla de inicio de una aplicación
	 *
	 * @access 	public
	 * @static
	 */
	public static function showInfoScreen(){

		View::setRenderLevel(View::LEVEL_NO_RENDER);

		Tag::setDocumentTitle('Bienvenido | Kumbia Enterprise Framework');
		Core::setInstanceName();

		Tag::addJavascript('core/info');
		Tag::addJavascriptFramework('scriptaculous');

		Tag::stylesheetLink('info');

		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
 		<head>'.Tag::getDocumentTitle().'
 		<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />';

		//Cargar estilos
		Tag::stylesheetLink('style');
		echo Tag::stylesheetLinkTags();

		//Cargar Javascripts
		echo Tag::javascriptSources();

		echo '</head>
		<body>
		<div align="center" id="header">
			<table>
				<tr>
					<td>
						<a href="http://www.loudertechnology.com/site">
							<div id="header-1"></div>
						</a>
					</td>
					<td>
						<div id="menu">
							<ul>
								<li><a class="inicio" href="#">Inicio</a></li>
								<li><a href="http://www.loudertechnology.com/projects/kumbia_enterprise_framework">Sitio Oficial</a></li>
								<li><a href="http://www.loudertechnology.com/site/docs/index">Documentación</a></li>
								<li><a href="http://www.loudertechnology.com/site/devzone/index">Zona Desarrolladores</a></li>
								<li><a href="http://groups.google.com/group/kef-support">Grupo Debate y Ayuda</a></li>
							</ul>
						</div>
					</td>
				</tr>
			</table>
		</div>

		<div align="center" id="footer-content">
			<div id="footer-cloud"></div>
			<div id="footer-back">
				<div align="center" id="footer-info">
					<a href="http://www.loudertechnology.com/site/projects/license">Licencia de C&oacute;digo Abierto</a> |
					<a href="http://www.loudertechnology.com/">Louder Technology</a> ', date("Y"), '
				</div>
			</div>
		</div>

		<div align="center" id="middle-content">
			<table id="table-content">
				<tr>
					<td><div id="welcome"></div></td>
					<td><div id="version">v'.Core::FRAMEWORK_VERSION.'</div></td>
					<td>
						<div id="tweets">
							<table width="100%" cellspacing="5">
								<tr>
									<td id="follow-us">Novedades</td>
								</tr>
								<tr>
									<td id="the-tweets">
										<div id="loading">Cargando...</div>
										<iframe id="louder-tweets"></iframe>
									</td>
								</tr>
							</table>
						</div>
					</td>
					<td>
						<div id="empezar">
							<div id="donde"></div>
							<p>
								Para reemplazar esta p&aacute;gina
								edite el archivo <i>apps/default/controllers/application.php</i> en el DocumentRoot del servidor
								Web <i>('.(isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : getcwd())."/".Core::getInstanceName().')</i>
							</p>
							<p>
								<ul>
									<li>
										<a href="http://www.loudertechnology.com/site/docs/show/caso-de-uso-aplicacion-de-cajero-bancario.phtml">Caso de Uso: Aplicaci&oacute;n de Cajero Bancario</a>
									</li>
									<li>
										<a href="http://www.loudertechnology.com/site/docs">Documentación y Listado de Ejemplos</a>
									</li>
									<li>
										<a href="http://www.loudertechnology.com/site/devzone">Ejemplos, Tutoriales y Tips de los Desarrolladores del Framework</a>
									</li>
								</ul>
							</p>
						</div>
					</td>
				</tr>
			</table>
		</div>

		<div id="como-empezar">
			<a href="#" onclick="this.parentNode.hide(); GettingStarted.show(); return false;">¿Donde empezar?</a>
		</div>

		</body></html>';

	}

}
