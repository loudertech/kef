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
 * @package		Controller
 * @subpackage	ApplicationController
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version		$Id: ApplicationController.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * ApplicationController
 *
 * Es la clase principal para controladores del framework
 *
 * @category	Kumbia
 * @package		Controller
 * @subpackage	ApplicationController
 * @copyright 	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class ApplicationController extends Controller {

	/**
	 * Visualiza una vista en el controlador actual
	 *
	 * @access 	protected
	 * @param 	string $view
	 */
	protected function render($view){
		$viewsDir = Core::getActiveViewsDir();
		$path = $viewsDir.'/'.$view.'.phtml';
		if(Core::fileExists($path)){
			foreach(EntityManager::getEntities() as $modelName => $model){
				$$modelName = $model;
			}
			foreach($this as $_var => $_value){
				$$_var = $_value;
			}
			foreach(View::getViewParams() as $_key => $_value){
				$$_key = $_value;
			}
			include KEF_ABS_PATH.$path;
		} else {
			throw new ApplicationControllerException('No existe la vista ó no se puede leer el archivo');
		}
	}

	/**
	 * Visualiza un texto en la vista actual
	 *
	 * @access	protected
	 * @param	string $text
	 */
	protected function renderText($text){
		echo $text;
	}

	/**
	 * Visualiza una vista parcial en el controlador actual
	 *
	 * @access	protected
	 * @param	string $partial
	 * @param	string $values
	 */
	protected function renderPartial($partial, $values = ''){
		View::renderPartial($partial, $values);
	}

	/**
	 * La definición de este método indica si se debe exportar
	 * las variables públicas
	 *
	 * @access 	public
	 * @return 	true
	 */
	public function isExportable(){
		return true;
	}

}
