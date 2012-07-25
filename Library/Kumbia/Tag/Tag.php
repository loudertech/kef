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
 * @package 	Tag
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) 2007-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @copyright 	Copyright (c) 2007-2008 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Tag.php,v 5f278793c1ae 2011/10/27 02:50:13 andres $
 */

/**
 * Tag
 *
 * Este componente actua como una biblioteca de etiquetas que permite generar
 * tags XHTML en la presentación de una aplicación mediante métodos estáticos
 * PHP predefinidos flexibles que integran tecnología del lado del cliente
 * como CSS y Javascript.
 *
 * @category 	Kumbia
 * @package		Tag
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) 2007-2008 Emilio Rafael Silveira Tovar(emilio.rst at gmail.com)
 * @copyright 	Copyright (c) 2007-2008 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class Tag {

	/**
	 * Indica si se debe usar localizacion
	 *
	 * @var boolean
	 */
	private static $_useLocale = true;

	/**
	 * Valores de los componentes
	 *
	 * @var array
	 */
	private static $_displayValues = array();

	/**
	 * Titulo del Documento HTML
	 *
	 * @var string
	 */
	private static $_documentTitle = '';

	/**
	 * Indica si ya se ha incluido el base del framework
	 *
	 * @var boolean
	 */
	private static $_includedBase = false;

	/**
	 * PATHs a framework javascript
	 *
	 * @var array
	 */
	private static $_javascriptFrameworks = array(
		'scriptaculous' => 'core/framework/scriptaculous/protoculous',
		'prototype' => 'core/framework/scriptaculous/prototype',
		'jquery' => 'core/framework/jquery/jquery',
		'mootools' => 'core/framework/mootools/mootools',
		'ext' => 'core/framework/ext/ext'
	);

	/**
	 * Establece el valor de un componente de UI
	 *
	 * @param string $id
	 * @param string $value
	 */
	public static function displayTo($id, $value){
		#if[compile-time]
		if(is_object($value)||is_array($value)||is_resource($value)){
			throw new TagException('Solo valores escalares pueden ser asignados a los componentes UI');
		}
		#endif
		self::$_displayValues[$id] = $value;
	}

	public static function resetInput(){
		self::$_displayValues = array();
		foreach($_POST as $key => $value){
			unset($_POST[$key]);
		}
	}

	/**
	 * Obtiene el valor de un componente tomado
	 * del mismo valor del nombre del campo en $_displayValues
	 * del mismo nombre del controlador o el indice en
	 * $_POST
	 *
	 * @param string $name
	 * @return mixed
	 * @static
	 */
	public static function getValueFromAction($name){
		if(isset(self::$_displayValues[$name])){
			return self::$_displayValues[$name];
		} else {
			if(isset($_POST[$name])){
				if(get_magic_quotes_gpc()==false){
					return $_POST[$name];
				} else {
					return stripslashes($_POST[$name]);
				}
			} else {
				$controller = Dispatcher::getController();
				if(isset($controller->$name)){
					return $controller->$name;
				} else {
					return "";
				}
			}
		}
	}

	/**
	 * Crea un enlace en una aplicación respetando las convenciones del framework
	 *
	 * @param	string $action
	 * @param	string $text
	 * @return	string
	 */
	public static function linkTo($action, $text=''){
		if(func_num_args()>2){
			$numberArguments = func_num_args();
			$action = Utils::getParams(func_get_args(), $numberArguments);
		}
		if(is_array($action)){
			if(isset($action['confirm'])&&$action['confirm']){
				if(!isset($action['onclick'])){
					$action['onclick'] = "";
				}
				$action['onclick'] = 'if(!confirm(\''.$action['confirm'].'\')) { return false; }; '.$action['onclick'];
				unset($action['confirm']);
			}
			$code = '<a href="'.Utils::getKumbiaUrl($action).'" ';
			if(isset($action['text'])){
				$text = $action['text'];
			} else {
				if(isset($action[1])){
					$text = $action[1];
				}
			}
			foreach($action as $key => $value){
				if(!is_integer($key)){
					$code.=' '.$key.'="'.$value.'" ';
				}
			}
			$code.='>'.$text.'</a>';
			return $code;
		} else {
			if($text==="") {
				$text = str_replace('_', ' ', $action);
				$text = str_replace('/', ' ', $text);
				$text = ucwords($text);
			}
			return '<a href="'.Utils::getKumbiaUrl($action).'">'.$text.'</a>';
		}
	}

	/**
	 * Crea un enlace a una acción dentro del controlador Actual
 	 *
	 * @param string $action
	 * @param string $text
	 * @return string
	 */
	static public function linkToAction($action, $text=''){
		return self::linkTo(Router::getController().'/'.$action, $text);
	}

	/**
	 * Permite ejecutar una acción en la vista actual dentro de un contenedor
	 * HTML usando AJAX
	 *
	 * confirm:		Texto de Confirmación
	 * success:		Código JavaScript a ejecutar cuando termine la petición AJAX
	 * before:		Código JavaScript a ejecutar antes de la petición AJAX
	 * complete:	Código JavaScript que se ejecuta al terminar la petición AJAX
	 * update:		Que contenedor HTML será actualizado
	 * action:		Acción que ejecutará la petición AJAX
	 * text:		Texto del Enlace
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	static public function linkToRemote(){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		$code = '';
		#if[compile-time]
		if(!isset($params['update'])){
			throw new TagException('Debe indicar el elemento DOM donde se actualizará el resultado AJAX');
		}
		if(!isset($params['action'])&&!isset($params['url'])){
			throw new TagException('Debe indicar la acción AJAX');
		}
		#endif
		if(isset($params['action'])){
			$url = Utils::getKumbiaUrl($params['action']);
		} else {
			if(isset($params['url'])){
				$url = $params['url'];
			} else {
				throw new TagException('Debe indicar la acción ó URL AJAX');
			}
		}
		$callbacks = array('before', 'success', 'complete', 'error');
		$params['onclick'] = 'AJAX.update(\''.$url.'\', \''.$params['update'].'\'); return false;';
		unset($params['update']);
		unset($params['action']);
		return self::linkTo($params);
	}

	/**
	 * Helper que analiza el tipo de dato del campo indicado y produce el componente de captura adecuado
	 *
	 * @param array $params
	 */
	public static function inputField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(isset($params[0])){
			$name = $params[0];
		} else {
			throw new TagException('Debe indicar la convención modelo.atributo para generar el componente');
		}
		$arguments = array();
		$tableItems = explode('.', $name);
		if(isset($tableItems[0])){
			$entity = EntityManager::getEntityInstance($tableItems[0]);
			$dataTypes = $entity->getDataTypes();
			if($entity->hasField($tableItems[1])){
				$params[0] = $tableItems[1];
				if(strpos($dataTypes[$tableItems[1]], 'char')!==false){
					if(preg_match('/([0-9]+)/', $dataTypes[$tableItems[1]], $matches)){
						$params['size'] = $matches[1];
					}
					return self::textField($params);
				}
				if($dataTypes[$tableItems[1]]=='date'){
					return self::dateField($params);
				}
				if(strpos($dataTypes[$tableItems[1]], 'decimal')!==false){
					if(preg_match('/([0-9]+)/', $dataTypes[$tableItems[1]], $matches)){
						$params['size'] = $matches[1];
					}
					return self::numericField($params);
				}
				if(strpos($dataTypes[$tableItems[1]], 'int')!==false){
					if(preg_match('/([0-9]+)/', $dataTypes[$tableItems[1]], $matches)){
						$params['size'] = $matches[1];
					}
					return self::numericField($params);
				}
				if(strpos($dataTypes[$tableItems[1]], 'enum')!==false){
					$domain = array();
					if(preg_match('/\((.*)\)/', $dataTypes[$tableItems[1]], $matches)){
						foreach(explode(',', $matches[1]) as $item){
							$item = strtoupper(str_replace("'", '', $item));
							$domain[$item] = $item;
						}
					}
					$params[1] = $domain;
					return self::selectStatic($params);
				}
			} else {
				throw new TagException('El atributo no existe en el modelo');
			}
		} else {
			throw new TagException('Convención modelo.atributo inválida');
		}
	}

	/**
	 * Caja de texto que autocompleta los resultados
	 *
	 * @param mixed $params(
	 *   name
	 * 	 action
	 *   after_update
	 *   id (optional)
	 *   message (default Consultando....)
	 * )
	 * @return string
	 * @static
	 */
	public static function textFieldWithAutocomplete($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params['value'])){
			$params['value'] = self::getValueFromAction($params[0]);
		}
		$hash = mt_rand(1, 100);
		if(!isset($params['name'])||!$params['name']) {
			$params['name'] = $params[0];
		}
		if(!isset($params['action'])){
			throw new TagException('El parámetro "action" es requerido por este helper');
		}
		if(!isset($params['after_update'])||!$params['after_update']) {
			$params['after_update'] = "function(){}";
		}
		if(!isset($params['id'])||!$params['id']) {
			$params['id'] = $params['name'] ? $params['name'] : $params[0];
		}
		if(!isset($params['message'])||!$params['message']) {
			$params['message'] = "Consultando...";
		}
		if(!isset($params['param_name'])||!$params['param_name']) {
			$params['param_name'] = $params[0];
		}
		$code = "<input type='text' id='".$params[0]."' name='".$params['name']."'";
		foreach($params as $key => $value){
			if(!in_array($key, array('id', 'name', 'param_name', 'message', 'action', 'after_update'))){
				if(!is_integer($key)){
					$code.="$key='$value' ";
				}
			}
		}
		$instancePath = Core::getInstancePath();
		/*$code.= " />
		<span id='indicator$hash' style='display: none'><img src='".$instancePath."img/spinner.gif' alt='".$params['message']."'/></span>
		<div id='".$params[0]."_choices' class='autocomplete'></div>
		<script type='text/javascript'>
		// <![CDATA[
		new Ajax.Autocompleter(\"".$params[0]."\", \"".$params[0]."_choices\",Utils.getKumbiaURL(\"".$params['action']."\"), { minChars: 2, indicator: 'indicator$hash', afterUpdateElement : ".$params['after_update'].", paramName: '".$params['param_name']."'});
		// ]]>
		</script>";*/
		$changeEvent = ', ""';
		if(isset($params['change']) && $params['change']){
			$changeEvent = ', function(event,ui){'.$params['change'].'}';
		}
		$code.= " />
		<script type='text/javascript'>Base.onReady(function(){ BaseUI.autocomplete(\"".$params[0]."\", Utils.getKumbiaURL(\"".$params['action']."\") ".$changeEvent."); });</script>";
		return $code;
	}

	/**
	 * Crea un TextArea
	 *
	 * @access	public
	 * @param	array $configuration
	 * @return	string
	 * @static
	 */
	public static function textArea($configuration){
		$numberArguments = func_num_args();
		$configuration = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($configuration['name'])||$configuration['name']){
			$configuration['name'] = $configuration[0];
		}
		if(!isset($configuration['cols'])||!$configuration['cols']){
			$configuration['cols'] = 40;
		}
		if(!isset($configuration['rows'])||!$configuration['rows']){
			$configuration['rows'] = 25;
		}
		if(!isset($configuration['value'])){
			$value = self::getValueFromAction($configuration[0]);
		} else {
			$value = $configuration['value'];
		}
		return "<textarea id=\"".$configuration['name']."\" name=\"".$configuration['name']."\" cols=\"".$configuration['cols']."\" rows=\"".$configuration['rows']."\">".$value."</textarea>\r\n";
	}

	/**
	 * Crea una caja de texto que solo acepta numeros
	 *
	 * @param 	mixed $params
	 * @return 	string
	 * @static
	 */
	public static function numericField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params[0])){
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])||!$params['name']){
			$params['name'] = $params[0];
		}
		if(isset($params['value'])){
			$value = $params['value'];
			unset($params['value']);
		} else {
			$value = self::getValueFromAction($params[0]);
		}
		if(!isset($params['onkeydown'])) {
			$params['onkeydown'] = "NumericField.maskNum(event)";
		} else {
			$params['onkeydown'].=";NumericField.maskNum(event)";
		}
		$code = "<input type='text' id='".$params[0]."' value='$value' ";
		foreach($params as $key => $value){
			if(!is_integer($key)){
				$code.="$key='$value' ";
			}
		}
		$code.=" />\r\n";
		return $code;
	}

	/**
	 * Crea una caja de texto que solo acepta numeros y los formatea como moneda
	 *
	 * @param 	mixed $params
	 * @return 	string
	 * @static
	 */
	public static function moneyField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params[0])){
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])||!$params['name']){
			$params['name'] = $params[0];
		}
		if(isset($params['value'])){
			$value = $params['value'];
			unset($params['value']);
		} else {
			$value = self::getValueFromAction($params[0]);
		}
		if(!isset($params['onkeydown'])) {
			$params['onkeydown'] = "NumericField.maskNum(event)";
		} else {
			$params['onkeydown'].=";NumericField.maskNum(event)";
		}
        if(!isset($params['formatOptions'])){
            $params['formatOptions'] = '';
        }
        if(isset($params['objectFormat'])){
		    if(!isset($params['onblur'])) {
			    $params['onblur'] = "this.value=".$params['objectFormat'].".money(this.value);";
		    } else {
			    $params['onblur'].= ";this.value=".$params['objectFormat'].".money(this.value);";
		    }
		    if(!isset($params['onfocus'])) {
			    $params['onfocus'] = "this.value=".$params['objectFormat'].".deFormat(this.value,\"money\");this.activate();";
		    } else {
			    $params['onfocus'].= ";this.value=".$params['objectFormat'].".deFormat(this.value,\"money\");this.activate();";
		    }
            $codeAlt = "<script type='text/javascript'>\n\t$('".$params[0]."').value=".$params['objectFormat'].".money($('".$params[0]."').value);\n</script>\r\n";
            unset($params['objectFormat']);
        }else{
		    if(!isset($params['onblur'])) {
			    $params['onblur'] = "defaultFormater=new Format(".$params['formatOptions'].");this.value=defaultFormater.money(this.value);";
		    } else {
			    $params['onblur'].=";defaultFormater=new Format(".$params['formatOptions'].");this.value=defaultFormater.money(this.value);";
		    }
		    if(!isset($params['onfocus'])) {
			    $params['onfocus'] = "defaultFormater=new Format(".$params['formatOptions'].");this.value=defaultFormater.deFormat(this.value,\"money\");this.activate();";
		    } else {
			    $params['onfocus'].= ";defaultFormater=new Format(".$params['formatOptions'].");this.value=defaultFormater.deFormat(this.value,\"money\");this.activate();";
		    }
            $codeAlt = "<script type='text/javascript'>\n\tdefaultFormater=new Format(".$params['formatOptions'].");\n\t$('".$params[0]."').value=defaultFormater.money($('".$params[0]."').value);\n</script>\r\n";
        }
        unset($params['formatOptions']);
        $params['format'] = 'money';
		$code = "<input type='text' id='".$params[0]."' value='$value' ";
		foreach($params as $key => $val){
			if(!is_integer($key)){
				$code.="$key='$val' ";
			}
		}
		$code.=" />\r\n";
        if(isset($codeAlt) && (!empty($value) || $value == 0)){
            $code.= $codeAlt;
        }
		return $code;
	}

	/**
	 * Crea una caja de texto que solo acepta numeros y los formatea como porcentaje
	 *
	 * @param 	mixed $params
	 * @return 	string
	 * @static
	 */
	public static function percentField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params[0])){
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])||!$params['name']){
			$params['name'] = $params[0];
		}
		if(isset($params['value'])){
			$value = $params['value'];
			unset($params['value']);
		} else {
			$value = self::getValueFromAction($params[0]);
		}
		if(!isset($params['onkeydown'])) {
			$params['onkeydown'] = "NumericField.maskNum(event)";
		} else {
			$params['onkeydown'].=";NumericField.maskNum(event)";
		}
        if(!isset($params['formatOptions'])){
            $params['formatOptions'] = '';
        }
        if(isset($params['objectFormat'])){
		    if(!isset($params['onblur'])) {
			    $params['onblur'] = "this.value=".$params['objectFormat'].".percent(this.value);";
		    } else {
			    $params['onblur'].= ";this.value=".$params['objectFormat'].".percent(this.value);";
		    }
		    if(!isset($params['onfocus'])) {
			    $params['onfocus'] = "this.value=".$params['objectFormat'].".deFormat(this.value,\"percent\");this.activate();";
		    } else {
			    $params['onfocus'].= ";this.value=".$params['objectFormat'].".deFormat(this.value,\"percent\");this.activate();";
		    }
            $codeAlt = "<script type='text/javascript'>\n\t$('".$params[0]."').value=".$params['objectFormat'].".percent($('".$params[0]."').value);\n</script>\r\n";
            unset($params['objectFormat']);
        }else{
		    if(!isset($params['onblur'])) {
			    $params['onblur'] = "defaultFormater=new Format(".$params['formatOptions'].");this.value=defaultFormater.percent(this.value);";
		    } else {
			    $params['onblur'].=";defaultFormater=new Format(".$params['formatOptions'].");this.value=defaultFormater.percent(this.value);";
		    }
		    if(!isset($params['onfocus'])) {
			    $params['onfocus'] = "defaultFormater=new Format(".$params['formatOptions'].");this.value=defaultFormater.deFormat(this.value,\"percent\");this.activate();";
		    } else {
			    $params['onfocus'].= ";defaultFormater=new Format(".$params['formatOptions'].");this.value=defaultFormater.deFormat(this.value,\"percent\");this.activate();";
		    }
            $codeAlt = "<script type='text/javascript'>\n\tdefaultFormater=new Format(".$params['formatOptions'].");\n\t$('".$params[0]."').value=defaultFormater.percent($('".$params[0]."').value);\n</script>\r\n";
        }
        unset($params['formatOptions']);
        $params['format'] = 'percent';
		$code = "<input type='text' id='".$params[0]."' value='$value' ";
		foreach($params as $key => $val){
			if(!is_integer($key)){
				$code.=$key."='$val' ";
			}
		}
		$code.=" />\r\n";
        if(isset($codeAlt) && (!empty($value) || $value == 0)){
            $code.= $codeAlt;
        }
		return $code;
	}

	/**
	 * Crea una caja de password que solo acepta números
	 *
	 * @param 	mixed $params
	 * @return 	string
	 */
	public static function numericPasswordField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		$value = self::getValueFromAction($params);
		if(!isset($params[0])||!$params[0]) {
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])||!$params['name']){
			$params['name'] = $params[0];
		}
		if(!$value) {
			$value = isset($params['value']) ? $params['value'] : "";
		}
		if(!isset($params['onkeydown'])) {
			$params['onkeydown'] = "NumericField.maskNum(event)";
		} else {
			$params['onkeydown'].=";NumericField.maskNum(event)";
		}
		$code = "<input type='password' id='".$params[0]."' value='$value' ";
		foreach($params as $key => $value){
			if(!is_integer($key)){
				$code.="$key='$value' ";
			}
		}
		$code.=" />\r\n";
		return $code;
	}

	/**
	 * Helper para capturar meses
	 *
	 * @access 	public
	 * @param 	mixed $params
	 * @return 	string
	 * @static
	 */
	public static function monthField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(self::$_useLocale){
			if(!isset($params['locale'])){
				$locale = Locale::getApplication();
			} else {
				$locale = $params['locale'];
			}
			if($locale->isDefaultLocale()==false){
				$i = 1;
				$months = array();
				$monthNames = $locale->getMonthList();
				foreach($monthNames as $monthName){
					if($i<10){
						$months['0'.$i] = ucfirst($monthName);
					} else {
						$months[$i] = ucfirst($monthName);
					}
					++$i;
				}
			}
		}
		if(!isset($months)){
			$months = array(
				'01' => 'Enero', '02' => 'Febrero',
				'03' => 'Marzo', '04' => 'Abril',
				'05' => 'Mayo', '06' => 'Junio',
				'07' => 'Julio', '08' => 'Agosto',
				'09' => 'Septiembre', '10' => 'Octubre',
				'11' => 'Noviembre', '12' => 'Diciembre'
			);
		}
		return self::selectStatic($params[0], $months);
	}

	/**
	 * Helper para capturar fechas.
     * Crear un conjunto de Tags HTML de Selects que permite agregar fecha en un campo.
	 *
	 * @access 	public
	 * @param 	mixed $params : Array con la configuración de de este campo
     * Dentro de este podemos encontrar las opciones:
     * Array(
     *  - id        : Es el string del id que se usa en el que contendra el valor seleccionado
     *  - name      : Es similar a id sino solo se agrega id se le asigna a id tambien este nombre
     *  - value     : Es el valor por defecto que tiene ese campo. Si no existe se deja dia:0,mes:0,año: actual.
     *  - today     : Indica si tiene un valor que use la fecha actual como defecto
     *  - useDummy  : Indica que coloque valores dummy para indicar no seleccionado.
     *  - locale    : Indica la localización (El languaje) De los meses
     *  - months    : Indica si es "complete" coloca los nombres de los meses completos ,por defecto tres digitos.
     * );
	 * @return 	string
	 * @static
	 */
	public static function dateField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);

		if(!isset($params[0])){
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])||!$params['name']) {
			$params['name'] = $params[0];
		}
		if(isset($params['value'])){
			$value = $params['value'];
			unset($params['value']);
		} else {
			$value = self::getValueFromAction($params[0]);
		}
		$flagValueExists = false;
		if($value){
			$flagValueExists = true;
			$year = substr($value, 0, 4);
			$month = substr($value, 5, 2);
			$day = substr($value, 8, 2);
		} else {
			if(isset($params['today'])&&$params['today']){
				$value = date('Y-m-d');
				$year = substr($value, 0, 4);
				$month = substr($value, 5, 2);
				$day = substr($value, 8, 2);
			} else {
				$year = date('Y');
				$month = 0;
				$day = 0;
			}
		}

		if(isset($params['useDummy'])&&$params['useDummy']){
			$useDummy = true;
			unset($params['useDummy']);
		} else {
			$useDummy = false;
		}
		$attributes = array();
		foreach($params as $_key => $_value){
			if(in_array($_key, array('name'))==false&&!is_integer($_key)){
				$attributes[] = $_key.'="'.$_value.'"';
			}
		}

		$code = '<table cellspacing="0" '.join(' ', $attributes).'><tr><td>';
		if(self::$_useLocale){
			if(!isset($params['locale'])){
				$locale = Locale::getApplication();
			} else {
				$locale = $params['locale'];
			}
			if($locale->isDefaultLocale()==false){
				$months = array();
				$i = 1;
				if(isset($params['months'])){
					if($params['months']=='complete'){
						$monthNames = $locale->getMonthList();
					} else {
						$monthNames = $locale->getAbrevMonthList();
					}
				} else {
					$monthNames = $locale->getAbrevMonthList();
				}
				foreach($monthNames as $monthName){
					if($i<10){
						$months['0'.$i] = ucfirst($monthName);
					} else {
						$months[$i] = ucfirst($monthName);
					}
					++$i;
				}
			}
		}
		$monthTable = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		if($month==2){
			if($year%4==0){
				$numberDays = 29;
			} else {
				$numberDays = 28;
			}
		} else {
			if($month>0){
				$numberDays = $monthTable[$month-1];
			} else {
				$numberDays = 31;
			}
		}
		if(!isset($months)){
			if(isset($params['months'])&&$params['months']=='complete'){
				$months = array(
					'01' => 'Enero', '02' => 'Febrero',
					'03' => 'Marzo', '04' => 'Abril',
					'05' => 'Mayo', '06' => 'Junio',
					'07' => 'Julio', '08' => 'Agosto',
					'09' => 'Septiembre', '10' => 'Octubre',
					'11' => 'Noviembre', '12' => 'Diciembre'
				);
			} else {
				$months = array(
					'01' => 'Ene', '02' => 'Feb',
					'03' => 'Mar', '04' => 'Abr',
					'05' => 'May', '06' => 'Jun',
					'07' => 'Jul', '08' => 'Ago',
					'09' => 'Sep', '10' => 'Oct',
					'11' => 'Nov', '12' => 'Dic'
				);
			}
		}
		if($useDummy && $flagValueExists==false){
			$displayJS = 'if(this.selectedIndex>0){Base.show(\''.$params[0].'Day\');Base.show(\''.$params[0].'Year\')}else{Base.hide(\''.$params[0].'Day\');Base.hide(\''.$params[0].'Year\');$(\''.$params[0].'\').setValue(\'\')};';
			$display = 'style="display:none"';
		} else {
			$displayJS = '';
			$display = '';
		}

		$code.='<select id="'.$params[0].'Month" onchange="'.$displayJS.'DateField.refresh(\''.$params[0].'\', this)">';
		if($useDummy){
			$code.='<option value="@">Sel...</option>';
		}
		foreach($months as $number => $name){
			if($number==$month){
				$code.='<option value="'.$number.'" selected="selected">'.$name.'</option>';
			} else {
				$code.='<option value="'.$number.'">'.$name.'</option>';
			}
		}
		$code.="</select></td><td>";

		$code.='<select id="'.$params[0].'Day" onchange="DateField.refresh(\''.$params[0].'\', this)" '.$display.">";
		for($i=1;$i<=$numberDays;++$i){
			$number = $i<10 ? '0'.$i : $i;
			if($number==$day){
				$code.='<option value="'.$number.'" selected="selected">'.$number.'</option>';
			} else {
				$code.='<option value="'.$number.'">'.$number.'</option>';
			}
		}
		$code.='</select></td><td>';
		$code.='<select id="'.$params[0].'Year" onchange="DateField.refresh(\''.$params[0].'\', this)" '.$display.">";
		if(isset($params['startYear'])){
			$startYear = $params['startYear'];
		} else {
			$startYear = 1925;
		}
		if(isset($params['finalYear'])){
			$finalYear = $params['finalYear'];
		} else {
			$finalYear = date('Y')+5;
		}
		for($i=$finalYear;$i>=$startYear;$i--){
			if($i==$year){
				$code.='<option value="'.$i.'" selected="selected">'.$i.'</option>';
			} else {
				$code.='<option value="'.$i.'">'.$i.'</option>';
			}
		}
		$code.="</select>";
		$code.="<input type='hidden' id='".$params[0]."' name='".$params[0]."' value='$value'/>";

		if(isset($params['calendar']) && $params['calendar']==true){
			$code .= '<script type="text/javascript">$( "#'.$params[0].'" ).datepicker({
				showOn: "button",
				buttonImage: $Kumbia.path+"img/calendar.gif",
				buttonImageOnly: true,
				dateFormat: "yy-mm-dd",
				defaultDate: "'.date('Y-m-d').'",
				onSelect: function(dateText, inst){
					var dateArray = dateText.split("-");
					$("#'.$params[0].'Day").val(dateArray[2]).show();
					$("#'.$params[0].'Month").val(dateArray[1]).show();
					$("#'.$params[0].'Year").val(dateArray[0]).show();
				}
			});</script>';
		}

		$code.="</td>";
		//$code.='<td><img class="calendarIcon" src="'.Core::getInstancePath().'img/calendar.gif" onclick="DateField.showCalendar(this, \''.$params[0].'\')" alt="Seleccionar Fecha"/></td>';
		$code.="</tr></table>";

		return $code;
	}

	/**
	 * Helper para capturar fechas.
     * Crear un conjunto de Tags HTML de Selects que permite agregar fecha en un campo.
	 *
	 * @access 	public
	 * @param 	mixed $params : Array con la configuración de de este campo
     * Dentro de este podemos encontrar las opciones:
     * Array(
     *  - id        : Es el string del id que se usa en el que contendra el valor seleccionado
     *  - name      : Es similar a id sino solo se agrega id se le asigna a id tambien este nombre
     *  - value     : Es el valor por defecto que tiene ese campo. Si no existe se deja dia:0,mes:0,año: actual.
     *  - today     : Indica si tiene un valor que use la fecha actual como defecto
     *  - useDummy  : Indica que coloque valores dummy para indicar no seleccionado.
     *  - locale    : Indica la localización (El languaje) De los meses
     *  - months    : Indica si es "complete" coloca los nombres de los meses completos ,por defecto tres digitos.
     * );
	 * @return 	string
	 * @static
	 */
	public static function date360Field($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);

		if(!isset($params[0])){
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])||!$params['name']) {
			$params['name'] = $params[0];
		}
		if(isset($params['value'])){
			$value = $params['value'];
			unset($params['value']);
		} else {
			$value = self::getValueFromAction($params[0]);
		}
		$flagValueExists = false;
		if($value){
			$flagValueExists = true;
			$year = substr($value, 0, 4);
			$month = substr($value, 5, 2);
			$day = substr($value, 8, 2);
		} else {
			if(isset($params['today'])&&$params['today']){
				$value = date('Y-m-d');
				$year = substr($value, 0, 4);
				$month = substr($value, 5, 2);
				$day = substr($value, 8, 2);
			} else {
				$year = date('Y');
				$month = 0;
				$day = 0;
			}
		}

		if(isset($params['useDummy'])&&$params['useDummy']){
			$useDummy = true;
			unset($params['useDummy']);
		} else {
			$useDummy = false;
		}
		$attributes = array();
		foreach($params as $_key => $_value){
			if(in_array($_key, array('name'))==false&&!is_integer($_key)){
				$attributes[] = $_key.'="'.$_value.'"';
			}
		}

		$code = '<table cellspacing="0" '.join(' ', $attributes).'><tr><td>';
		if(self::$_useLocale){
			if(!isset($params['locale'])){
				$locale = Locale::getApplication();
			} else {
				$locale = $params['locale'];
			}
			if($locale->isDefaultLocale()==false){
				$months = array();
				$i = 1;
				if(isset($params['months'])){
					if($params['months']=='complete'){
						$monthNames = $locale->getMonthList();
					} else {
						$monthNames = $locale->getAbrevMonthList();
					}
				} else {
					$monthNames = $locale->getAbrevMonthList();
				}
				foreach($monthNames as $monthName){
					if($i<10){
						$months['0'.$i] = ucfirst($monthName);
					} else {
						$months[$i] = ucfirst($monthName);
					}
					++$i;
				}
			}
		}
		$monthTable = array(30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30);
		if($month==2){
			if($year%4==0){
				$numberDays = 30;
			} else {
				$numberDays = 30;
			}
		} else {
			if($month>0){
				$numberDays = $monthTable[$month-1];
			} else {
				$numberDays = 30;
			}
		}
		if(!isset($months)){
			if(isset($params['months'])&&$params['months']=='complete'){
				$months = array(
					'01' => 'Enero', '02' => 'Febrero',
					'03' => 'Marzo', '04' => 'Abril',
					'05' => 'Mayo', '06' => 'Junio',
					'07' => 'Julio', '08' => 'Agosto',
					'09' => 'Septiembre', '10' => 'Octubre',
					'11' => 'Noviembre', '12' => 'Diciembre'
				);
			} else {
				$months = array(
					'01' => 'Ene', '02' => 'Feb',
					'03' => 'Mar', '04' => 'Abr',
					'05' => 'May', '06' => 'Jun',
					'07' => 'Jul', '08' => 'Ago',
					'09' => 'Sep', '10' => 'Oct',
					'11' => 'Nov', '12' => 'Dic'
				);
			}
		}
		if($useDummy && $flagValueExists==false){
			$displayJS = 'if(this.selectedIndex>0){Base.show(\''.$params[0].'Day\');Base.show(\''.$params[0].'Year\')}else{Base.hide(\''.$params[0].'Day\');Base.hide(\''.$params[0].'Year\');$(\''.$params[0].'\').setValue(\'\')};';
			$display = 'style="display:none"';
		} else {
			$displayJS = '';
			$display = '';
		}

		$code.='<select id="'.$params[0].'Month" onchange="'.$displayJS.'DateField.refresh(\''.$params[0].'\', this)">';
		if($useDummy){
			$code.='<option value="@">Sel...</option>';
		}
		foreach($months as $number => $name){
			if($number==$month){
				$code.='<option value="'.$number.'" selected="selected">'.$name.'</option>';
			} else {
				$code.='<option value="'.$number.'">'.$name.'</option>';
			}
		}
		$code.="</select></td><td>";

		$code.='<select id="'.$params[0].'Day" onchange="DateField.refresh(\''.$params[0].'\', this)" '.$display.">";
		for($i=1;$i<=$numberDays;++$i){
			$number = $i<10 ? '0'.$i : $i;
			if($number==$day){
				$code.='<option value="'.$number.'" selected="selected">'.$number.'</option>';
			} else {
				$code.='<option value="'.$number.'">'.$number.'</option>';
			}
		}
		$code.='</select></td><td>';
		$code.='<select id="'.$params[0].'Year" onchange="DateField.refresh(\''.$params[0].'\', this)" '.$display.">";
		if(isset($params['startYear'])){
			$startYear = $params['startYear'];
		} else {
			$startYear = 1925;
		}
		if(isset($params['finalYear'])){
			$finalYear = $params['finalYear'];
		} else {
			$finalYear = date('Y')+5;
		}
		for($i=$finalYear;$i>=$startYear;$i--){
			if($i==$year){
				$code.='<option value="'.$i.'" selected="selected">'.$i.'</option>';
			} else {
				$code.='<option value="'.$i.'">'.$i.'</option>';
			}
		}
		$code.="</select>";
		$code.="<input type='hidden' id='".$params[0]."' name='".$params[0]."' value='$value'/>";

		if(isset($params['calendar']) && $params['calendar']==true){
			$code .= '<script type="text/javascript">$( "#'.$params[0].'" ).datepicker({
				showOn: "button",
				buttonImage: $Kumbia.path+"img/calendar.gif",
				buttonImageOnly: true,
				dateFormat: "yy-mm-dd",
				defaultDate: "'.date('Y-m-d').'",
				onSelect: function(dateText, inst){
					var dateArray = dateText.split("-");
					$("#'.$params[0].'Day").val(dateArray[2]).show();
					$("#'.$params[0].'Month").val(dateArray[1]).show();
					$("#'.$params[0].'Year").val(dateArray[0]).show();
				}
			});</script>';
		}

		$code.="</td>";
		//$code.='<td><img class="calendarIcon" src="'.Core::getInstancePath().'img/calendar.gif" onclick="DateField.showCalendar(this, \''.$params[0].'\')" alt="Seleccionar Fecha"/></td>';
		$code.="</tr></table>";

		return $code;
	}

	/**
	 * Inicializa los DatePicker que vayán a ser inscrustados en el código
	 *
	 */
	public static function initDatePicker(){
		Tag::addJavascript('core/calendar-source');
		if(self::$_useLocale){
			if(!isset($params['locale'])){
				$locale = Locale::getApplication();
			} else {
				$locale = $params['locale'];
			}
			if($locale->isDefaultLocale()==false){
				$months = array();
				$monthNames = $locale->getMonthList();
				foreach($monthNames as $monthName){
					$months[] = '"'.ucfirst($monthName).'"';
				}
				echo '<script type="text/javascript">MonthNames = [', join(', ', $months).'];</script>';
				$days = array();
				$daysNames = $locale->getDaysNamesList();
				foreach($daysNames as $dayName){
					$days[] = '"'.ucfirst(substr($dayName, 0, 1)).'"';
				}
				echo '<script type="text/javascript">WeekDays = [', join(', ', $days).'];</script>';
			}
		}
	}

	/**
	 * Agrega un input de fecha con calendario
	 *
	 * @param array $params
	 */
	public static function datePickerField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);

		if(!isset($params[0])){
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])||!$params['name']) {
			$params['name'] = $params[0];
		}
		if(isset($params['value'])){
			$value = $params['value'];
			unset($params['value']);
		} else {
			$value = self::getValueFromAction($params[0]);
		}

		if($value){
			$year = substr($value, 0, 4);
			$month = substr($value, 5, 2);
			$day = substr($value, 8, 2);
		} else {
			if(isset($params['today'])&&$params['today']){
				$value = date('Y-m-d');
				$year = substr($value, 0, 4);
				$month = substr($value, 5, 2);
				$day = substr($value, 8, 2);
			} else {
				$year = date('Y');
				$month = 0;
				$day = 0;
			}
		}

		if($day>0){
			echo '<script type="text/javascript">var vdateId = "id_', $params[0], '"; DateInput("', $params[0], '", true, "YYYY-MM-DD", "', $value, '")</script>';
		} else {
			echo '<script type="text/javascript">var vdateId = "id_', $params[0], '"; DateInput("', $params[0], '", false, "YYYY-MM-DD")</script>';
		}

	}

	/**
	 * Helper para capturar fechas.
     * Crear un conjunto de Tags HTML de Selects que permite agregar fecha en un campo.
	 *
	 * @access 	public
	 * @param 	mixed $params : Array con la configuración de de este campo
     * Dentro de este podemos encontrar las opciones:
     * Array(
     *  - id        : Es el string del id que se usa en el que contendra el valor seleccionado
     *  - name      : Es similar a id sino solo se agrega id se le asigna a id tambien este nombre
     *  - value     : Es el valor por defecto que tiene ese campo. Si no existe se deja dia:0,mes:0,año: actual.
     *  - active    : Indica si el valor por defecto debe ser la hora actual
     *  - locale    : Indica la localización (El languaje) De los meses
     * );
	 * @return 	string
	 * @static
	 */
	public static function timeField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);


		if(!isset($params[0])){
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])||!$params['name']) {
			$params['name'] = $params[0];
		}
		if(isset($params['value'])){
			$value = $params['value'];
			unset($params['value']);
		} else {
			$value = self::getValueFromAction($params[0]);
		}

		if($value){
			$hours = substr($value, 0, 2);
			$minutes = substr($value, 3, 2);
		} else {
			if(isset($params['active'])&&$params['active']){
				$value = date('G:i');
				$hours = substr($value, 0, 2);
				$minutes = substr($value, 3, 2);
			} else {
				$hours = '00';
				$minutes = '00';
			}
		}

		$attributes = array();
		foreach($params as $_key => $_value){
			if(in_array($_key, array('name'))==false&&!is_integer($_key)){
				$attributes[] = $_key.'="'.$_value.'"';
			}
		}

		$code = '<table cellspacing="0" '.join(' ', $attributes).'><tr><td>';
		if(self::$_useLocale){
			if(!isset($params['locale'])){
				$locale = Locale::getApplication();
			} else {
				$locale = $params['locale'];
			}
			if($locale->isDefaultLocale()==false){

			}
		}

		$code.='<select id="'.$params[0].'Hour" onchange="TimeField.refresh(\''.$params[0].'\', this)">';
		for($i=0;$i<=23;++$i){
			$number = $i<10 ? '0'.$i : $i;
			if($number==$hours){
				$code.='<option value="'.$number.'" selected="selected">'.$number.'</option>';
			} else {
				$code.='<option value="'.$number.'">'.$number.'</option>';
			}
		}
		$code.="</select></td><td>";

		$code.='<select id="'.$params[0].'Minutes" onchange="TimeField.refresh(\''.$params[0].'\', this)">';
		for($i=0;$i<=59;++$i){
			$number = $i<10 ? '0'.$i : $i;
			if($number==$minutes){
				$code.='<option value="'.$number.'" selected="selected">'.$number.'</option>';
			} else {
				$code.='<option value="'.$number.'">'.$number.'</option>';
			}
		}
		$code.='</select></td>';

		$code.="</tr></table>";
		$code.="<input type='hidden' id='".$params[0]."' name='".$params[0]."' value='$value'/>";
		return $code;
	}

	/**
	 * Helper para capturar idiomas
	 *
	 * @access 	public
	 * @param 	mixed $params
	 * @return 	string
	 * @static
	 */
	public static function languageField($params=''){
		$numberArguments = func_num_args();
		$arguments = func_get_args();
		$params = Utils::getParams($arguments, $numberArguments);
		if(!isset($params['locale'])){
			$locale = Locale::getApplication();
		} else {
			$locale = $params['locale'];
		}
		$params[1] = array();
		foreach($locale->getLanguagesList() as $code => $language){
		 	$params[1][$code] = ucfirst($language);
		}
		return self::selectStatic($params);
	}

	/**
	 * Helper para capturar territorios
	 *
	 * @access 	public
	 * @param 	mixed $params
	 * @return 	string
	 * @static
	 */
	public static function territoryField($params=''){
		$numberArguments = func_num_args();
		$arguments = func_get_args();
		$params = Utils::getParams($arguments, $numberArguments);
		if(!isset($params['locale'])){
			$locale = Locale::getApplication();
		} else {
			$locale = $params['locale'];
		}
		$params[1] = array();
		foreach($locale->getTerritoriesList() as $code => $territory){
		 	$params[1][$code] = ucfirst($territory);
		}
		sort($params[1]);
		return self::selectStatic($params);
	}

	/**
	 * Helper para capturar zonas horarias
	 *
	 * @access 	public
	 * @param 	mixed $params
	 * @return 	string
	 * @static
	 */
	public static function timezoneField($params=''){
		$numberArguments = func_num_args();
		$arguments = func_get_args();
		$params = Utils::getParams($arguments, $numberArguments);
		if(!isset($params['locale'])){
			$locale = Locale::getApplication();
		} else {
			$locale = $params['locale'];
		}
		$params[1] = array();
		foreach($locale->getTimezonesList() as $code => $timezone){
		 	$params[1][$code] = ucfirst($timezone);
		}
		sort($params[1]);
		return self::selectStatic($params);
	}

	/**
	 * Crea un combo que toma los valores de un array
	 *
	 * @param 	mixed $params
	 * @param 	string $data
	 * @return 	string
	 */
	public static function selectStatic($params='', $data=''){
		$numberArguments = func_num_args();
		$arguments = func_get_args();
		$params = Utils::getParams($arguments, $numberArguments);
		if(is_array($params)){
			$value = "";
			if(!isset($params['value'])){
				$value = self::getValueFromAction($params[0]);
				unset($params['value']);
			} else {
				$value = $params['value'];
			}
			$code = '<select id="'.$params[0].'" name="'.$params[0].'" ';
			if(!isset($params['dummyValue'])){
				$dummyValue = '@';
			} else {
				$dummyValue = $params['dummyValue'];
				unset($params['dummyValue']);
			}
			if(!isset($params['dummyText'])){
				$dummyText = 'Seleccione...';
			} else {
				$dummyText = $params['dummyText'];
				unset($params['dummyText']);
			}
			foreach($params as $attributeName => $attributeValue){
				if(!is_integer($attributeName)){
					if(!is_array($attributeValue)){
						$code.= $attributeName.'="'.$attributeValue.'" ';
					}
				}
			}
			$code.=">\r\n";
			if(isset($params['use_dummy'])){
				$code.= "\t<option value='$dummyValue'>$dummyText</option>\r\n";
				unset($params['use_dummy']);
			} else {
				if(isset($params['useDummy'])&&$params['useDummy']){
					$code.= "\t<option value='$dummyValue'>$dummyText</option>\r\n";
					unset($params['useDummy']);
				}
			}
			if(is_array($params[1])){
				foreach($params[1] as $optionValue => $optionText){
					if($optionValue==$value && $value!==''){
						$code.= "\t<option value='$optionValue' selected='selected'>$optionText</option>\r\n";
					} else {
						$code.= "\t<option value='$optionValue'>$optionText</option>\r\n";
					}
				}
			}
			$code.= "</select>\r\n";
		}
		return $code;
	}

	/**
	 * Crea una lista SELECT
	 *
	 * @access 	public
	 * @param 	mixed $params
	 * @param 	array $data
	 * @static
	 */
	public static function select($params='', $data=''){
		if(func_num_args()>1){
			$numberArguments = func_num_args();
			$params = Utils::getParams(func_get_args(), $numberArguments);
		}
		if(is_array($params)){
			if(!isset($params['value'])){
				$value = self::getValueFromAction($params[0]);
			} else {
				$value = $params['value'];
			}
			$callback = false;
			if(isset($params['option_callback'])){
				if(strpos($params['option_callback'], '.')){
					$callback = explode('.', $params['option_callback']);
				} else {
					$callback = $params['option_callback'];
				}
				#if[compile-time]
				if(is_callable($callback)==false){
					throw new TagException('El option_callback no es valido');
				}
				#endif
				unset($params['option_callback']);
			}
			$code ="<select id='".$params[0]."' name='".$params[0]."' ";
			if(is_array($params)){
				foreach($params as $at => $val){
					if(!is_integer($at)){
						if(!is_array($val)&&!in_array($at, array('using', 'use_dummy'))){
							$code.="$at='".$val."' ";
						}
					}
				}
			}
			$code.=">\r\n";

			if(!isset($params['dummyValue'])){
				$dummyValue = '@';
			} else {
				$dummyValue = $params['dummyValue'];
				unset($params['dummyValue']);
			}
			if(!isset($params['dummyText'])){
				$dummyText = 'Seleccione...';
			} else {
				$dummyText = $params['dummyText'];
				unset($params['dummyText']);
			}

			if(isset($params['use_dummy'])&&$params['use_dummy']==true){
				$code.="\t<option value='$dummyValue'>$dummyText</option>\r\n";
			} else {
				if(isset($params['useDummy'])&&$params['useDummy']==true){
					$code.="\t<option value='$dummyValue'>$dummyText...</option>\r\n";
				}
			}
			if(is_object($params[1])){
				#if[compile-time]
				if(!isset($params['using'])){
					throw new TagException("Debe indicar el parámetro 'using' para el helper Tag::select()");
				}
				#endif
				$using = explode(',', $params['using']);
				foreach($params[1] as $o){
					if($callback==false){
						if($value==$o->readAttribute($using[0])){
							$code.="\t<option selected='selected' value='".trim($o->readAttribute($using[0]))."'>".trim($o->readAttribute($using[1]))."</option>\r\n";
						} else {
							$code.="\t<option value='".trim($o->readAttribute($using[0]))."'>".trim($o->readAttribute($using[1]))."</option>\r\n";
						}
					} else {
						$code.=call_user_func_array($callback, array($o, $value));
					}
				}
			} else {
				if(is_array($params[1])){
					foreach($params[1] as $d){
						$code.="\t<option value='".$d[0]."'>".$d[1]."</option>\r\n";
					}
				} else {
					throw new TagException("La collección de opciones no es valida");
				}
			}
			$code.= "</select>\r\n";
		} else {
			$code = "<select id='$params' name='$params'></select>";
		}
		return $code;
	}

	/**
	 * Crea una lista SELECT cuyos textos de las opciones estan localizados
	 *
	 * @access 	public
	 * @param 	mixed $params
	 * @param 	array $data
	 * @param 	Traslate $traslate
	 * @return 	string
	 * @static
	 */
	public static function localeSelect($params='', $data='', $traslate=''){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(is_array($params)){
			if(!isset($params['value'])){
				$value = self::getValueFromAction($params[0]);
			} else {
				$value = $params['value'];
			}
			$callback = false;
			if(isset($params['option_callback'])){
				if(strpos($params['option_callback'], '.')){
					$callback = explode('.', $params['option_callback']);
				} else {
					$callback = $params['option_callback'];
				}
				#if[compile-time]
				if(is_callable($callback)==false){
					throw new TagException('El option_callback no es valido');
				}
				#endif
				unset($params['option_callback']);
			}
			$code ="<select id='".$params[0]."' name='".$params[0]."' ";
			if(is_array($params)){
				foreach($params as $at => $val){
					if(!is_integer($at)){
						if(!is_array($val)&&!in_array($at, array('using', 'use_dummy'))){
							$code.="$at='".$val."' ";
						}
					}
				}
			}
			$code.=">\r\n";
			if(isset($params['use_dummy'])&&$params['use_dummy']==true){
				$code.="\t<option value='@'>Seleccione...</option>\r\n";
			}
			if(!isset($params[2])){
				throw new TagException('Debe indicar el recurso de traducción');
			} else {
				$traslate = $params[2];
			}
			if(is_object($params[1])){
				#if[compile-time]
				if(!isset($params['using'])){
					throw new TagException('Debe indicar el parámetro "using" para el helper Tag::select()');
				}
				#endif
				$using = explode(',', $params['using']);
				foreach($params[1] as $o){
					if($callback==false){
						if($value==$o->readAttribute($using[0])){
							$code.="\t<option selected='selected' value='".$o->readAttribute($using[0])."'>".$traslate->_($o->readAttribute($using[1]))."</option>\r\n";
						} else {
							$code.="\t<option value='".$o->readAttribute($using[0])."'>".$traslate->_($o->readAttribute($using[1]))."</option>\r\n";
						}
					} else {
						$code.=call_user_func_array($callback, array($o, $value));
					}
				}
			} else {
				foreach($params[1] as $d){
					$code.="\t<option value='".$d[0]."'>".$d[1]."</option>\r\n";
				}
			}
			$code.= "</select>\r\n";
		} else {
			$code.="<select id='$params' name='$params'></select>";
		}
		return $code;
	}

	/**
	 * Crea una lista SELECT con datos de modelos y de arrays
	 *
	 * @access 	public
	 * @param 	string $name
	 * @param 	string $modelData
	 * @param 	array $arrayData
	 * @return 	string
	 * @static
	 */
	public static function selectMixed($name='', $modelData='', $arrayData=''){
		if(func_num_args()>1){
			$numberArguments = func_num_args();
			$params = Utils::getParams(func_get_args(), $numberArguments);
		}
		if(is_array($params)){
			if(!isset($params['value'])){
				$value = self::getValueFromAction($params[0]);
			} else {
				$value = $params['value'];
			}
			$callback = false;
			if(isset($params['option_callback'])){
				if(strpos($params['option_callback'], '.')){
					$callback = explode('.', $params['option_callback']);
				} else {
					$callback = $params['option_callback'];
				}
				#if[compile-time]
				if(is_callable($callback)==false){
					throw new TagException("El option_callback no es valido");
				}
				#endif
				unset($params['option_callback']);
			}
			$code ="<select id='".$params[0]."' name='".$params[0]."' ";
			if(is_array($params)){
				foreach($params as $_attribute => $_value){
					if(!is_integer($_attribute)){
						if(!is_array($_value)&&!in_array($_attribute, array('using', 'use_dummy'))){
							$code.="$_attribute='$_value' ";
						}
					}
				}
			}
			$code.=">\r\n";
			if(isset($params['use_dummy'])&&$params['use_dummy']==true){
				$code.="\t<option value='@'>Seleccione...</option>\r\n";
			}
			if(is_array($arrayData)){
				foreach($arrayData  as $k => $d){
					if($k==$value){
						$code.="\t<option value='$k' selected='selected'>$d</option>\r\n";
					} else {
						$code.="\t<option value='$k'>$d</option>\r\n";
					}
				}
			}
			if(is_object($params[1])){
				#if[compile-time]
				if(!isset($params['using'])){
					throw new TagException("Debe indicar el parámetro 'using' para el helper Tag::select()");
				}
				#endif
				$using = explode(',', $params['using']);
				foreach($params[1] as $o){
					if($callback==false){
						if($value==$o->readAttribute($using[0])){
							$code.="\t<option selected='selected' value='".$o->readAttribute($using[0])."'>".$o->readAttribute($using[1])."</option>\r\n";
						} else {
							$code.="\t<option value='".$o->readAttribute($using[0])."'>".$o->readAttribute($using[1])."</option>\r\n";
						}
					} else {
						$code.=call_user_func_array($callback, array($o, $value));
					}
				}
			} else {
				foreach($params[1] as $d){
					$code.="\t<option value='".$d[0]."'>".$d[1]."</option>\r\n";
				}
			}
			$code.= "</select>\r\n";
		} else {
			$code.="<select id='$params' name='$params'></select>";
		}
		return $code;
	}

	/**
	 * Carga el framework javascript y funciones auxiliares
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function javascriptBase($validations=true){
		$path = Core::getInstancePath();
		$code = '<script type="text/javascript" src="'.$path.'javascript/core/base.js"></script>'."\r\n";
		$code.= Tag::javascriptLocation();
		return $code;
	}

	/**
	 * Imprime la ubicación javascript
	 *
	 * @return string
	 */
	public static function javascriptLocation(){
		$application = Router::getActiveApplication();
		$controllerName = Router::getController();
		$actionName = Router::getAction();
		$module = Router::getModule();
		$id = Router::getId();
		$path = Core::getInstancePath();
		return '<script type="text/javascript" src="'.$path.'javascript/core/main.php?app='.$application.'&module='.$module.'&path='.urlencode($path).'&controller='.$controllerName.'&action='.$actionName.'&id='.$id.'"></script>'."\r\n";
	}

	/**
	 * Devuelve la ubicación javascript
	 *
	 * @return string
	 */
	public static function getJavascriptLocation(){
		$application = Router::getActiveApplication();
		$controllerName = Router::getController();
		$actionName = Router::getAction();
		$module = Router::getModule();
		$id = Router::getId();
		$path = Core::getInstancePath();
		return "<script type=\"text/javascript\">\$Kumbia={app:\"$application\",path:\"$path\",controller:\"$controllerName\",action:\"$actionName\",id:\"$id\"}</script>\n";
	}

	/**
	 * Genera una etiqueta script que apunta a un archivo JavaScript
	 * respetando las rutas y convenciones del framework
 	 *
	 * @param	string $src
	 * @param	string $cache
	 * @return	string
	 */
	public static function javascriptInclude($src='', $noCache=true, $parameters=""){
		if($src==""){
			$src = Router::getController();
		}
		$src.='.js';
		if(!$noCache){
			$cache = mt_rand(0, 999999);
			$src.="?nocache=".$cache;
			if($parameters){
				$src.="&".$parameters;
			}
		} else {
			if($parameters){
				$src.="?".$parameters;
			}
		}
		$instancePath = Core::getInstancePath();
		return '<script type="text/javascript" src="'.$instancePath.'javascript/'.$src.'"></script>'."\r\n";
	}

	/**
	 * Incluye una etiqueta SCRIPT con un recurso javascript minizado
	 *
	 * @param string $src
	 */
	public static function javascriptMinifiedInclude($src){
		$jsSource = 'public/javascript/'.$src.'.js';
		$jsMinSource = 'public/javascript/'.$src.'.min.js';
		if(file_exists($jsMinSource)==false){
			if(class_exists('Jsmin')==false){
				require KEF_ABS_PATH.'Library/Kumbia/Tag/Jsmin/Jsmin.php';
			}
			$minified = Jsmin::minify(file_get_contents($jsSource));
			file_put_contents($jsMinSource, $minified);
		} else {
			if(filemtime($jsSource)>filemtime($jsMinSource)){
				if(class_exists('Jsmin', false)==false){
					require KEF_ABS_PATH.'Library/Kumbia/Tag/Jsmin/Jsmin.php';
				}
				$minified = Jsmin::minify(file_get_contents($jsSource));
				file_put_contents($jsMinSource, $minified);
			}
		}
		return self::javascriptInclude($src.'.min');
	}

	/**
 	 * Crea un boton de submit tipo imagen para el formulario actual
	 *
	 * @access 	public
	 * @param 	string $caption
	 * @param 	string $src
	 * @return 	string
	 * @static
	 */
	public static function submitImage($src){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params['src'])){
			$params['src'] = Core::getInstancePath().'img/'.$params[0];
		}
		$code = "<input type='image' src='".$params['src']."' ";
		foreach($params as $key => $value){
			if(!is_integer($key)){
				$code.="$key='$value' ";
			}
		}
		$code.=" />\r\n";
		return $code;
	}

	/**
	 * Crea un boton HTML
	 *
	 * @return string
	 * @static
	 */
	public static function button(){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params['value'])){
			$params['value'] = $params[0];
		}
		if(isset($params['id'])&&$params['id']&&!isset($params['name'])) {
			$params['name'] = $params['id'];
		}
		if(!isset($params['id'])) {
			$params['id'] = isset($params['name']) ? $params['name'] : "";
		}
		$code = "<input type='button' ";
		foreach($params as $key => $value){
			if(!is_integer($key)&&$key!=$params){
				$code.="$key=\"$value\" ";
			}
		}
		return $code." />\r\n";
	}

	/**
	 * Agrega una etiqueta script que apunta a un archivo en public/javascript/core
	 *
	 * @param string $src
	 * @return string
	 */
	public static function javascriptLibrary($src){
		$instancePath = Core::getInstancePath();
		return "<script type='text/javascript' src='".$instancePath."javascript/core/$src.js'></script>\r\n";
	}

	/**
	 * Permite incluir una imágen dentro de una vista respetando
	 * las convenciones de directorios y rutas del framework
	 *
	 * @param string $img
	 * @return string
	 * @static
	 */
	public static function image($img){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		$code = "";
		if(!isset($params['src'])){
			$instancePath = Core::getInstancePath();
			$code.='<img src="'.$instancePath.'img/'.$params[0].'" ';
		} else {
			$code.='<img src="'.$params['src'].'" ';
			unset($params['src']);
		}
		if(!isset($params['alt'])){
			$params['alt'] = "";
		}
		foreach($params as $attribute => $value){
			if(!is_integer($attribute)){
				$code.=$attribute.'="'.$value.'" ';
			}
		}
		$code.= '/>';
		return $code;
	}

	/**
	 * Permite generar un formulario remoto
	 *
	 * @param 	mixed $params
	 * @return 	string
	 */
	public static function formRemote($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params['action'])||!$params['action']) {
			$params['action'] = $params[0];
		}
		$params['callbacks'] = array();
		$id = Router::getId();
		if(isset($params['complete'])&&$params['complete']){
			$params['callbacks'][] = ' complete: function(){ '.$params['complete'].' }';
		}
		if(isset($params['before'])&&$params['before']){
			$params['callbacks'][] = ' before: function(){ '.$params['before'].' }';
		}
		if(isset($params['success'])&&$params['success']){
			$params['callbacks'][] = ' success: function(){ '.$params['success'].' }';
		}
		if(isset($params['required'])&&$params['required']){
			$requiredFields = array();
			foreach($params['required'] as $required){
				$requiredFields[] = "'".$required."'";
			}
			$requiredFields = join(',', $requiredFields);
			$code = "<form action='".Utils::getKumbiaUrl($params['action'].'/'.$id)."' method='post'
			onsubmit='if(validaForm(this,new Array(".$requiredFields."))){ return ajaxRemoteForm(this,\"".$params['update']."\",{".join(",",$params['callbacks'])."}); } else{ return false; }'";
			unset($params['required']);
		} else{
			#if[compile-time]
			if(!isset($params['update'])){
				throw new TagException('Debe indicar el contenedor a actualizar con el parámetro "update"');
			}
			#endif
			$code = "<form action='".Utils::getKumbiaUrl($params['action'].'/'.$id)."' method='post'
			onsubmit='return ajaxRemoteForm(this, \"".$params['update']."\", { ".join(",", $params['callbacks'])." });'";
		}
		foreach($params as $at => $val){
			if(!is_integer($at)&&(!in_array($at, array('action', 'complete', 'before', 'success', 'callbacks')))){
				$code.="$at=\"".$val."\" ";
			}
		}
		return $code.=">\r\n";
	}

	/**
	 * Crea un boton de submit para el formulario remoto actual
	 *
	 * @param string $caption
	 * @return string
	 */
	public static function submitRemote($caption){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!$params['caption']) {
			$params['caption'] = $params[0];
		}
		$params['callbacks']	= array();
		if($params['complete']){
			$params['callbacks'][] = " complete: function(){ ".$params['complete']." }";
		}
		if($params['before']){
			$params['callbacks'][] = " before: function(){ ".$params['before']." }";
		}
		if($params['success']){
			$params['callbacks'][] = " success: function(){ ".$params['success']." }";
		}
		$callbacks = array(
			'action' => true,
			'complete' => true,
			'before' => true,
			'success' => true,
			'callbacks' => true,
			'caption' => true,
			'update' => true
		);
		$code = '<input type="submit" value="'.$params['caption'].'" ';
		foreach($params as $attribute => $value){
			if(!is_integer($at)&&!isset($callbacks[$attribute])){
				$code.= $attribute.'="'.$value.'" ';
			}
		}
		$code.=' onclick="return ajaxRemoteForm(this.form, \''.$params['update'].'\')" />'."\r\n";
		return $code;
	}

	/**
	 * Establece una etiqueta meta
	 *
	 * @access	public
	 * @param	string $name
	 * @param	string $content
	 * @static
	 */
	public static function setMeta($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		$code = '<meta ';
		foreach($params as $attribute => $value){
			if(!is_integer($attribute)){
				$code.=$attribute.'="'.$value.'" ';
			}
		}
		$code.= '/>'."\r\n";
		MemoryRegistry::prepend('CORE_META_TAGS', $code);
	}

	/**
	 * Imprime las metas cargadas
	 *
	 * @access public
	 * @static
	 */
	public static function getMetas(){
		$metas = MemoryRegistry::get('CORE_META_TAGS');
		if(is_array($metas)){
			foreach($metas as $meta){
				echo $meta;
			}
		}
	}

	/**
	 * Establece el título del documento HTML
	 *
	 * @access	public
	 * @param	string $title
	 * @static
	 */
	public static function setDocumentTitle($title){
		self::$_documentTitle = $title;
	}

	/**
	 * Agrega al final un texto del titulo actual del documento HTML
	 *
	 * @access	public
	 * @param	string $title
	 * @static
	 */
	public static function appendDocumentTitle($title){
		self::$_documentTitle.= $title;
	}

	/**
	 * Agrega al principio un texto del titulo actual del documento HTML
	 *
	 * @access	public
	 * @param	string $title
	 * @static
	 */
	public static function prependDocumentTitle($title){
		self::$_documentTitle = $title.self::$_documentTitle;
	}

	/**
	 * Devuelve el título del documento HTML
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getDocumentTitle(){
		return '<title>'.self::$_documentTitle.'</title>'."\r\n";
	}

	/**
	 * Agrega una etiqueta link para incluir un archivo CSS respetando
	 * las rutas y convenciones de Kumbia
	 *
	 * @access	public
	 * @param	string $src
	 * @param	boolean $useVariables
	 * @static
	 */
	public static function stylesheetLink($src='', $useVariables=false, $parameters=""){
		if(!$src) {
			$src = Router::getController();
		}
		$instancePath = Core::getInstancePath();
		if($useVariables==true){
			if($instancePath){
				$kb = substr($instancePath, 0, strlen($instancePath)-1);
			} else {
				$kb = '/';
			}
			$code = '<link rel="stylesheet" type="text/css" href="'.$instancePath.'css.php?c='.$src.'&p='.$kb.'&'.$parameters.'" />'."\r\n";
		} else {
			if($parameters!=""){
				$parameters = "?".$parameters;
			}
			$code = '<link rel="stylesheet" type="text/css" href="'.$instancePath.'css/'.$src.'.css'.$parameters.'" />'."\r\n";
		}
		MemoryRegistry::prepend('CORE_CSS', $code);
		return $code;
	}

	/**
	 * Devuelve los CSS cargados mediante Tag::stylesheetLink
	 *
	 * @access 	public
	 * @return 	string
	 * @static
	 */
	public static function stylesheetLinkTags(){
		$styleSheets = MemoryRegistry::get('CORE_CSS');
		$code = '';
		if(is_array($styleSheets)){
			foreach($styleSheets as $css){
				$code.= $css;
			}
		}
		return $code;
	}

	/**
	 * Resetea los CSS cargados mediante Tag::styleSheetLink
	 *
	 * @access public
	 * @static
	 */
	public static function resetStylesheetLinks(){
		MemoryRegistry::reset('CORE_CSS');
	}

	/**
	 * Elimina los tags agregados a la salida
	 *
	 * @access public
	 * @static
	 */
	public static function removeStylesheets(){
		MemoryRegistry::reset('CORE_CSS');
	}

	/**
	 * Agrega un Javascript a una cola para ser luego obtenidos en la vista principal con Tag::javascriptSources()
	 *
	 * @param	string $src
	 * @return	string
	 */
	public static function addJavascript($src, $noCache=true){
		$instancePath = Core::getInstancePath();
		if($noCache==true){
			MemoryRegistry::prepend('CORE_JS', '<script type="text/javascript" src="'.$instancePath.'javascript/'.$src.'.js"></script>'."\r\n");
		} else {
			$cacheSalt = mt_rand(0, 100000);
			MemoryRegistry::prepend('CORE_JS', '<script type="text/javascript" src="'.$instancePath.'javascript/'.$src.'.js?cache='.$cacheSalt.'"></script>'."\r\n");
		}
	}

	/**
	 * Devuelve los Javascript cargados mediante Tag::javascriptSource()
	 *
	 * @access 	public
	 * @return 	string
	 * @static
	 */
	public static function javascriptSources(){
		$javascripts = MemoryRegistry::get('CORE_JS');
		if(is_array($javascripts)){
			return join('', $javascripts);
		}
	}

	/**
	 * Resetea los CSS cargados mediante Tag::javascriptSource()
	 *
	 * @access public
	 * @static
	 */
	public static function resetJavascriptSources(){
		MemoryRegistry::reset('CORE_JS');
	}

	/**
	 * Carga un framework Javascript
	 *
	 * @access public
	 * @static
	 */
	public static function addJavascriptFramework($framework){
		if(isset(self::$_javascriptFrameworks[$framework])){
			Tag::addJavascript(self::$_javascriptFrameworks[$framework]);
		} else {
			throw new TagException('No se puede encontrar el framework javascript "'.$framework.'"');
		}
	}

	/**
	 * Crea una etiqueta de formulario
	 *
	 * @access 	public
	 * @param 	string $action
	 * @return 	string
	 * @static
	 */
	public static function form($action=''){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		$parameters = join('/', Router::getParameters());
		if(!isset($params[0])){
			if(isset($params['action'])){
				$action = $params['action'];
			} else {
				$action = Router::getController().'/'.Router::getAction();
			}
		} else {
			$action = $params[0];
		}
		if(!isset($params['method'])||!$params['method']) {
			$params['method'] = 'post';
		}
		if(isset($params['confirm'])&&$params['confirm']){
			$params['onsubmit'].= $params['onsubmit'].";if(!confirm(\"".$params['confirm']."\")) { return false; }";
			unset($params['confirm']);
		}
		if($parameters===''){
			$action = Utils::getKumbiaUrl($action);
		} else {
			$action = Utils::getKumbiaUrl($action.'/'.$parameters);
		}
		if(isset($params['parameters'])){
			$action.= '?'.$params['parameters'];
		}
		$str = "<form action='".$action."' ";
		foreach($params as $key => $value){
			if(!is_integer($key)){
				$str.= "$key='$value' ";
			}
		}
		return $str.">\r\n";
	}

	/**
	 * Etiqueta para cerrar un formulario
	 *
	 * @access	public
	 * @return	string
	 * @static
	 */
	public static function endForm(){
		return "</form>\r\n";
	}

	/**
 	 * Crea una caja de Texto
 	 *
 	 * @access 	public
 	 * @param 	mixed $params
 	 * @return 	string
 	 * @static
 	 */
	static public function textField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params[0])) {
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])||!$params['name']){
			$params['name'] = $params[0];
		}
		if(isset($params['value'])){
			$value = $params['value'];
			unset($params['value']);
		} else {
			$value = self::getValueFromAction($params[0]);
		}
		$code = "<input type='text' id='".$params[0]."' value='$value' ";
		foreach($params as $_key => $_value){
			if(!is_integer($_key)){
				$code.="$_key='$_value' ";
			}
		}
		$code.=" />\r\n";
		return $code;
	}

	/**
	 * Crea un componente para capturar Passwords
	 *
	 * @param 	mixed $params
	 * @return 	string
	 */
	static public function passwordField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!is_array($params)){
			return '<input type="password" id="'.$params.'" name="'.$params."/>\r\n";
		} else {
			if(!isset($params[0])) {
				$params[0] = $params['id'];
			}
			if(!isset($params['name'])||!$params['name']) {
				$params['name'] = $params[0];
			}
			if(!isset($params['value'])){
				$params['value'] = self::getValueFromAction($params[0]);
			}
			$code = "<input type='password' id='".$params[0]."' ";
			foreach($params as $key => $value){
				if(!is_integer($key)){
					$code.="$key='$value' ";
				}
			}
			$code.=" />\r\n";
			return $code;
		}
	}

	/**
	 * Crea un botón de submit para el formulario actual
	 *
	 * @access	public
	 * @param	string $caption
	 * @return	string
	 * @static
	 */
	public static function submitButton($caption){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params['caption'])){
			$params['caption'] = $params[0];
		} else {
			if(!$params['caption']){
				$params['caption'] = $params[0];
			}
		}
		$code = "<input type='submit' value='".$params['caption']."' ";
		foreach($params as $key => $value){
			if(!is_integer($key)){
				$code.=$key."='$value' ";
			}
		}
		$code.=" />\r\n";
		return $code;
	}

	/**
	 * Crea un CheckBox
	 *
	 * @access 	public
	 * @param 	mixed $params
	 * @return 	string
	 * @static
	 */
	public static function checkboxField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		$value = self::getValueFromAction($params[0]);
		if(!isset($params[0])||!$params[0]) {
			$params[0] = isset($params['id']) ? $params['id'] : "";
		}
		if(!isset($params['name'])||!$params['name']){
			$params['name'] = $params[0];
		}
		if($value!==""&&$value!==null){
			$params['checked'] = "checked";
		}
		$code = "<input type='checkbox' id='".$params[0]."' ";
		foreach($params as $key => $value){
			if(!is_integer($key)){
				$code.="$key='$value' ";
			}
		}
		$code.=" />\r\n";
		return $code;
	}

	/**
	 * Crea una caja de texto que acepta solo texto en mayúscula
	 *
	 * @access 	public
	 * @param 	mixed $params
	 * @return 	string
	 * @static
	 */
	public static function textUpperField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params[0])){
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])||$params['name']==""){
			$params['name'] = $params[0];
		}
		if(isset($params['value'])){
			$value = $params['value'];
			unset($params['value']);
		} else {
			$value = self::getValueFromAction($params[0]);
		}
		if(!isset($params['onblur'])){
			$params['onblur'] = "this.value=this.value.toUpperCase()";
		} else {
			$params['onblur'].=";this.value=this.value.toUpperCase()";
		}
		$code = "<input type='text' id='".$params[0]."' value='$value' ";
		foreach($params as $_key => $_value){
			if(!is_integer($_key)){
				$code.="$_key='$_value' ";
			}
		}
		$code.=" />\r\n";
		return $code;
	}

	/**
	 * Crea una caja de texto que acepta solo texto en minúscula
	 *
	 * @access 	public
	 * @param 	mixed $params
	 * @return 	string
	 * @static
	 */
	public static function textLowerField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params[0])){
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])||$params['name']==""){
			$params['name'] = $params[0];
		}
		if(isset($params['value'])){
			$value = $params['value'];
			unset($params['value']);
		} else {
			$value = self::getValueFromAction($params[0]);
		}
		if(!isset($params['onblur'])){
			$params['onblur'] = "this.value=this.value.toLowerCase()";
		} else {
			$params['onblur'].=";this.value=this.value.toLowerCase()";
		}
		$code = "<input type='text' id='".$params[0]."' value='$value' ";
		foreach($params as $_key => $_value){
			if(!is_integer($_key)){
				$code.="$_key='$_value' ";
			}
		}
		$code.=" />\r\n";
		return $code;
	}

	/**
	 * Crea un Input tipo Text
	 *
	 * @access 	public
	 * @param 	string $name
	 * @return 	string
	 * @static
	 */
	public static function fileField($name){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		$value = self::getValueFromAction($name);
		if(!isset($params[0])) {
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])||!$params['name']){
			$params['name'] = $params[0];
		}
		$code = "<input type='file' id='".$params[0]."' ";
		foreach($params as $key => $value){
			if(!is_integer($key)){
				$code.="$key='$value' ";
			}
		}
		$code.=" />\r\n";
		return $code;
	}

	/**
	 * Crea un input tipo Radio
	 *
	 * @access 	public
	 * @param 	mixed $params
	 * @return 	string
	 * @static
	 */
	public static function radioField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params[0])){
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])){
			$params['name'] = $params[0];
		}
		if(isset($params['value'])){
			$value = $params['value'];
			unset($params['value']);
		} else {
			$value = self::getValueFromAction($params[0]);
		}
		if(isset($params[1])&&is_array($params[1])){
			$code = "<table><tr>";
			foreach($params[1] as $key => $text){
				if($value==$key){
					$code.= "<td><input type='radio' name='".$params[0]."' id='".$params[0]."' value='$key' checked='checked' /></td><td>$text</td>\r\n";
				} else {
					$code.= "<td><input type='radio' name='".$params[0]."' id='".$params[0]."' value='$key' /></td><td>$text</td>\r\n";
				}
			}
			$code.= "</tr></table>";
		} else {
			$code = "<input type='radio' name='".$params[0]."' value='$value' ";
			foreach($params as $key => $value){
				if(!is_integer($key)){
					$code.="$key='$value' ";
				}
			}
			$code.="/>";
		}
		return $code;
	}

	/**
	 * Crea un Componente Oculto
	 *
	 * @access 	public
	 * @param 	mixed $params
	 * @return 	string
	 * @static
	 */
	public static function hiddenField($params){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params[0])){
			$params[0] = $params['id'];
		}
		if(!isset($params['name'])){
			$params['name'] = $params[0];
		}
		if(!isset($params['value'])){
			$params['value'] = self::getValueFromAction($params[0]);
		}
		$code="<input type='hidden' id='".$params[0]."'";
		foreach($params as $key => $value){
			if(!is_integer($key)){
				$code.="$key='$value' ";
			}
		}
		$code.=" />\r\n";
		return $code;
	}

	/**
	 * Crea una opcion de un SELECT
	 *
	 * @access 	public
	 * @param	string $value
	 * @param 	string $text
	 * @static
	 */
	public static function option($value, $text){
		if(func_num_args()>1){
			$numberArguments = func_num_args();
			$params = Utils::getParams(func_get_args(), $numberArguments);
			$value = $params[0];
			$text = $params[1];
		} else {
			$value = '';
		}
		$code = "<option value='$value' ";
		if(is_array($params)){
			foreach($params as $at => $val){
				if(!is_integer($at)){
					$code.="$at='".$val."' ";
				}
			}
		}
		$code.= ">$text</option>\r\n";
		return $code;
	}

	/**
	 * Crea un componente para subir Imágenes
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function uploadImage(){
		$code = '';
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params['name'])){
			$params['name'] = $params[0];
		}
		$code.="<span id='".$params['name']."_span_pre'>
		<select name='".$params[0]."' id='".$params[0]."' onchange='show_upload_image(this)'>";
		$code.="<option value='@'>Seleccione...\n";
		foreach(scandir("public/img/upload") as $file){
			if($file!='index.html'&&$file!='.'&&$file!='..'&&$file!='Thumbs.db'&&$file!='desktop.ini'){
				$nfile = str_replace('.gif', '', $file);
				$nfile = str_replace('.jpg', '', $nfile);
				$nfile = str_replace('.png', '', $nfile);
				$nfile = str_replace('.bmp', '', $nfile);
				$nfile = str_replace('_', ' ', $nfile);
				$nfile = ucfirst($nfile);
				if(urlencode("upload/$file")==$params['value']){
					$code.="<option selected='selected' value='upload/$file' style='background: #EAEAEA'>$nfile</option>\n";
				} else {
					$code.="<option value='upload/$file'>$nfile</option>\n";
				}
			}
		}
		$code.="</select> <a href='#".$params['name']."_up' name='".$params['name']."_up' id='".$params['name']."_up' onclick='enable_upload_file(\"".$params['name']."\")'>Subir Imagen</a></span>
		<span style='display:none' id='".$params['name']."_span'>
		<input type='file' id='".$params['name']."_file' onchange='upload_file(\"".$params['name']."\")' />
		<a href='#".$params['name']."_can' name='".$params['name']."_can' id='".$params['name']."_can' style='color:red' onclick='cancel_upload_file(\"".$params['name']."\")'>Cancelar</a></span>
		";
		if(!isset($params['width'])){
			$params['width'] = 128;
		}
		if(!isset($params['value'])){
			$params['value'] = '';
		}
		if($params['value']){
			$params['style']="border: 1px solid black;margin: 5px;".$params['value'];
		} else {
			$params['style']="border: 1px solid black;display:none;margin: 5px;".$params['value'];
		}

		$code.="<div>".Tag::image(urldecode($params['value']), 'width: '.$params['width'], 'style: '.$params['style'], 'id: '.$params['name']."_im")."</div>";
		return $code;
	}

	/**
	 * Hace que un elemento reciba items con drag-n-drop
	 *
	 * @access 	public
	 * @param 	string $obj
	 * @param 	string $action
	 * @return 	string
	 * @static
	 */
	public static function setDroppable($obj, $action=''){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!$params['name']){
			$params['name'] = $params[0];
		}
		return "<script type=\"text/javascript\">Droppables.add('".$params['name']."', {hoverclass: '".$params['hover_class']."',onDrop:".$params['action']."})</script>";
	}

	/**
	 * Hace que un elemento reciba items con drag-n-drop
	 *
	 * @access 	public
	 * @param 	string $action
	 * @param 	double $seconds
	 * @return 	string
	 * @static
	 */
	public static function redirectTo($action, $seconds = 0.01){
		$seconds*=1000;
		return "<script type=\"text/javascript\">setTimeout('window.location=\"?/$action\"', $seconds)</script>";
	}

	/**
	 * Imprime una etiqueta TR cada $n llamados a este helper
	 *
	 * @access public
	 * @param int $n
	 * @static
	 */
	public static function trBreak($n=''){
		static $l;
		if($n=='') {
			$l = 0;
			return;
		}
		if(!$l) {
			$l = 1;
		} else {
			++$l;
		}
		if(($l%$n)==0) {
			echo "</tr><tr>";
		}
	}

	/**
	 * Imprime una etiqueta BR cada $n llamados a este helper
	 *
	 * @access public
	 * @param int $n
	 * @static
	 */
	public static function brBreak($n=''){
		static $l;
		if($n=='') {
			$l = 0;
			return;
		}
		if(!$l) {
			$l = 1;
		} else {
			++$l;
		}
		if(($l%$n)==0) {
			echo "<br/>\n";
		}
	}

	/**
	 * Intercala entre llamados una lista de colores para etiquetas TR
	 *
	 * @access 	public
	 * @param 	array $colors
	 * @static
	 */
	public static function trColor($colors){
		static $i;
		$numberArgs = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArgs);
		$colors = $params[0];
		if(!$i) {
			$i = 1;
		}
		echo "<tr bgcolor=\"".$colors[$i-1]."\"";
		if(count($colors)==$i) {
			$i = 1;
		} else {
			++$i;
		}
		if(isset($params)){
			if(is_array($params)){
				foreach($params as $key => $value){
					if(!is_integer($key)){
						echo " $key = '$value'";
					}
				}
			}
		}
		echo ">";
	}

	/**
	 * Intercala entre llamados una lista de clases CSS para etiquetas TR
	 *
	 * @access 	public
	 * @param 	array $classes
	 * @static
	 */
	public static function trClassName($classes){
		static $i;
		if(func_num_args()>1){
			$params = Utils::getParams(func_get_args());
		}
		if(!$i) {
			$i = 1;
		}
		$code = "<tr class=\"".$classes[$i-1]."\"";
		if(count($classes)==$i) {
			$i = 1;
		} else {
			++$i;
		}
		if(isset($params)){
			if(is_array($params)){
				foreach($params as $key => $value){
					if(!is_integer($key)){
						$code.= " $key = '$value'";
					}
				}
			}
		}
		$code.=">";
		return $code;
	}

	/**
	 * Crea un botón que al hacer click carga un controlador y una acción determinada
	 *
	 * @access 	public
	 * @param 	string $caption
	 * @param 	string $action
	 * @param 	string $classCSS
	 * @return 	string
	 * @static
	 */
	static public function buttonToAction($caption, $action, $classCSS=''){
		if($classCSS!=''){
			$classCSS = "class='$classCSS'";
		}
		return "<button $classCSS onclick='window.location=\"".Utils::getKumbiaUrl($action)."\"'>$caption</button>";
	}

	/**
	 * Crea un input botón que al hacer click carga un controlador y una acción determinada
	 *
	 * @access 	public
	 * @param 	string $caption
	 * @param 	string $action
	 * @param 	string $classCSS
	 * @return 	string
	 * @static
	 */
	static public function inputButtonToAction($caption, $action, $classCSS=''){
		if($classCSS!=''){
			$classCSS = "class='$classCSS'";
		}
		return "<input type='button' $classCSS onclick='window.location=\"".Utils::getKumbiaUrl($action)."\"' value='$caption'/>";
	}

	/**
	 * Crea un Button que al hacer click carga con AJAX un controlador y una accion determinada
	 *
	 * @param 	string $caption
	 * @param 	string $action
	 * @param 	string $classCSS
	 * @return 	string
	 */
	static public function buttonToRemoteAction($caption, $action, $classCSS=''){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(func_num_args()==2){
			$params['action'] = $params[1];
			$params['caption'] = $params[0];
		} else {
			if(!isset($params['action'])||!$params['action']) {
				$params['action'] = $params[1];
			}
			if(!isset($params['caption'])||!$params['caption']) {
				$params['caption'] = $params[0];
			}
		}
		if(!isset($params['update'])){
			$params['update'] = "";
		}
		$code = "<button onclick='AJAX.execute({action:\"".$params['action']."\", container:\"".$params['update']."\", callbacks: { success: function(){".$params['success']."}, before: function(){".$params['before']."} } })'";
		unset($params['action']);
		unset($params['success']);
		unset($params['before']);
		unset($params['complete']);
		foreach($params as $k => $v){
			if(!is_integer($k)&&$k!='caption'){
				$code.=" $k='$v' ";
			}
		}
		$code.=">".$params['caption']."</button>";
		return $code;
	}

	/**
	 * Crea un select multiple que actualiza un container
	 * usando una accion ajax que cambia dependiendo del id
	 * selecionado en el select
	 *
	 * @access 	public
	 * @param 	string $id
	 * @return 	string
	 * @static
	 */
	public static function updaterSelect($id){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(func_num_args()==1){
			$params['id'] = $id;
		}
		if(!$params['id']){
			$params['id'] = $params[0];
		}
		if(!$params['container']){
			$params['container'] = $params['update'];
		}
		$code = "
		<select multiple onchange='AJAX.viewRequest({
			action: \"".$params['action']."/\"+selectedItem($(\"".$params['id']."\")).value,
			container: \"".$params['container']."\"
		})' ";
		unset($params['container']);
		unset($params['update']);
		unset($params['action']);
		foreach($params as $k => $v){
			if(!is_integer($k)){
				$code.=" $k='$v' ";
			}
		}
		$code.=">\n";
		return $code;
	}

	/**
	 * Helper de Paginacion
	 *
	 * @param array $items
	 * @param integer $pageNumber
	 * @param integer $show
	 * @return object
	 */
	public static function paginate($items, $pageNumber=null, $show=10){
		$n = count($items);
		$page = new stdClass();
		$start = $show*($pageNumber-1);
		$totalPages = round((count($items)-1)/$show);
		if(is_array($items)){
			if($pageNumber===null){
				$pageNumber = 1;
			}
			$page->items = array_slice($items, $start, $show);
		} else {
			if($pageNumber===null){
				$pageNumber = 0;
			}
			if(is_object($items)){
				if($items instanceof ActiveRecordResultset){
					#if[compile-time]
					if($start<0){
						throw new TagException("El número de la página es negativo ó cero ($start)");
					}
					#endif
					$page->items = array();
					$total = count($items);
					if($total>0){
						if($start<=$total){
							$items->seek($start);
						} else {
							$items->seek(1);
							$pageNumber = 1;
						}
						$i = 1;
						while($items->valid()==true){
							$page->items[] = $items->current();
							if($i>=$show){
								break;
							}
							++$i;
						}
					}
				}
			}
		}
		$page->first = 1;
		$page->next = ($start + $show)<$n ? ($pageNumber+1) : (($start + $show)==$n ? $n : ((int)($n/$show) + 1));
		//Fix next
		if($page->next > $totalPages){
			$page->next = $totalPages;
		}
		$page->before = ($pageNumber>1) ? ($pageNumber-1) : 1;
		$page->current = $pageNumber;
		$page->total_pages = ($n % $show) ? ((int)($n/$show) + 1) : ($n/$show);
		$page->last = $page->total_pages;
		return $page;
	}

	/**
	 * Crea pestañas de diferentes colores
	 *
	 * @access public
	 * @param array $tabs
	 * @param string $color
	 * @param int $width
	 * @static
	 */
	static public function tab($tabs, $width=800){
		$code = "<table cellspacing='0' cellpadding='0' width='$width'><tr>";
		$p = 1;
		$w = $width;
		foreach($tabs as $tab){
			if($p==1){
				$className = 'tab_active';
			} else {
				$className = 'tab_inactive';
			}
			$ww = (int) ($width * 0.22);
			$www = (int) ($width * 0.21);
			$code.="<td align='center' width='$ww' class='tab_td'><div style='width:".$www."px;' id='tabdiv_$p' onclick='showTab($p, this)' class='tab_div $className'>".$tab['caption']."</div></td>";
			++$p;
			$w-=$ww;
		}
		$code.= "
			<script type='text/javascript'>
				function showTab(p, obj){
					for(var i=1;i<$p;i++){
					    $('tab_'+i).hide();
						$('tabdiv_'+i).removeClassName('tab_active');
						$('tabdiv_'+i).addClassName('tab_inactive');
					};
					$('tab_'+p).show();
					$('tabdiv_'+p).removeClassName('tab_inactive');
					$('tabdiv_'+p).addClassName('tab_active');
				}
			</script>
			";
		++$p;
		//$w = $width/2;
		$code.="<td width='$w'></td><tr>";
		$code.="<td colspan='$p' class='tab_con'>";
		$p = 1;
		foreach($tabs as $tab){
			if($p!=1){
				$code.="<div id='tab_$p' style='display:none'>";
			} else {
				$code.="<div id='tab_$p'>";
			}
			ob_start();
			View::renderPartial($tab['partial']);
			$code.=ob_get_contents();
			ob_end_clean();
			$code.="</div>";
			++$p;
		}
		$code.="<br></td><td width='30'></td></table>";
		return $code;
	}

	static public function updateDiv(){
		$params = Utils::getParams(func_get_args());
		$name = $params[0];
		if(isset($params['value'])){
			$value = $params['value'];
		} else {
			$value = "";
		}
		$html = "<div><div id='".$name."1' ondblclick=\"$('$name').show();$('$name').activate();this.hide()\" onmouseover='this.style.background=\"#ffffcc\"' onmouseout='this.style.background=\"transparent\"'>$value</div>";
		$html.= "<input id='$name' type='text' value='$value' style='display:none' onblur='$(\"".$name."1\").show();this.hide();$(\"{$name}1\").innerHTML=this.value'";
		unset($params['value']);
		foreach($params as $key => $value){
			if(!is_integer($key)){
				$html.= "$key = '$value'";
			}
		}
		$html.= "/></div>";
		return $html;
	}

}
