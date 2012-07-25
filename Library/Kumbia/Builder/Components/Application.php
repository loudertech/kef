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
 * @package		Builder
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Application.php,v 7a54c57f039b 2011/10/19 23:41:19 andres $
 */

/**
 * ApplicationBuilderComponent
 *
 * Builder para construir aplicaciones
 *
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Application.php,v 7a54c57f039b 2011/10/19 23:41:19 andres $
 */
class ApplicationBuilderComponent {

	/**
	 * Crea los archivos .INI por defecto de una aplicacion
	 *
	 * @param string $name
	 */
	private static function createINIFiles($name){
		$str = '[routes]
';
		file_put_contents("apps/".$name."/config/routes.ini", $str);
		$str = '[application]
mode = development
name = "Project Name"
dbdate = YYYY-MM-DD
debug = On
';
		file_put_contents('apps/'.$name.'/config/config.ini', $str);
$str = '[development]
database.type = mysql
database.host = localhost
database.username = root
database.password =
database.name = development_db

[production]
database.type = mysql
database.host = localhost
database.username = root
database.password =
database.name = production_db

[test]
database.type = mysql
database.host = localhost
database.username = root
database.password =
database.name = test_db
';
		file_put_contents("apps/$name/config/environment.ini", $str);
		$str = "[modules]\nextensions = \"\"";
		file_put_contents('apps/'.$name.'/config/boot.ini', $str);
	}

	/**
	 * Crea el archivo ControllerBase por defecto
	 *
	 * @param string $name
	 */
	private static function createControllerBase($name){
		$str = "<?php

/**
 * Todas las controladores heredan de esta clase en un nivel superior
 * por lo tanto los metodos aqui definidos estan disponibles para
 * cualquier controlador.
 *
 * @category Kumbia
 * @package Controller
 * @access public
 **/
class ControllerBase {

	public function init(){
		Core::info();
	}

}

";
		file_put_contents("apps/".$name."/controllers/application.php", $str);
	}

	/**
	 * Crea el archivo modelbase por defecto
	 *
	 * @param string $name
	 */
	private static function createModelBase($name){
		$str = "<?php\n\n/**\n * ActiveRecord\n *\n * Esta clase es la clase padre de todos los modelos\n * de la aplicacion\n *\n * @category Kumbia\n * @package ActiveRecord\n */\nabstract class ActiveRecord extends ActiveRecordBase {\n\n}\n\n";
		file_put_contents("apps/$name/models/base/modelBase.php", $str);
	}

	/**
	 * Crea el archivo views/index.phtml por defecto
	 *
	 * @param string $name
	 */
	private static function createIndexView($name){
		$str = '<!doctype html>
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
		<?php
		  	echo Tag::getDocumentTitle();
		  	Tag::stylesheetLink(\'style\');
			echo Tag::stylesheetLinkTags();
			Tag::addJavascriptFramework(\'scriptaculous\');
			echo Tag::getJavascriptLocation();
			echo Tag::javascriptSources();
		?>
	</head>
	<body>
		<?php View::getContent(); ?>
	</body>
</html>
';
		file_put_contents('apps/'.$name.'/views/index.phtml', $str);
	}

	public function __construct($options){
		if(!isset($options['name'])){
			throw new BuilderException("No se indicó el nombre de la aplicación");
		}
		$this->_options = $options;
	}

	public function build(){
		$name = $this->_options['name'];
		if(file_exists("apps/".$name)){
			throw new BuilderException("La aplicación '".$name."' ya existe");
		}
		@mkdir('apps/'.$name);
		@mkdir('apps/'.$name.'/controllers');
		@mkdir('apps/'.$name.'/config');
		@mkdir('apps/'.$name.'/models');
		@mkdir('apps/'.$name.'/models/base');
		@mkdir('apps/'.$name.'/views');
		@mkdir('apps/'.$name.'/logs');
		@mkdir('apps/'.$name.'/plugins');
		@mkdir('apps/'.$name.'/views/layouts');
		self::createINIFiles($name);
		self::createModelBase($name);
		self::createIndexView($name);
		self::createControllerBase($name);

	}


}