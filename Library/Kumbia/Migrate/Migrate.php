<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.

 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	Migrate
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * Migrate
 *
 * Subcomponente que permite actualizar versiones anteriores del framework a la más reciente
 *
 * @category 	Kumbia
 * @package 	Migrate
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */
class Migrate {

	/**
	 * Indica si el controlador actual es StandardForm
	 *
	 * @var boolean
	 */
	private $_isStandardForm = false;

	/**
	 * Nombre de la clase actual
	 *
	 * @var string
	 */
	private $_className = '';

	/**
	 * Relación de métodos de ActiveRecord
	 *
	 * @var array
	 */
	private $_activeRecordMethodMap = array(
		'find_first' => 'findFirst',
		'belongs_to' => 'belongsTo',
		'has_many' => 'hasMany',
		'has_one' => 'hasOne'
	);

	/**
	 * Relación de métodos de Db
	 *
	 * @var array
	 */
	private $_dbMethodMap = array(
		'fetch_one' => 'fetchOne'
	);

	/**
	 * Mapa de Funciones a migrar de controladores
	 *
	 * @var array
	 */
	private $_controllerMethodMap = array(
		'set_response' => 'setResponse',
		'route_to' => 'routeTo',
		'request' => 'getRequestParam',
		'post' => 'getPostParam',
		'getPost' => 'getPostParam',
		'getPOST' => 'getPostParam',
		'get' => 'getQueryParam',
		'getGet' => 'getQueryParam',
		'getGET' => 'getQueryParam',
		'render_text' => 'renderText',
		'render_partial' => 'renderPartial'
	);

	/**
	 * Mapa de funciones a migrar de controladores
	 *
	 * @var array
	 */
	private $_standardFormMethodMap = array(
		'set_form_caption' => 'setFormCaption',
		'set_caption' => 'setCaption',
		'set_type_image' => 'setTypeImage',
		'set_text_upper' => 'setTextUpper',
		'set_combo_static' => 'setComboStatic',
		'set_combo_dynamic' => 'setComboDynamic',
		'set_query_only' => 'setQueryOnly',
		'not_report' => 'notReport',
		'not_browse' => 'notBrowse',
		'set_hidden' => 'setHidden',
		'set_title_form' => 'setTitleForm',
		'unable_insert' => 'unableInsert',
		'unable_delete' => 'unableDelete',
		'unable_update' => 'unableUpdate',
	);

	private $_controllerFilters = array(
		'before_filter' => 'beforeFilter',
		'after_filter' => 'afterFilter'
	);

	private $_activeRecordEvent = array(
		'after_create' => 'afterCreate',
		'before_create' => 'beforeCreate',
		'after_update' => 'afterUpdate',
		'before_update' => 'beforeUpdate',
		'before_delete' => 'beforeDelete'
	);

	/**
	 * Eventos de StandardForm
	 *
	 * @var unknown_type
	 */
	private $_standardFormEvents = array(
		'before_insert' => 'beforeInsert',
		'before_report' => 'beforeReport',
		'before_delete' => 'beforeDelete',
		'after_report' => 'afterReport',
		'before_update' => 'beforeUpdate'
	);

	/**
	 * Relación de componentes y sus métodos
	 *
	 * @var unknown_type
	 */
	private $_commonMap = array(
		'Kumbia' => array(
			'route_to' => array('Router', 'routeTo'),
			'stylesheet_link_tags' => array('Tag', 'stylesheetLinkTags'),
			'javascript_base' => array('Tag', 'javascriptBase'),
			'import' => array('Core', 'import')
		),
		'kumbia' => array(
			'route_to' => array('Router', 'routeTo'),
			'stylesheet_link_tags' => array('Tag', 'stylesheetLinkTags'),
			'javascript_base' => array('Tag', 'javascriptBase'),
		),
		'Db' => array(
			'raw_connect' => array('Db', 'rawConnect'),
		),
		'Config' => array(
			'read' => array('CoreConfig', 'readFromActiveApplication')
		),
		'Session' => array(
			'set_data' => array('Session', 'setData'),
			'get_data' => array('Session', 'getData'),
			'unset_data' => array('Session', 'unsetData')
		),
	);

	/**
	 * Relación de Helpers
	 *
	 * @var array
	 */
	private $_tagHelpers = array(
		'form_tag' => 'Tag::form',
		'link_to' => 'Tag::linkTo',
		'link_to_remote' => 'Tag::linkTo',
		'submit_tag' => 'Tag::submitButton',
		'end_form_tag' => 'Tag::endForm',
		'text_field_tag' => 'Tag::textField',
		'password_field_tag' => 'Tag::passwordField',
		'numeric_field_tag' => 'Tag::numericField',
		'stylesheet_link_tag' => 'Tag::stylesheetLink',
		'javascript_include_tag' => 'Tag::javascriptInclude',
		'javascript_library_tag' => 'Tag::javascriptLibrary',
		'img_tag' => 'Tag::image',
		'tr_break' => 'Tag::trBreak',
		'date_field_tag' => 'Tag::dateField',
		'button_to_action' => 'Tag::buttonToAction',
		'textupper_field_tag' => 'Tag::textUpperField',
		'text_field_with_autocomplete' => 'Tag::textFieldWithAutocomplete',
		'button_tag' => 'Tag::button',
		'select_tag' => 'Tag::select',
		'hidden_field_tag' => 'Tag::hiddenField',
		'file_field_tag' => 'Tag::fileFieldTag'
	);

	/**
	 * Indica si es un metodo de active record
	 *
	 * @param array $tokens
	 * @param int $i
	 */
	private function _isActiveRecordMethod($tokens, $i){
		if(isset($tokens[$i-4])){
			if(isset($tokens[$i-4][1])){
				if($tokens[$i-4][1]=='$this'){
					if(isset($this->_activeRecordMethodMap[$tokens[$i][1]])){
						$tokens[$i][1] = $this->_activeRecordMethodMap[$tokens[$i][1]];
						return true;
					} else {
						return false;
					}
				}

			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Indica si es un metodo de active record
	 *
	 * @param array $tokens
	 * @param int $i
	 */
	private function _isOwnActiveRecordMethod($tokens, $i){
		if(isset($tokens[$i-2])){
			if(isset($tokens[$i-2][1])){
				if($tokens[$i-2][1]=='$this'){
					if(isset($this->_activeRecordMethodMap[$tokens[$i][1]])){
						$tokens[$i][1] = $this->_activeRecordMethodMap[$tokens[$i][1]];
						return true;
					}
				}
				if($tokens[$i-2][1]=='function'){
					if($tokens[$i][1]=='__construct'){
						$tokens[$i][1] = 'initialize';
						return true;
					} else {
						if(isset($this->_activeRecordEvent[$tokens[$i][1]])){
							$tokens[$i][1] = $this->_activeRecordEvent[$tokens[$i][1]];
							return true;
						}
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Migra un metodo del controlador
	 *
	 * @param array $tokens
	 * @param int $i
	 */
	private function _isControllerMethod($tokens, $i){
		if(isset($tokens[$i-2])){
			if(isset($tokens[$i-2][1])){
				if($tokens[$i-2][1]=='$this'){
					if(isset($this->_controllerMethodMap[$tokens[$i][1]])){
						$tokens[$i][1] = $this->_controllerMethodMap[$tokens[$i][1]];
						return true;
					} else {
						if($this->_isStandardForm==true){
							if(isset($this->_standardFormMethodMap[$tokens[$i][1]])){
								$tokens[$i][1] = $this->_standardFormMethodMap[$tokens[$i][1]];
								return true;
							}
						}
					}
				}
				if($tokens[$i-2][1]=='function'){
					if(substr($tokens[$i][1], 0, 2)!='__'&&$tokens[$i][1]!=$this->_className){
						if($this->_isStandardForm==false){
							if(isset($this->_controllerFilters[$tokens[$i][1]])){
								$tokens[$i][1] = $this->_controllerFilters[$tokens[$i][1]];
								return true;
							} else {
								$tokens[$i][1] = $tokens[$i][1].'Action';
								return true;
							}
						} else {
							if($tokens[$i][1]!='initialize'){
								if(isset($this->_standardFormEvents[$tokens[$i][1]])){
									$tokens[$i][1] = $this->_standardFormEvents[$tokens[$i][1]];
									return true;
								} else {
									if(isset($this->_controllerFilters[$tokens[$i][1]])){
										$tokens[$i][1] = $this->_controllerFilters[$tokens[$i][1]];
										return true;
									} else {
										$tokens[$i][1] = $tokens[$i][1].'Action';
										return true;
									}
								}
							}
						}
					} else {
						if($tokens[$i][1]=='__construct'||$tokens[$i][1]==$this->_className){
							$tokens[$i][1] = 'initialize';
							return true;
						}
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Migra metodos comunes
	 *
	 * @param array $tokens
	 * @param int $i
	 */
	private function _isCommonMethod($tokens, $i){
		if(isset($tokens[$i])){
			if(isset($tokens[$i][1])){
				if(isset($this->_commonMap[$tokens[$i][1]])){
					if(isset($this->_commonMap[$tokens[$i][1]][$tokens[$i+2][1]])){
						$replaceMethod = &$this->_commonMap[$tokens[$i][1]][$tokens[$i+2][1]];
						$tokens[$i][1] = $replaceMethod[0];
						$tokens[$i+2][1] = $replaceMethod[1];
						return true;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Migra los helpers JavaScript
	 *
	 * @param array $tokens
	 * @param int $i
	 * @return
	 */
	private function _isTagHelper($tokens, $i){
		if(isset($this->_tagHelpers[$tokens[$i][1]])){
			$tokens[$i][1] = $this->_tagHelpers[$tokens[$i][1]];
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Migra los helpers JavaScript
	 *
	 * @param array $tokens
	 * @param int $i
	 * @return
	 */
	private function _isViewContent($tokens, $i){
		if($tokens[$i][1]=='content'){
			$tokens[$i][1] = 'View::getContent';
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Indica si es un método de acceso a la base de datos
	 *
	 * @param	array $tokens
	 * @param	int $i
	 * @return 	boolean
	 */
	private function _isDbMethod($tokens, $i){
		if(isset($this->_dbMethodMap[$tokens[$i][1]])){
			$tokens[$i][1] = $this->_dbMethodMap[$tokens[$i][1]];
			return true;
		}
		return false;
	}

	/**
	 * Indica si es un helper en una vista
	 *
	 * @param	array $tokens
	 * @param	int $i
	 * @return 	boolean
	 */
	private function _isInViewActiveRecord($tokens, $i){
		if(isset($this->_activeRecordMethodMap[$tokens[$i][1]])){
			$tokens[$i][1] = $this->_activeRecordMethodMap[$tokens[$i][1]];
			return true;
		}
		return false;
	}

	/**
	 * Migra el antiguo KUMBIA_PATH
	 *
	 * @param	array $tokens
	 * @param 	int $i
	 * @return	booelan
	 */
	private function _isKumbiaPath($tokens, $i){
		if($tokens[$i][1]=='KUMBIA_PATH'){
			$tokens[$i][1] = 'Core::getInstancePath()';
			return true;
		}
		return false;
	}

	/**
	 * Migra un modelo a KEF
	 *
	 * @param string $source
	 */
	public function migrateModel($source){
		$migratedSource = "";
		$tokens = token_get_all($source);
		$i = 0;
		$tl = count($tokens);
		for($i=0;$i<$tl;++$i){
			$token = $tokens[$i];
			if(!isset($token[1])){
				$migratedSource.=$token[0];
			} else {
				if($token[0]==T_STRING){
					if($this->_isOwnActiveRecordMethod(&$tokens, $i)==true){
						$token = $tokens[$i];
					} else {
						if($this->_isCommonMethod(&$tokens, $i)==true){
							$token = $tokens[$i];
						}
					}
				}
				$migratedSource.=$token[1];
			}
		}
		return $migratedSource;
	}

	/**
	 * Migra un controlador a KEF
	 *
	 * @param string $source
	 */
	public function migrateController($source){
		$migratedSource = "";
		$tokens = token_get_all($source);
		$i = 0;
		$tl = count($tokens);
		for($i=0;$i<$tl;++$i){
			$token = $tokens[$i];
			if(!isset($token[1])){
				$migratedSource.=$token[0];
			} else {
				if($i<$tl){
					#$migratedSource.=$token[0];
				} else {
					if($token[0]==T_CLOSE_TAG){
						continue;
					}
				}
				if($token[0]==T_STRING){
					if($this->_isActiveRecordMethod(&$tokens, $i)==true){
						$token = $tokens[$i];
					} else {
						if($this->_isControllerMethod(&$tokens, $i)==true){
							$token = $tokens[$i];
						} else {
							if($this->_isCommonMethod(&$tokens, $i)==true){
								$token = $tokens[$i];
							} else {
								if($this->_isKumbiaPath(&$tokens, $i)==true){
									$token = $tokens[$i];
								}
							}
						}
					}
				} else {
					if($token[0]==T_EXTENDS){
						if(isset($tokens[$i+2][1])){
							if($tokens[$i+2][1]=='StandardForm'){
								$this->_isStandardForm = true;
							}
						}
					} else {
						if($token[0]==T_CLASS){
							if(isset($tokens[$i+2][1])){
								$this->_className = $tokens[$i+2][1];
							}
						}
					}
				}
				$migratedSource.=$token[1];
			}
		}
		return $migratedSource;
	}

	public function migrateAppController($source){
		$migratedSource = "";
		$tokens = token_get_all($source);
		$tl = count($tokens);
		for($i=0;$i<$tl;++$i){
			$token = $tokens[$i];
			if(!isset($token[1])){
				$migratedSource.=$token[0];
			} else {
				if($this->_isCommonMethod(&$tokens, $i)==true){
					$token = $tokens[$i];
				}
				$migratedSource.=$token[1];
			}
		}
		return $migratedSource;
	}

	/**
	 * Migra una vista de cualquier tipo
	 *
	 * @param	string $source
	 * @return	string
	 */
	public function migrateView($source){
		$migratedSource = "";
		$tokens = token_get_all($source);
		$tl = count($tokens);
		for($i=0;$i<$tl;++$i){
			$token = $tokens[$i];
			if(!isset($token[1])){
				$migratedSource.=$token[0];
			} else {
				if($token[0]==T_OPEN_TAG){
					$migratedSource.='<?php ';
				} else {
					if($token[0]==T_OPEN_TAG_WITH_ECHO){
						$migratedSource.='<?php echo ';
					} else {
						if($this->_isCommonMethod(&$tokens, $i)==true){
							$token = $tokens[$i];
						} else {
							if($this->_isTagHelper(&$tokens, $i)==true){
								$token = $tokens[$i];
							} else {
								if($this->_isViewContent(&$tokens, $i)==true){
									$token = $tokens[$i];
								} else {
									if($this->_isInViewActiveRecord(&$tokens, $i)==true){
										$token = $tokens[$i];
									} else {
										if($this->_isKumbiaPath(&$tokens, $i)==true){
											$token = $tokens[$i];
										} else {
											if($this->_isDbMethod(&$tokens, $i)==true){
												$token = $tokens[$i];
											}
										}
									}
								}
							}
						}
						$migratedSource.=$token[1];
					}
				}
			}
		}
		return $migratedSource;
	}

}