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
 * @license 	New BSD License
 */

/**
 * GeneratorDb
 *
 * Permite realizar consultas sobre los meta-datos de StandardForm
 *
 * @category	Kumbia
 * @package 	Generator
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class GeneratorDb {

	/**
	 * Devuelve los meta-datos de un formulario
	 *
	 * @param	string $formName
	 * @param	string $controller
	 * @return	array
	 */
	private static function _getData($formName, $controller){
		$instance = Core::getInstanceName();
		$appName = Router::getApplication();
		$module = Router::getModule();
		$controller = $formName.'standard';
		if(isset($_SESSION['KSF'][$instance][$appName][$controller])){
			$controllerData = &$_SESSION['KSF'][$instance][$appName][$controller];
			if($controllerData['status']=='C'){
				$data = unserialize(gzuncompress($controllerData['data']));
				$_SESSION['KSF'][$instance][$appName][$controller]['data'] = $data;
				$_SESSION['KSF'][$instance][$appName][$controller]['status'] = 'N';
			} else {
				$data = unserialize($controllerData['data']);
			}
			return $data;
		} else {
			return array();
		}
	}

	/**
	 * Consulta la etiqueta apartir del nombre del formulario y campo
	 *
	 * @param string $formName
	 * @param string $fieldName
	 */
	public static function getCaption($formName, $fieldName){
		$data = self::_getData($formName, $fieldName);
		if(isset($data['components'][$fieldName]['caption'])){
			return $data['components'][$fieldName]['caption'];
		} else {
			return ucwords(str_replace('_', ' ', $fieldName));
		}
	}

	/**
	 * Consulta la etiqueta apartir del nombre del formulario y campo
	 *
	 * @param string $formName
	 * @param string $fieldName
	 */
	public static function isHidden($formName, $fieldName){
		$data = self::_getData($formName, $fieldName);
		if(isset($data['components'][$fieldName]['type'])){
			if($data['components'][$fieldName]['type']=='hidden'){
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function getDetail($formName, $fieldName, $value){
		$data = self::_getData($formName, $fieldName);
		if(isset($data['components'][$fieldName]['type'])){
			switch($data['components'][$fieldName]['type']){
				case 'text':
					return $value;
				case 'combo':
					if($data['components'][$fieldName]['class']=='dynamic'){
						$component = $data['components'][$fieldName];
						$db = DbPool::getConnection();
						if(isset($component['extraTables'])){
							if($component['extraTables']){
								ActiveRecord::sqlSanizite($component["extraTables"]);
								$component['extraTables']=",".$component['extraTables'];
							}
						}
						if(isset($component["detail_field"])){
							ActiveRecordUtils::sqlSanizite($component["detail_field"]);
						}
						if(isset($component['orderBy'])){
							ActiveRecordUtils::sqlSanizite($component['orderBy']);
							if(!$component['orderBy']){
								$orderBy = $name;
							} else {
								$orderBy = $component['orderBy'];
							}
						} else {
							$orderBy = $fieldName;
						}
						ActiveRecordUtils::sqlItemSanizite($component['foreignTable']);
						if($component['column_relation']){
							$where = ' WHERE '.$component['column_relation'].' = \''.$value.'\'';
						} else {
							$where = ' WHERE '.$fieldName.' = \''.$value.'\'';
						}
						if(isset($component['whereCondition'])){
							if($component['whereCondition']) {
								$where = 'AND '.$component['whereCondition'];
							}
						}
						if($component['column_relation']){
							ActiveRecordUtils::sqlSanizite($component['column_relation']);
							if(isset($component['extraTables'])){
								$query = 'SELECT '.$component['foreignTable'].'.'.$component['column_relation'].' as '.$fieldName.',
										'.$component['detailField'].' FROM
										'.$component['foreignTable'].$component['extraTables'].' '.$where.' order by '.$orderBy;
							} else {
								$query = 'SELECT '.$component['foreignTable'].'.'.$component['column_relation'].' as '.$fieldName.',
										'.$component['detailField'].' FROM
										'.$component['foreignTable'].' '.$where.' ORDER BY '.$orderBy;
							}
						}else {
							$query = 'SELECT '.$component['foreignTable'].'.'.$fieldName.', '.$component['detailField'].
									' FROM '.$component['foreignTable'].$component['extraTables'].' '.$where.' ORDER BY '.$orderBy;
						}
						$cursor = $db->query($query);
						if($cursor!=false){
							$detail = $db->fetchArray();
							return $detail[$component['detailField']];
						} else {
							return $value;
						}
					} else {
						foreach($data['components'][$fieldName]['items'] as $item){
							if($item[0]==$value){
								return $item[1];
							}
						}
					}
					return $value;
				default:
					return $data['components'][$fieldName]['type'];
			}
		} else {
			return $value;
		}
	}

}
