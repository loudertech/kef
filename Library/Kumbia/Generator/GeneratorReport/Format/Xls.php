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
 * @category 	Kumbia
 * @package 	Generator
 * @subpackage 	GeneratorReport
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Xls.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * @see Spreadsheet_Excel_Writer
 */
require KEF_ABS_PATH.'Library/Excel/Main.php';

/**
 * XlsGenerator
 *
 * Genera un reporte en Excel
 *
 * @param array $result
 * @param array $sumArray
 * @param string $title
 * @param array $weightArray
 * @param array $headerArray
 */
function xls($result, $sumArray, $title, $weightArray, $headerArray){

	$file = md5(mt_rand(1, 10000));
	$config = CoreConfig::readAppConfig();
	$active_app = Router::getApplication();

	$workbook = new Spreadsheet_Excel_Writer("public/temp/$file.xls");
	$worksheet = $workbook->addWorksheet();

	$titulo_verdana  = $workbook->addFormat(array('fontfamily' => 'Verdana',
	'size' => 20));
	$titulo_verdana2 = $workbook->addFormat(array('fontfamily' => 'Verdana',
	'size' => 18));

	$workbook->setCustomColor(12, 0xF2, 0xF2, 0xF2);
	$worksheet->setInputEncoding("utf-8");

	$column_title = $workbook->addFormat(array('fontfamily' => 'Verdana',
	'size' => 12,
	'fgcolor' => 12,
	'border' => 1,
	'bordercolor' => 'black',
	"halign" => 'center'
	));

	$column = $workbook->addFormat(array(	'fontfamily' => 'Verdana',
	'size' => 11,
	'border' => 1,
	'bordercolor' => 'black',
	));

	$column_centered = $workbook->addFormat(array(	'fontfamily' => 'Verdana',
	'size' => 11,
	'border' => 1,
	'bordercolor' => 'black',
	"halign" => 'center'
	));

	$worksheet->write(0, 0, strtoupper($config->application->name), $titulo_verdana);
	$worksheet->write(1, 0, "REPORTE DE ".strtoupper($title), $titulo_verdana2);
	$worksheet->write(2, 0, "FECHA ".date("Y-m-d"), $titulo_verdana2);

	for($i=0;$i<=count($headerArray)-1;++$i){
		$worksheet->setColumn($i, $i, $weightArray[$i]);
		$worksheet->write(4, $i, $headerArray[$i], $column_title);
	}

	$l = 5;
	foreach($result as $row){
		for($i=0;$i<=count($row)-1;++$i){
			if(!is_numeric($row[$i])){
				$worksheet->writeString($l, $i, $row[$i], $column);
			} else {
				$worksheet->writeString($l, $i, $row[$i], $column_centered);
			}
		}
		++$l;
	}

	$workbook->close();

	if(isset($raw_output)){
		echo "<script type='text/javascript'> window.open('".Core::getInstancePath()."temp/".$file.".xls', null);  </script>";
	} else {
		Generator::formsPrint("<script type='text/javascript'> window.open('".Core::getInstancePath()."temp/".$file.".xls', null);  </script>");
	}

}
