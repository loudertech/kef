<?php

/**
 * Kumbia PHP Framework
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
 * @category Kumbia
 * @copyright Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license New BSD License
 */

/**
 * El objetivo de esta funcion es reemplazar las variables @path, @img_path
 * @css_path en los archivos css para que busquen bien las rutas.
 *
 * Los archivos CSS son cacheados mientras no cambie la fecha de modificacion
 * de estos, en este caso vuelven a ser cacheados.
 *
 * Este archivo solo tiene funcion cuando se envia el segundo parametro
 * a stylesheetLinkTag("ruta.css", true)
 */
if(isset($_GET['c'])){
	$css = $_GET['c'];
	if(file_exists("css/$css.css")){
		$cachCss = '_'.str_replace('/', '', $_GET['p'].$css).".css";
		if(file_exists("temp/$cachCss")==true){
			if(filemtime("temp/$cachCss")>filemtime("css/$css.css")){
				header('Content-type: text/css', true);
				header("Location: temp/$cachCss", true);
				exit;
			}
		}
		$cssContent = file_get_contents("css/$css.css");
		$cssContent = str_replace("@path", $_GET['p'], $cssContent);
		$cssContent = str_replace("@img_path", $_GET['p']."img", $cssContent);
		$cssContent = str_replace("@css_path", $_GET['p']."css", $cssContent);
		header('Content-type: text/css', true);
		file_put_contents("temp/$cachCss", $cssContent);
		print $cssContent;
	}
}
