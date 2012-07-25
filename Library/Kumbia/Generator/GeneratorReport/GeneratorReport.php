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
 * @package		Generator
 * @subpackage	GeneratorReport
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: GeneratorReport.php 95 2009-09-27 03:32:04Z gutierrezandresfelipe $
 */

/**
 * Generador de Reportes
 *
 * @category	Kumbia
 * @package		Generator
 * @subpackage	GeneratorReport
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
abstract class GeneratorReport {

	/**
	 * Genera un reporte con las condiciones del formulario
	 *
	 * @access public
	 * @param array $form
	 * @static
	 */
	static public function generate($form){

		$weightArray = array();
		$headerArray = array();
		$tables = "";
		$selectedFields = array();
		$whereCondition = array();

		$n = 0;
		$db = DbPool::getConnection();
		if(isset($form['dataFilter'])&&$form['dataFilter']){
			if(strpos($form['dataFilter'], '@')){
				ereg("[\@][A-Za-z0-9_]+", $form['dataFilter'], $regs);
				foreach($regs as $reg){
					$form['dataFilter'] = str_replace($reg, $_REQUEST["fl_".str_replace("@", "", $reg)], $form['dataFilter']);
				}
			}
		}
		if($form['type']=='standard'){
			if(isset($form['joinTables'])&&$form['joinTables']){
				$tables = $form['joinTables'];
			}
			if(isset($form['joinConditions'])&&$form['joinConditions']) {
				$whereCondition[] = $form['joinConditions'];
			}
			foreach($form['components'] as $name => $com){
				if(!isset($com['attributes']['value'])){
					$com['attributes']['value'] = "";
				}
				if(isset($_REQUEST['fl_'.$name])){
					if($_REQUEST['fl_'.$name]==$com['attributes']['value']){
						$_REQUEST['fl_'.$name] = "";
					}
				} else {
					$_REQUEST['fl_'.$name] = "";
				}
				if(trim($_REQUEST["fl_".$name])&&$_REQUEST["fl_".$name]!='@'){
					if(!isset($form['components'][$name]['valueType'])){
						$form['components'][$name]['valueType'] = "";
					}
					if($form['components'][$name]['valueType']=='date'){
						$whereCondition[] = $form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
					} else {
						if($form['components'][$name]['valueType']=='numeric'){
							$whereCondition[] = $form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
						} else {
							if($form['components'][$name]['type']=='hidden'){
								$whereCondition[] = $form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
							} else {
								if($com['type']=='check'){
									if($_REQUEST["fl_".$name]==$form['components'][$name]['checkedValue'])
									$whereCondition[] = $form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
								} else {
									if($com['type']=='time'){
										if($_REQUEST["fl_".$name]!='00:00'){
											$whereCondition[] = " {$form['source']}.$name = '".$_REQUEST["fl_".$name]."'";
										}
									} else {
										if(!isset($com['primary'])){
											$com['primary'] = false;
										}
										if($com['primary']||$com['type']=='combo'){
											$whereCondition[] = $form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
										} else {
											$whereCondition[] = $form['source'].".$name like '%".$_REQUEST["fl_".$name]."%'";
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$_REQUEST['reportTypeField'] = Filter::bring($_REQUEST['reportTypeField'], 'identifier');
		if(preg_match('/_id$/', $_REQUEST['reportTypeField'])){
			$orderFields = i18n::substr($_REQUEST['reportTypeField'], 0, i18n::strlen($_REQUEST['reportTypeField'])-3);
		} else {
			$orderFields = $_REQUEST['reportTypeField'];
		}

		$n = 0;
		$a = 0;
		$aliases = array();
		foreach($form['components'] as $name => $com){
			if(!isset($com['notReport'])){
				$com['notReport'] = false;
			}
			if(!isset($com['class'])){
				$com['class'] = false;
			}
			if(!$com['notReport']){
				if(isset($com['caption'])&&$com['caption']){
					$headerArray[$n] = html_entity_decode($com['caption'], ENT_COMPAT, 'UTF-8');
					$headerArray[$n] = str_replace("<br/>", " ", $headerArray[$n]);
				} else {
					$com['caption'] = "";
				}
				if($com['type']=='combo'&&$com['class']=='dynamic'){
					$foreignTable = 'a'.$a;
					$a++;
					if(isset($com['extraTables'])&&$com['extraTables']){
						$tables.= $com['extraTables'].",";
					}
					if(isset($com['whereConditionOnQuery'])&&$com['whereConditionOnQuery']){
						$whereCondition[] = $com['whereConditionOnQuery'];
					}
					if(strpos($com['detailField'], 'concat(')!==false){
						$selectedFields[] = str_replace($com['foreignTable'].'.', $foreignTable.'.', $com['detailField']);
					} else {
						$selectedFields[] = $foreignTable.'.'.$com['detailField'];
						if($com['foreignTable']==$orderFields){
							$orderFields = $foreignTable.'.'.$com['detailField'];
						}
					}
					$tables.=$com['foreignTable'].' '.$foreignTable.',';
					if($com['column_relation']){
						$whereCondition[] = $foreignTable.'.'.$com['column_relation'].' = '.$form['source'].'.'.$name;
					} else {
						$whereCondition[] = $foreignTable.'.'.$name.' = '.$form['source'].'.'.$name;
					}
					$weightArray[$n] = i18n::strlen($headerArray[$n])+2;
					$n++;
				} else {
					if($com['type']!='hidden'){
						if($com['class']=='static'){
							$weightArray[$n] = i18n::strlen($headerArray[$n])+2;
							#if($config->database->type=='postgresql'){
							#	$selectedFields.="CASE ";
							#}
							#if($config->database->type=='mysql'){
								$field = '';
								for($i=0;$i<=count($com['items'])-2;$i++){
									$field.="IF(".$form['source'].".".$name."='".$com['items'][$i][0]."', '".$com['items'][$i][1]."', ";
									if($weightArray[$n]<strlen($com['items'][$i][1])) {
										$weightArray[$n] = i18n::strlen($com['items'][$i][1])+1;
									}
								}
							#}

							/*if($config->database->type=='postgresql'){
								for($i=0;$i<=count($com['items'])-1;$i++){
									$selectedFields.=" when ".$form['source'].".".$name."='".$com['items'][$i][0]."' THEN '".$com['items'][$i][1]."' ";
									if($weightArray[$n]<strlen($com['items'][$i][1])) {
										$weightArray[$n] = strlen($com['items'][$i][1])+1;
									}
								}
							}*/

							$n++;
							#if($config->database->type=='mysql'){
								$field.="'".$com['items'][$i][1]."')";
								for($j=0;$j<=$i-2;$j++) {
									$field.=")";
								}
							/*}
							if($config->database->type=='postgresql'){
								$selectedFields.=" end ";
							}*/
							$selectedFields[] = $field;
						} else {
							$selectedFields[] = $form['source'].".".$name;
							//Aqui seguro que no es foranea, entonces tenemos que poner la tabla principal                                                  //
							//antes para evitar repeticiones
							if($name==$orderFields){
								$orderFields = $form['source'].".".$orderFields;
							}
							$weightArray[$n] = i18n::strlen($headerArray[$n])+2;
							$n++;
						}
					}

				}
			}
		}
		$tables.=$form['source'];
		$selectedFields = join(',', $selectedFields);
		if(isset($form['dataRequisite'])&&$form['dataRequisite']){
			$whereCondition[] = $form['dataFilter'];
		}
		if($orderFields){
			$orderCondition = 'ORDER BY '.$orderFields;
		} else {
			$orderCondition = '';
		}
		$query = 'SELECT '.$selectedFields.' FROM '.$tables;
		if(count($whereCondition)>0){
			$query.=' WHERE '.join(' AND ', $whereCondition);
		}
		$query.=' '.$orderCondition;
		$q = $db->query($query);
		if(!is_bool($q)){
			if($db->numRows($q)==false){
				Flash::notice('No hay información para listar');
				return;
			}
		} else {
			Flash::error($db->error());
			return;
		}

		$result = array();
		$n = 0;
		$db->setFetchMode(dbBase::DB_NUM);
		while($row = $db->fetchArray($q)){
			$result[$n++] = $row;
		}
		$db->setFetchMode(dbBase::DB_BOTH);

		foreach($result as $row){
			for($i=0;$i<=count($row)-1;$i++){
				if($weightArray[$i]<strlen(trim($row[$i]))){
					$weightArray[$i] = strlen(trim($row[$i]));
				}
			}
		}

		for($i=0;$i<=count($weightArray)-1;$i++){
			$weightArray[$i]*= 1.8;
		}

		$sumArray = array_sum($weightArray);

		//echo $sumArray;

		if(!isset($_REQUEST['reportType'])||!$_REQUEST['reportType']){
			$_REQUEST['reportType'] = 'pdf';
		}

		if($_REQUEST['reportType']!='html'){
			$title = html_entity_decode($form['caption'], ENT_NOQUOTES, 'UTF-8');
		} else {
			$title = $form['caption'];
		}

		switch($_REQUEST['reportType']){
			case 'pdf':
				require_once "Library/Kumbia/Generator/GeneratorReport/Format/Pdf.php";
				pdf($result, $sumArray, $title, $weightArray, $headerArray);
				break;
			case 'xls':
				#error_reporting(0);
				require_once "Library/Kumbia/Generator/GeneratorReport/Format/Xls.php";
				xls($result, $sumArray, $title, $weightArray, $headerArray);
				break;
			case 'html':
				require_once "Library/Kumbia/Generator/GeneratorReport/Format/Htm.php";
				htm($result, $sumArray, $title, $weightArray, $headerArray);
				break;
			case 'doc':
				require_once "Library/Kumbia/Generator/GeneratorReport/Format/Doc.php";
				doc($result, $sumArray, $title, $weightArray, $headerArray);
				break;
			default:
				require_once "Library/Kumbia/Generator/GeneratorReport/Format/Pdf.php";
				pdf($result, $sumArray, $title, $weightArray, $headerArray);
				break;
		}

	}
}

