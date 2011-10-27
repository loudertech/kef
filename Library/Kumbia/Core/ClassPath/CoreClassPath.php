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
 * @package		Core
 * @subpackage	CoreClassPath
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: CoreClassPath.php 120 2010-02-06 22:32:50Z gutierrezandresfelipe $
 */

/**
 * CoreClassPath
 *
 * Mantiene un directorio de rutas a las clases del framework de tal
 * forma que se pueda realizar la inyección de dependencia en la
 * aplicación cuando sean requeridos.
 *
 * @category	Kumbia
 * @package		Core
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
abstract class CoreClassPath {

	/**
	 * Directorio absoluto al Framework
	 *
	 * @var string
	 */
	static private $_dirName = '';

	/**
	 * Indica si se ha inicializado el directorio absoluto
	 *
	 * @var boolean
	 */
	static private $_iniDirName = false;

	/**
	 * Directorio de Recursos
	 *
	 * @var array
	 */
	static private $_classPath = array(
		'Acl' => 'Acl/Acl',
		'AclException' => 'Acl/AclException',
		'AclRoleException' => 'Acl/Role/AclRoleException',
		'AclResourceException' => 'Acl/Role/AclResourceException',
		'ActiveRecordBase' => 'ActiveRecord/Base/ActiveRecordBase',
		'ActiveRecordCriteria' => 'ActiveRecord/Criteria/ActiveRecordCriteria',
		'ActiveRecordJoin' => 'ActiveRecord/Join/ActiveRecordJoin',
		'ActiveRecordGenerator' => 'ActiveRecord/Generator/ActiveRecordGenerator',
		'ActiveRecordException' => 'ActiveRecord/ActiveRecordException',
		'ActiveRecordMessage' => 'ActiveRecord/Message/ActiveRecordMessage',
		'ActiveRecordMetaData' => 'ActiveRecord/MetaData/ActiveRecordMetaData',
		'ActiveRecordMetaDataException' => 'ActiveRecord/MetaData/ActiveRecordMetadataException',
		'ActiveRecordMigration' => 'ActiveRecord/Migration/ActiveRecordMigration',
		'ActiveRecordMigrationException' => 'ActiveRecord/Migration/ActiveRecordMigrationException',
		'ActiveRecordMigrationProfiler' => 'ActiveRecord/Migration/Profiler/ActiveRecordMigrationProfiler',
		'ActiveRecordQuery' => 'ActiveRecord/Query/ActiveRecordQuery',
		'ActiveRecordResultset' => 'ActiveRecord/Resultset/ActiveRecordResultset',
		'ActiveRecordRow' => 'ActiveRecord/Row/ActiveRecordRow',
		'ActiveRecordTransaction' => 'ActiveRecord/Transaction/ActiveRecordTransaction',
		'ActiveRecordTransactionException' => 'ActiveRecord/Transaction/ActiveRecordTransactionException',
		'ActiveRecordValidatorException' => 'ActiveRecord/Validator/ActiveRecordValidatorException',
		'ActiveRecordUtils' => 'ActiveRecord/Utils/ActiveRecordUtils',
		'ApplicationPlugin' => 'Plugin/Abstract/ApplicationPlugin',
		'ApplicationMonitor' => 'ApplicationMonitor/ApplicationMonitor',
		'ApplicationController' => 'Controller/Application/ApplicationController',
		'ApplicationControllerException' => 'Controller/Application/ApplicationControllerException',
		'AuditLogger' => 'AuditLogger/AuditLogger',
		'AuditLoggerException' => 'AuditLogger/AuditLoggerException',
		'Auth' => 'Auth/Auth',
		'AuthException' => 'Auth/AuthException',
		'AssertionFailed' => 'PHPUnit/AssertionFailed',
		'Benchmark' => 'Benchmark/Benchmark',
		'Browser' => 'ActionHelpers/Browser/Browser',
		'Builder' => 'Builder/Builder',
		'BuilderException' => 'Builder/BuilderException',
		'Cache' => 'Cache/Cache',
		'CacheException' => 'Cache/CacheException',
		'Captcha' => 'Captcha/Captcha',
		'CaptchaException' => 'Captcha/CaptchaException',
		'CassieRecord' => 'Cassie/Record/CassieRecord',
		'CassieRecordResultset' => 'Cassie/Record/Resultset/CassieRecordResultset',
		'CassieRecordException' => 'Cassie/Record/CassieRecordException',
		'CommonEvent' => 'CommonEvent/Base/Event',
		'Compiler' => 'Compiler/Compiler',
		'CompilerException' => 'Compiler/CompilerException',
		'ComponentBuilder' => 'ComponentBuilder/ComponentBuilder',
		'ComponentBuilderException' => 'ComponentBuilder/ComponentBuilderException',
		'ComponentPlugin' => 'Plugin/Abstract/ComponentPlugin',
		'Config' => 'Config/Config',
		'ConfigException' => 'Config/ConfigException',
		'Controller' => 'Controller/Controller',
		'ControllerBase' => 'Controller/ControllerBase',
		'ControllerException' => 'Controller/ControllerException',
		'ControllerRequest' => 'Controller/ControllerRequest',
		'ControllerResponse' => 'Controller/ControllerResponse',
		'ControllerPlugin' => 'Plugin/Abstract/ControllerPlugin',
		'ControllerUploadFile' => 'Controller/ControllerUploadFile',
		'Core' => 'Core/Core',
		'CoreClassPath' => 'Core/ClassPath/CoreClassPath',
		'CoreConfig' => 'Core/Config/CoreConfig',
		'CoreConfigException' => 'Core/Config/CoreConfigException',
		'CoreInfo' => 'Core/Info/CoreInfo',
		'CoreLocale' => 'Core/Locale/CoreLocale',
		'CoreLocaleException' => 'Core/Locale/CoreLocaleException',
		'CoreException' => 'Core/CoreException',
		'CoreType' => 'Core/Type/CoreType',
		'Currency' => 'Currency/Currency',
		'CurrencyFormat' => 'Currency/Format/CurrencyFormat',
		'Date' => 'Date/Date',
		'DateException' => 'Date/DateException',
		'DateFormat' => 'Date/Format/DateFormat',
		'DbBase' => 'Db/DbBase',
		'DbException' => 'Db/DbException',
		'DbConstraintViolationException' => 'Db/DbConstraintViolationException',
		'DbColumn' => 'Db/Column/DbColumn',
		'DbInvalidFormatException' => 'Db/DbInvalidFormatException',
		'DbIndex' => 'Db/Index/DbIndex',
		'DbLockAdquisitionException' => 'Db/DbLockAdquisitionException',
		'DbLoader' => 'Db/Loader/DbLoader',
		'DbLoaderException' => 'Db/Loader/DbLoaderException',
		'DbPool' => 'Db/Pool/DbPool',
		'DbReference' => 'Db/Reference/DbReference',
		'DbRawValue' => 'Db/DbRawValue/DbRawValue',
		'DbProfiler' => 'Db/Profiler/DbProfiler',
		'DbSQLGrammarException' => 'Db/DbSQLGrammarException',
		'Debug' => 'Debug/Debug',
		'DebugException' => 'Debug/DebugException',
		'DebugRemote' => 'Debug/Remote/DebugRemote',
		'Decimal' => 'Decimal/Decimal',
		'DispatcherException' => 'Dispatcher/DispatcherException',
		'EntityManager' => 'EntityManager/EntityManager',
		'EntityManagerException' => 'EntityManager/EntityManagerException',
		'EventManager' => 'Event/EventManager',
		'Facility' => 'Facility/Facility',
		'Feed' => 'Feed/Feed',
		'FeedItem' => 'Feed/Item/FeedItem',
		'FileLogger' => 'Logger/Adapters/File',
		'Filter' => 'Filter/Filter',
		'FilterException' => 'Filter/FilterException',
		'Flash' => 'ActionHelpers/Flash/Flash',
		'FormCriteria' => 'ActionHelpers/FormCriteria/FormCriteria',
		'GeoIP' => 'GeoIP/GeoIP',
		'GeoIPException' => 'GeoIP/GeoIPException',
		'Generator' => 'Generator/Generator',
		'Helpers' => 'Helpers/Helpers',
		'Highlight' => 'Highlight/Highlight',
		'HighlightException' => 'Highlight/HighlightException',
		'HttpUri' => 'HttpUri/HttpUri',
		'i18n' => 'i18n/i18n',
		'JsonController' => 'Controller/Json/JsonController',
		'GarbageCollector' => 'GarbageCollector/GarbageCollector',
		'GeneratorReport' => 'Generator/GeneratorReport/GeneratorReport',
		'Linguistics' => 'Linguistics/Linguistics',
		'LinguisticsException' => 'Linguistics/LinguisticsException',
		'Locale' => 'Locale/Locale',
		'LocaleData' => 'Locale/Data/LocaleData',
		'LocaleException' => 'Locale/LocaleException',
		'LocaleMath' => 'Locale/Math/LocaleMath',
		'LocaleMathException' => 'Locale/LocaleMath/LocaleMathException',
		'Logger' => 'Logger/Logger',
		'LoggerException' => 'Logger/LoggerException',
		'Migrate' => 'Migrate/Migrate',
		'MutableController' => 'Controller/Mutable/MutableController',
		'MultiThreadController' => 'Controller/Application/MultiThread/MultiThreadController',
		'NamespaceContainer' => 'Session/Namespace/NamespaceContainer',
		'Object' => 'Object',
		'PdfDocument' => 'PdfDocument/PdfDocument',
		'PdfDocumentException' =>  'PdfDocument/PdfDocumentException',
		'PHPUnit' => 'PHPUnit/PHPUnit',
		'PHPUnitTestCase' => 'PHPUnit/PHPUnitTestCase',
		'PHPUnitException' => 'PHPUnit/PHPUnitException',
		'PluginManager' => 'Plugin/Plugin',
		'PluginException' => 'Plugin/PluginException',
		'Scriptaculous' => 'ActionHelpers/Scriptaculous/Scriptaculous',
		'ScriptaculousException' => 'ActionHelpers/Scriptaculous/ScriptaculousException',
		'Script' => 'Script/Script',
		'ScriptColor' => 'Script/Color/ScriptColor',
		'ScriptException' => 'Script/ScriptException',
		'Session' => 'Session/Session',
		'SessionException' => 'Session/SessionException',
		'SessionNamespace' => 'Session/Namespace/Namespace',
		'SessionRecord' => 'ActiveRecord/SessionRecord/SessionRecord',
		'StandardForm' => 'Controller/StandardForm/StandardFormController',
		'StandardFormException' => 'Controller/StandardForm/StandardFormException',
		'Security' => 'Security/Security',
		'SecurityFirewall' => 'Security/Firewall/SecurityFirewall',
		'Soap' => 'Soap/Soap',
		'SoapException' => 'Soap/SoapException',
		'Registry' => 'Registry/Registry',
		'Report' => 'Report/Report',
		'ReportText' => 'Report/Components/ReportText/ReportText',
		'ReportStyle' => 'Report/Components/ReportStyle/ReportStyle',
		'ReportFormat' => 'Report/Components/ReportFormat/ReportFormat',
		'ReportAdapter' => 'Report/ReportAdapter/ReportAdapter',
		'ReportComponent' => 'Report/ReportComponent/ReportComponent',
		'ReportException' => 'Report/ReportException',
		'Resolver' => 'Resolver/Resolver',
		'Router' => 'Router/Router',
		'RouterException' => 'Router/RouterException',
		'UserComponent' => 'UserComponent/UserComponent',
		'UserComponentException' => 'UserComponent/UserComponentException',
		'Utils' => 'Utils/Utils',
		'Tag' => 'Tag/Tag',
		'TagException' => 'Tag/TagException',
		'TemporaryActiveRecord' => 'ActiveRecord/Temporary/TemporaryActiveRecord',
		'TransactionDefinition' => 'Transactions/TransactionDefinition',
		'TransactionFailed' => 'ActiveRecord/Transaction/TransactionFailed',
		'TransactionManager' => 'Transactions/TransactionManager',
		'TransactionManagerException' => 'Transactions/TransactionExceptionManager',
		'Traslate' => 'Traslate/Traslate',
		'Twitter' => 'Twitter/Twitter',
		'Validation' => 'Validation/Validation',
		'ValidationException' => 'Validation/ValidationException',
		'ValidationMessage' => 'Validation/ValidationMessage',
		'Version' => 'Version/Version',
		'View' => 'View/View',
		'ViewException' => 'View/ViewException',
		'ViewPlugin' => 'Plugin/Abstract/ViewPlugin',
		'WebServiceController' => 'Controller/WebService/WebServiceController',
		'WebServiceClient' => 'Soap/Client/WebServiceClient',
		'WebServiceException' => 'Controller/WebService/WebServiceControllerException'
	);

	/**
	 * Verifica si una clase existe en el CLASSPATH
	 *
	 * @param	string $className
	 * @return	boolean
	 */
	static public function lookupClass($className){
		return isset(self::$_classPath[$className]);
	}

	/**
	 * Establece el directorio de inicio de la aplicación
	 *
	 * @param string $path
	 */
	static public function setInitDir($path){
		self::$_dirName = $path;
		self::$_iniDirName = true;
	}

	/**
	 * Devuelve el PATH de la clase solicitada
	 *
	 * @param string $className
	 */
	static public function getClassPath($className){
		if(self::$_iniDirName==false){
			self::$_dirName = KEF_ABS_PATH;
			self::$_iniDirName = true;
		}
		return self::$_dirName.'Library/Kumbia/'.self::$_classPath[$className].'.php';
	}

	/**
	 * Reemplaza una entrada en el CLASSPATH
	 *
	 * @param string $className
	 * @param string $path
	 */
	static public function replacePath($className, $path){
		self::$_classPath[$className] = $path;
	}

	/**
	 * Agrega una entrada en el CLASSPATH
	 *
	 * @param string $className
	 * @param string $path
	 */
	static public function addToPath($className, $path){
		if(!isset(self::$_classPath[$className])){
			self::$_classPath[$className] = $path;
		}
	}

}
