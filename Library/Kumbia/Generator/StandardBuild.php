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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (C) 2007-2007 Julian Cortes (jucorant at gmail.com)
 * @license		New BSD License
 * @version 	$Id: StandardBuild.php 106 2009-10-09 03:31:51Z gutierrezandresfelipe $
 */

/**
 * StandardBuild
 *
 * Clase que genera los formularios StandardForm
 *
 * @category	Kumbia
 * @package		Generator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (C) 2007-2007 Julian Cortes (jucorant at gmail.com)
 * @license 	New BSD License
 */
abstract class StandardGenerator {

	/**
	 * Crea el formulario Standard
	 *
	 * @access 	public
	 * @param	array $form
	 * @static
	 */
	static public function buildFormStandard($form){

		if(!isset($_REQUEST['value'])) {
			$_REQUEST['value'] = "";
		}
		if(!isset($_REQUEST['option'])) {
			$_REQUEST['option'] = "";
		}
		if(!isset($_REQUEST['queryStatus'])) {
			$_REQUEST['queryStatus'] = false;
		}
		if(!isset($_REQUEST['oldsubaction'])) {
			$_REQUEST['oldsubaction'] = "";
		}
		if(!isset($form['unableInsert'])) {
			$form['unableInsert'] = false;
		}
		if(!isset($form['unableQuery'])) {
			$form['unableQuery'] = false;
		}
		if(!isset($form['unableUpdate'])){
			$form['unableUpdate'] = false;
		}
		if(!isset($form['unableDelete'])) {
			$form['unableDelete'] = false;
		}
		if(!isset($form['unableBrowse'])) {
			$form['unableBrowse'] = false;
		}
		if(!isset($form['unableReport'])) {
			$form['unableReport'] = false;
		}
		if(!isset($form['fieldsPerRow'])) {
			$form['fieldsPerRow'] = 1;
		}
		if(!isset($form['show_not_nulls'])) {
			$form['show_not_nulls'] = false;
		}
		if(!isset($form['buttons'])){
			$form['buttons'] = array();
			$form['buttons']['insert'] = '';
			$form['buttons']['query'] = '';
			$form['buttons']['browse'] = '';
			$form['buttons']['report'] = '';
		}

		$appName = Router::getApplication();
		$controller = Dispatcher::getController();
		$controller_name = Router::getController();

		$jsPath = 'public/javascript/'.$appName.'/'.$controller_name.'.js';
		if(file_exists($jsPath)){
			Tag::addJavascript($appName.'/'.$controller_name);
		}

		if(!isset($form['dataRequisite'])){
			$form['dataRequisite'] = 1;
		}
		Generator::formsPrint("<div align='center' id='stdForm'>");
		if(!$form['dataRequisite']) {
			Generator::formsPrint("<div align='center' id='notFound'><b>No hay datos en consulta</b></div>");
		} else {
			Generator::formsPrint("<div align='center'>");
			if($_REQUEST['oldsubaction']=='Modificar') {
				$_REQUEST['queryStatus'] = true;
			}
			if($controller->view!='browse'){
				if(!$_REQUEST['queryStatus']){
					if(!$form['unableInsert']){
						$caption = $form['buttons']['insert'] ? $form['buttons']['insert'] : "Adicionar";
						Generator::formsPrint("<input type='button' class='controlButton' id='adiciona' value='$caption' lang='Adicionar' onclick='enable_insert(this)'>&nbsp;");
					}
					if(!$form['unableQuery']){
						$caption = $form['buttons']['query'] ? $form['buttons']['query'] : "Consultar";
						Generator::formsPrint("<input type='button' class='controlButton' id='consulta' value='$caption' lang='Consultar' onclick='enable_query(this)'>&nbsp;\r\n");
					}
					$ds = "disabled='disabled'";
				} else {
					$query_string = Utils::getKumbiaURL($controller_name."/fetch/");
					Generator::formsPrint("<input type='button' id='primero' class='controlButton' onclick='window.location=\"{$query_string}0/&amp;queryStatus=1\"' value='Primero'>&nbsp;");
					Generator::formsPrint("<input type='button' id='anterior' class='controlButton' onclick='window.location=\"{$query_string}".($_REQUEST['id']-1)."/&amp;queryStatus=1\"' value='Anterior'>&nbsp;");
					Generator::formsPrint("<input type='button' id='siguiente' class='controlButton' onclick='window.location=\"{$query_string}".($_REQUEST['id']+1)."/&amp;queryStatus=1\"' value='Siguiente'>&nbsp;");
					Generator::formsPrint("<input type='button' id='ultimo' class='controlButton' onclick='window.location=\"{$query_string}last/&amp;queryStatus=1\"' value='Ultimo'>&nbsp;");
					$ds = "";
				}

				//El Boton de Actualizar
				if($_REQUEST['queryStatus']){
					if(!$form['unableUpdate']){
						if(isset($form['buttons']['update'])){
							$caption = $form['buttons']['update'] ? $form['buttons']['update'] : "Modificar";
						} else {
							$caption = "Modificar";
						}
						if(isset($form['updateCondition'])){
							if(strpos($form['updateCondition'], '@')){
								if(preg_match('/[\@][A-Za-z0-9_]+/', $form['updateCondition'], $regs)){
									foreach($regs as $reg){
										$form['updateCondition'] = str_replace($reg, $_REQUEST["fl_".str_replace("@", "", $reg)], $form['updateCondition']);
									}
								}
							}
							$form['updateCondition'] = " \$val = (".$form['updateCondition'].");";
							eval($form['updateCondition']);
							if($val){
								Generator::formsPrint("<input type='button' class='controlButton' id='modifica' value='$caption' lang='Modificar' $ds onclick=\"enable_update(this)\">&nbsp;");
							}
						} else {
							Generator::formsPrint("<input type='button' class='controlButton' id='modifica' value='$caption' lang='Modificar' $ds onclick=\"enable_update(this)\">&nbsp;");
						}
					}
					//El de Borrar
					if(!$form['unableDelete']){
						if(isset($form['buttons']['delete'])){
							$caption = $form['buttons']['delete'] ? $form['buttons']['delete'] : "Borrar";
						} else {
							$caption = "Borrar";
						}
						Generator::formsPrint("<input type='button' class='controlButton controlButtonCancel' id='borra' value='$caption' lang='Borrar' $ds onclick=\"enable_delete()\">\r\n&nbsp;");
					}
				}

				if(!$_REQUEST['queryStatus']) {
					if(!$form['unableBrowse']){
						$caption = $form['buttons']['browse'] ? $form['buttons']['browse'] : "Visualizar";
						Generator::formsPrint("<input type='button' class='controlButton' id='visualiza' value='$caption' lang='Visualizar' onclick='enable_browse(this, \"$controller_name\")'>&nbsp;\r\n");
					}
				}

				//Boton de Reporte
				if(!$_REQUEST['queryStatus']) {
					if(!$form['unableReport']){
						$caption = $form['buttons']['report'] ? $form['buttons']['report'] : "Reporte";
						Generator::formsPrint("<input type='button' class='controlButton' id='reporte' value='$caption' lang='Reporte' onclick='enable_report(this)'>&nbsp;\r\n");
					}
				} else {
					Generator::formsPrint("<br /><br />\n<input type='button' class='controlButton' id='volver' onclick='window.location=\"".Utils::getKumbiaURL("$controller_name/back")."\"' value='Atr&aacute;s'>&nbsp;\r\n");
				}

				Generator::formsPrint("</div><br />\r\n");
				Generator::formsPrint("<table align='center'><tr>\r\n");
				$n = 1;
				//La parte de los Componentes
				Generator::formsPrint("<td align='right' valign='top'>\r\n");
				foreach($form['components'] as $name => $com){

					switch($com['type']){
						case 'text':
						Component::buildTextComponent($com, $name, $form);
						break;

						case 'combo':
						Component::buildStandardCombo($com, $name);
						break;

						case 'helpContext':
						Component::buildHelpContext($com, $name, $form);
						break;

						case 'userDefined':
						Component::buildUserdefinedComponent($com, $name, $form);
						break;

						case 'time':
						Component::buildTimeComponent($com, $name, $form);
						break;

						case 'password':
						Component::buildStandardPassword($com, $name);
						break;

						case 'textarea':
						Component::buildTextArea($com, $name);
						break;

						case 'image':
						Component::buildStandardImage($com, $name);
						break;

						//Este es el Check Chulito
						case 'check':
						if($com['first']){
							Generator::formsPrint($com['groupcaption']."</td><td><table cellpadding='0'>");
						}
						Generator::formsPrint("<tr><td>\r\n<input type='checkbox' disabled name='fl_$name' id='flid_$name' style='border:1px solid #FFFFFF'");
						if($_REQUEST['fl_'.$name]==$com['checkedValue']){
							Generator::formsPrint(" checked='checked'  ");
						}
						if($com["attributes"]){
							foreach($com["attributes"] as $nitem => $item) {
								Generator::formsPrint(" $nitem='$item' ");
							}
						}
						Generator::formsPrint(">\r\n</td><td>".$com['caption']."</td></tr>");
						if($com["last"]){
							Generator::formsPrint("</table>");
						}
						break;

						//Textarea
						case 'textarea':
						Generator::formsPrint("".$com['caption']." :</td><td><textarea disabled='disabled' name='fl_$name' id='flid_$name' ");
						foreach($com['attributes'] as $natt => $vatt){
							Generator::formsPrint("$natt='$vatt' ");
						}
						Generator::formsPrint(">".$_REQUEST['fl_'.$name]."</textarea>");
						break;

						//Oculto
						case 'hidden':
						if(!isset($_REQUEST['fl_'.$name])){
							$_REQUEST['fl_'.$name] = "";
						}
						Generator::formsPrint("<input type='hidden' name='fl_$name' id='flid_$name' value='".(isset($com['value']) ? $com['value'] : $_REQUEST['fl_'.$name])."'/>\r\n");
						break;
					}
					if($form['show_not_nulls']){
						if($com['notNull']&&$com['valueType']!='date'){
							Generator::formsPrint("*\n");
						}
					}
					if($com['type']!='hidden'){
						Generator::formsPrint("</td>");
						if($com['type']=='check'){
							if($com['last']) {
								if(!($n%$form['fieldsPerRow'])) {
									Generator::formsPrint("</tr><tr>\r\n");
								}
								$n++;
								Generator::formsPrint("<td align='right' valign='top'>");
							}
						}
						else {
							if(!($n%$form['fieldsPerRow'])) {
								Generator::formsPrint("</tr><tr>\r\n");
							}
							$n++;
							Generator::formsPrint("<td align='right' valign='top'>");
						}
					}
				}
				Generator::formsPrint("</td></tr><tr>
				<td colspan='2' align='center' style='display:none'>
				<div id='reportOptions' style='display:none'>
				<table>
					<tr>
						<td align='right'>
							<label for='reportType'>Formato Reporte:</label>
							<select name='reportType' id='reportType'>
								<option value='html'>HTML</option>
								<option value='pdf'>PDF</option>
								<option value='xls'>EXCEL</option>
								<option value='doc'>WORD</option>
							</select>
						</td>
						<td align='center'>
							<label for='reportField'>Ordenar por:</label>
							<select name='reportTypeField' id='reportTypeField'>");
								reset($form['components']);
								$numberComponents = count($form['components']);
								for($i=0;$i<$numberComponents;$i++){
									if(!isset($form['components'][key($form['components'])]['notReport'])){
										$form['components'][key($form['components'])]['notReport'] = false;
									}
									if(!$form['components'][key($form['components'])]['notReport']){
										if(isset($form['components'][key($form['components'])]['caption'])){
											Generator::formsPrint("<option value ='" .key($form['components']) ."'>".$form['components'][key($form['components'])]['caption']."</option>");
										}
									}
									next($form['components']);
								}
						Generator::formsPrint("</select>
						</td>
					</tr>
				</table>
				</div>
				</td>
				</tr>");
				Generator::formsPrint("</table><br />\r\n");

				if(isset($_REQUEST['fl_id'])){
					if($_REQUEST['fl_id']>0){
						Generator::formsPrint('<div align="right">
							<div class="rcs_box">
								'.Tag::image('pos2/abook.png').' '.Tag::linkTo('rcs/revisions/'.$form['source'].'/'.$_REQUEST['fl_id'], 'Consultar Revisiones').'
							</div>
						</div>');
					}
				}

			} else {
				/**
				 * @see Browse
				 */
				require_once 'Library/Kumbia/Generator/Browse.php';

				Browse::formsBrowse($form);
			}

			//Todos los Labels
			Generator::formsPrint("<script type='text/javascript'>\nvar Labels = {");
			$aLabels = "";
			foreach($form['components'] as $key => $com){
				if(isset($com['caption'])){
					$aLabels.=$key.": '".$com['caption']."',";
				} else {
					$aLabels.=$key.": '$key',";
				}
			}
			$aLabels = substr($aLabels, 0, strlen($aLabels)-1);
			Generator::formsPrint("$aLabels};\r\n");

			//Todos los campos
			Generator::formsPrint("var Fields = [");
			reset($form['components']);
			$numberComponents = count($form['components']);
			for($i=0;$i<$numberComponents;$i++){
				Generator::formsPrint("'".key($form['components'])."'");
				if($i!=(count($form['components'])-1)){
					Generator::formsPrint(",");
				}
				next($form['components']);
			}
			Generator::formsPrint("];\r\n");

			//Campos que no pueden ser nulos
			Generator::formsPrint("var NotNullFields = [");
			reset($form['components']);
			$NotNullFields = "";
			for($i=0;$i<$numberComponents;$i++){
				if(!isset($form['components'][key($form['components'])]['notNull'])){
					$form['components'][key($form['components'])]['notNull'] = false;
				}
				if(!isset($form['components'][key($form['components'])]['primary'])){
					$form['components'][key($form['components'])]['primary'] = false;
				}
				if($form['components'][key($form['components'])]['notNull']||$form['components'][key($form['components'])]['primary']){
					$NotNullFields.="'".key($form['components'])."',";
				}
				next($form['components']);
			}
			$NotNullFields = substr($NotNullFields, 0, strlen($NotNullFields)-1);
			Generator::formsPrint("$NotNullFields];\r\n");

			Generator::formsPrint("var DateFields = [");
			$dFields = "";
			foreach($form['components'] as $key => $value){
				if(isset($value['valueType'])){
					if($value['valueType']=='date')
					$dFields.="'".$key."',";
				}
			}
			$dFields = substr($dFields, 0, strlen($dFields)-1);
			Generator::formsPrint("$dFields];\r\n");

			//Campos que no son llave
			Generator::formsPrint("var UFields = [");
			$uFields = "";
			foreach($form['components'] as $key => $value){
				if(!$value['primary']){
					$uFields.="'".$key."',";
				}
			}
			$uFields = substr($uFields, 0, strlen($uFields)-1);
			Generator::formsPrint("$uFields];\r\n");

			//Campos E-Mail
			Generator::formsPrint("var emailFields = [");
			$uFields = "";
			foreach($form['components'] as $key => $value){
				if(isset($value['valueType'])){
					if($value['valueType']=='email'){
						$uFields.="'".$key."',";
					}
				}
			}
			$uFields = substr($uFields, 0, strlen($uFields)-1);
			Generator::formsPrint("$uFields];\r\n");

			//Campos Time
			Generator::formsPrint("var timeFields = [");
			$uFields = "";
			foreach($form['components'] as $key => $value){
				if($value['type']=='time'){
					$uFields.="'".$key."',";
				}
			}
			$uFields = substr($uFields, 0, strlen($uFields)-1);
			Generator::formsPrint("$uFields];\r\n");

			//Campos Time
			Generator::formsPrint("var imageFields = [");
			$uFields = "";
			foreach($form['components'] as $key => $value){
				if($value['type']=='image'){
					$uFields.="'".$key."',";
				}
			}
			$uFields = substr($uFields, 0, strlen($uFields)-1);
			Generator::formsPrint("$uFields];\r\n");

			//Campos que son llave
			Generator::formsPrint("var PFields = [");
			$pFields = "";
			foreach($form['components'] as $key => $value){
				if($value['primary']){
					$pFields.="'".$key."',";
				}
			}
			$pFields = substr($pFields, 0, strlen($pFields)-1);
			Generator::formsPrint("$pFields];\r\n");

			//Campos que son Auto Numericos
			Generator::formsPrint("var AutoFields = [");
			$aFields = "";
			foreach($form['components'] as $key => $value){
				if(isset($value['auto_numeric'])){
					if($value['auto_numeric']){
						$aFields.="'".$key."',";
					}
				}
			}
			$aFields = substr($aFields, 0, strlen($aFields)-1);
			Generator::formsPrint("$aFields];\r\n");

			Generator::formsPrint("var queryOnlyFields = [");
			$rFields = "";
			foreach($form['components'] as $key => $value){
				if(!isset($value['valueType'])) {
					$value['valueType'] = "";
				}
				if(!isset($value['queryOnly'])) {
					$value['queryOnly'] = false;
				}
				if($value['valueType']!='date'){
					if($value['queryOnly']){
						$rFields.="'".$key."',";
					}
				}
			}
			$rFields = substr($rFields, 0, strlen($rFields)-1);
			Generator::formsPrint("$rFields];\r\n");

			Generator::formsPrint("var queryOnlyDateFields = [");
			$rdFields = "";
			foreach($form['components'] as $key => $value){
				if(!isset($value['valueType'])) $value['valueType'] = "";
				if(!isset($value['queryOnly'])) $value['queryOnly'] = false;
				if($value['valueType']=='date'){
					if($value['queryOnly']){
						$rdFields.="'".$key."',";
					}
				}
			}
			$rdFields = substr($rdFields, 0, strlen($rdFields)-1);
			Generator::formsPrint("$rdFields];\r\n");

			Generator::formsPrint("var AddFields = [");
			$aFields = "";
			foreach($form['components'] as $key => $value){
				if(!isset($value['auto_numeric'])) {
					$value['auto_numeric'] = false;
				}
				if(!isset($value['attributes']['value'])) {
					$value['attributes']['value'] = false;
				}
				if((!$value['auto_numeric'])&&(!$value['attributes']['value'])){
					$aFields.="'".$key."',";
				}

			}
			$aFields = substr($aFields, 0, strlen($aFields)-1);
			Generator::formsPrint("$aFields];\r\n");

			Generator::formsPrint("var AutoValuesFields = [");
			$aFields = "";
			foreach($form['components'] as $key => $value){
				if(!isset($value['auto_numeric'])) {
					$value['auto_numeric'] = false;
				}
				if($value['auto_numeric']){
					$aFields.="'".$key."',";
				}
			}
			$aFields = substr($aFields, 0, strlen($aFields)-1);
			Generator::formsPrint("$aFields];\r\n");

			Generator::formsPrint("var AutoValuesFFields = [");
			$aFields = "";
			if(!isset($db)) {
				$db = DbPool::getConnection();
			}
			foreach($form['components'] as $key => $value){
				if(!isset($value['auto_numeric'])){
					$value['auto_numeric'] = false;
				}
				if($value['auto_numeric']){
					$db->setFetchMode(DbBase::DB_NUM);
					$q = $db->query("select max($key) from ".$form['source']);
					$row = $db->fetchArray($q);
					if($row){
						$row[0]++;
						$aFields.="'".$row[0]."',";
					} else {
						$aFields.="'1',";
					}
				}
			}
			$aFields = substr($aFields, 0, strlen($aFields)-1);
			Generator::formsPrint("$aFields];\r\n");

			if(!isset($_REQUEST['param'])) {
				$_REQUEST['param'] = "";
			}

			Generator::formsPrint("\nnew Event.observe(window, \"load\", function(){\n");
			if($controller->keep_action){
				Generator::formsPrint("\tkeep_action('".$controller->keep_action."');\n");
			}
			Generator::formsPrint("\tif(typeof register_form_events != 'undefined') register_form_events()\n})\n</script>\n");

			if($controller->view!='browse'){
				Generator::formsPrint("<div align='center'><input type='button' class='controlButton' id='aceptar' value='Aceptar' disabled='disabled' onclick='form_accept()' />&nbsp;");
				Generator::formsPrint("<input type='button' class='controlButton' id='cancelar' value='Cancelar' disabled='disabled' onclick='cancel_form()' />&nbsp;</div>");
				Generator::formsPrint("<input type='hidden' id='actAction' value='' />\n
				</form>
                <form id='saveDataForm' method='post' action='' style='display:none' enctype=\"multipart/form-data\"></form>");
			}
		}

	}

}
