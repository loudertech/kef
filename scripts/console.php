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
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: console.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */

if(isset($_SERVER['SERVER_SOFTWARE'])){
	header('Location: index.php');
	exit;
}

$fp = fopen("php://stdin", "r");
echo "Bienvenido a Kumbia Enterprise Console\n";
echo "Escriba 'exit' para salir\n\n";
echo "iphp> ";
while($_c = fgets($fp)){
	if(rtrim($_c)=="quit"){
		exit;
	}
	try {
		if(trim($_c)){
			$_a = eval("return ".trim($_c).";");
			if($_a===null){
				echo "NULL";
			} else {
				if($_a===false){
					echo "FALSE";
				} else {
					if($_a===true){
						echo "TRUE";
					} else {
						if(!is_object($_a)){
							print_r($_a);
						} else {
							echo "Object Instance Of ".get_class($_a);
						}
					}
				}
			}
			echo "\niphp> ";
		} else {
			echo "iphp> ";
		}
	}
	catch(KumbiaException $e){
		echo $e->getMessage()."\n";
		$i = 1;
		foreach($e->getTrace() as $trace){
			if($trace['class']){
				echo "#$i {$trace['class']}::{$trace['function']}(".join(",",$trace['args']).") en ".basename($trace['file'])."\n";
			}
			$i++;
		}
	}
	echo "iphp> ";
}
fclose($fp);
