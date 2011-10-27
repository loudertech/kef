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
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: db2.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */

require 'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
require 'Library/Kumbia/Autoload.php';

class Db2Profiler extends DbProfiler {

	private $_displayProfile = false;

	public function setDisplayProfile($displayProfile){
		$this->_displayProfile = $displayProfile;
	}

	public function beforeStartProfile(DbProfilerItem $profile){
		if($this->_displayProfile==true){
			echo $profile->getInitialTime(), ': ', str_replace(array("\n", "\t"), " ", $profile->getSQLStatement());
		}
	}

	public function afterEndProfile($profile){
		if($this->_displayProfile==true){
			echo '  => ', $profile->getFinalTime(), ' (', ($profile->getTotalElapsedSeconds()), ')', PHP_EOL;
		}
	}

}

/**
 * Db2CliScript
 *
 * Emula una consola de MySQL para DB2
 *
 * @category 	Kumbia
 * @package 	Scripts
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: db2.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */
class Db2CliScript extends Script {

	/**
	 * DbDb2 connection
	 *
	 * @var DbDb2
	 */
	private $_connection;

	private $_profiler;

	private $_inputHandler;

	public function run(){

		$posibleParameters = array(
			'user=s' => "--user \t\tUsuario con el que se conecta a la bd",
			'password=s' => "--user \t\tPassword del usuario que se conecta a la bd",
			'name=s' => "--name \t\tNombre de la base de datos [opcional]",
			'port=i' => "--name \t\tPuerto donde se realizará la conexión (solo TCP/IP) [opcional]",
			'debug=s' => "--debug yes,no \t\tDebug de la utilidad [opcional]",
			'version=s' => "--version \t\tVersión de la utilidad [opcional]",
			'help' => "--help \t\t\tMuestra esta ayuda"
		);

		$posibleAlias = array(
			'u' => 'user',
			'p' => 'password'
		);

		$this->parseParameters($posibleParameters, $posibleAlias);
		if($this->isReceivedOption('help')){
			$this->showHelp($posibleParameters);
			return;
		}

		$this->checkRequired(array('user', 'password'));

		$hostname = $this->getOption('host');
		if(!$hostname){
			$hostname = 'localhost';
		}

		$username = $this->getOption('user');
		if(!$username){
			$username = 'db2inst1';
		}

		$password = $this->getOption('password');
		if(!$password){
			$password = null;
		}

		$port = $this->getOption('port');
		if(!$port){
			$port = null;
		}

		$database = $this->getOption('name');
		if(!$database){
			$database = $this->getLastUnNamedParam();
		}
		if(!$database){
			throw new ScriptException('Se require un nombre de base de datos');
		}

		echo 'Connecting...', PHP_EOL;
		$this->_connection = DbLoader::factory('db2', array(
			'host' => $hostname,
			'port' => $port,
			'username' => $username,
			'password' => $password,
			'name' => $database,
			'schema' => $schema,
		));

		$this->_profiler = new Db2Profiler();
		$this->_connection->setProfiling($this->_profiler);

		Locale::setApplication(new Locale('en_US'));

		$this->_prepareReadline();
		$serverInfo = $this->_connection->getServerInfo();
		echo "Welcome to the DB2 monitor. Commands end with ; or \g.\n";
		echo "Your DB2 connection is ".$this->_connection->getConnectionId().".\n";
		echo "Server version: ".$serverInfo->DBMS_NAME.' '.$serverInfo->DBMS_VER." ".$serverInfo->DB_NAME."\n\n";
		echo "Copyright (c) 1993, 2007, IBM Corporation and/or its affiliates. All rights reserved.\n\n";
		echo "Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.\n\n";
		$this->_inputPrompt();
		$this->_connection->setScrollableCursors(false);
		while(true){
			try {
				$command = $this->_readLine();
				if($command=='quit'||$command=='exit'){
					$this->_connection->close();
					exit;
				}
				if(preg_match('/^desc(ribe)*[ ]+([table][ ]+){0,1}([a-zA-Z0-9\.]+)/i', $command, $matches)){
					if(strpos($matches[3], '.')!==false){
						list($schemaName, $tableName) = explode('.', $matches[3]);
					} else {
						$schemaName = $defaultSchema;
						$tableName = $matches[3];
					}
					$tableExists = $this->_connection->describeTable($tableName, $schemaName);
					if($tableExists){
						$describe = $this->_connection->describeTable($tableName, $schemaName);
						$this->_arrayToTable($describe, array('Field', 'Type', 'Null', 'Key', 'Extra'));
					} else {
						throw new DbException('No existe la tabla '.$matches[3], 0);
					}
					$this->_inputPrompt();
					continue;
				}
				if(preg_match('/^use[ ]+schema[ ]+([a-zA-Z0-9]+)/i', $command, $matches)){
					$defaultSchema = $matches[1];
					$this->_connection->query('SET SCHEMA = '.$matches[1]);
					echo 'Schema changed', PHP_EOL, PHP_EOL;
					$this->_inputPrompt();
					continue;
				}
				if(preg_match('/^show[ ]+tables[ ]+on[ ]+([a-zA-Z0-9]+)/i', $command, $matches)){
					$tables = $this->_connection->listTables($matches[1]);
					if(count($tables)==0){
						echo 'Empty set (0.00 sec)', PHP_EOL, PHP_EOL;
					} else {
						$this->_arrayToTable($tables, array('Tables on '.$matches[1]));
					}
					$this->_inputPrompt();
					continue;
				}
				if(preg_match('/^show[ ]+create[ ]+table[ ]([a-zA-Z0-9]+)/i', $command, $matches)){
					$tableDefinition = $this->_connection->getTableDefinition($matches[1]);
					$this->_arrayToTable(array(array($matches[1], $tableDefinition)), array('Table', 'Create Table'));
					$this->_inputPrompt();
					continue;
				}
				if(preg_match('/^show[ ]+tables/i', $command, $matches)){
					$tables = $this->_connection->listTables($defaultSchema);
					if(count($tables)==0){
						echo 'Empty set (0.00 sec)', PHP_EOL, PHP_EOL;
					} else {
						if($defaultSchema==''){
							$this->_arrayToTable($tables, array('Tables on all schemas'));
						} else {
							$this->_arrayToTable($tables, array('Tables on '.$defaultSchema));
						}
					}
					$this->_inputPrompt();
					continue;
				}
				$success = $this->_connection->query($command);
				if($success==true){
					$affectedRows = $this->_connection->affectedRows();
					if($affectedRows<0){
						$this->_renderResult($success, $affectedRows);
					} else {
						echo 'Query OK, '.$affectedRows.' rows affected (', LocaleMath::round($this->_profiler->getLastProfile()->getTotalElapsedSeconds(), 2), ' sec)', PHP_EOL, PHP_EOL;
					}
				}
			}
			catch(CoreException $e){
				echo get_class($e), ' : ', $e->getConsoleMessage(), PHP_EOL, PHP_EOL;
			}
			$this->_inputPrompt();
		}
	}

	private function _arrayToTable($rows, $headers){
		$table = array(
			'weight' => array(),
			'headers' => array(),
			'data' => array()
		);
		foreach($headers as $key => $value){
			$table['weight'][$key] = strlen($value);
			$table['headers'][$key] = $value;
		}
		foreach($rows as $row){
			if(is_array($row)){
				$n = 0;
				$data = array();
				foreach($row as $key => $value){
					if(strlen($value)>$table['weight'][$n]){
						$table['weight'][$n] = strlen($value);
					}
					$data[$n] = $value;
					$n++;
				}
				$table['data'][] = $data;
			} else {
				if(strlen($row)>$table['weight'][0]){
					$table['weight'][0] = strlen($row);
				}
				$table['data'][] = array($row);
			}
		}
		if(count($rows)>0){
			$this->_renderTable($table, count($headers));
			echo count($rows).' rows in set (0.00)';
		}
		echo PHP_EOL, PHP_EOL;
	}

	private function _renderResult($result, $affectedRows){
		$number = 0;
		if($affectedRows<0){
			$rows = array(
				'weight' => array(),
				'headers' => array(),
				'data' => array()
			);
			$firstRow = true;
			$numberColumns = 0;
			$this->_connection->setFetchMode(DbBase::DB_NUM);
			while($row = @$this->_connection->fetchArray($result)){
				if($firstRow==true){
					foreach($row as $key => $value){
						$fieldName = $this->_connection->fieldName($key, $result);
						$rows['weight'][$key] = strlen($fieldName);
						$rows['headers'][$key] = $fieldName;
						$numberColumns++;
					}
					$firstRow = false;
				}
				foreach($row as $key => $value){
					if(strlen($value)>$rows['weight'][$key]){
						$rows['weight'][$key] = strlen($value);
					}
					$rows['data'][$number][$key] = $value;
				}
				$number++;
			}
		}
		if($number>0){
			$this->_renderTable($rows, $numberColumns);
			echo $number.' rows in set ';
		} else {
			echo 'Empty set ';
		}
		$profiler = $this->_profiler->getLastProfile();
		if($profiler){
			echo '(', Currency::number($profiler->getTotalElapsedSeconds(), 2), ' sec)';
		} else {
			echo '(0.00 sec)';
		}
		echo PHP_EOL, PHP_EOL;
	}

	private function _renderTable($table, $numberColumns){
		echo '+';
		$totalLength = 0;
		for($i=0;$i<$numberColumns;$i++){
			echo str_repeat('-', $table['weight'][$i]+2);
			if($numberColumns!=($i+1)){
				echo '-';
			}
			$totalLength+=$table['weight'][$i]+2;
		}
		echo '+', PHP_EOL;
		echo '|';
		for($i=0;$i<$numberColumns;$i++){
			$padLength = intval(($table['weight'][$i]-strlen($table['headers'][$i]))/2);
			$padDiff = ($table['weight'][$i]-strlen($table['headers'][$i]))-$padLength*2;
			echo ' ', str_repeat(' ', $padLength), $table['headers'][$i], str_repeat(' ', $padLength+$padDiff), ' ';
			if($numberColumns!=($i+1)){
				echo '|';
			}
		}
		echo '|', PHP_EOL;
		echo '+';
		for($i=0;$i<$numberColumns;$i++){
			echo str_repeat('-', $table['weight'][$i]+2);
			if($numberColumns!=($i+1)){
				echo '-';
			}
		}
		echo '+', PHP_EOL;
		foreach($table['data'] as $row){
			echo '|';
			foreach($row as $key => $value){
				$padLength = intval($table['weight'][$key]-strlen($value));
				if(is_numeric($value)){
					echo ' ', str_repeat(' ', $padLength), $value, ' ';
				} else {
					echo ' ', $value, str_repeat(' ', $padLength), ' ';
				}
				if($numberColumns!=($key+1)){
					echo '|';
				}
			}
			echo '|', PHP_EOL;
		}
		echo '+';
		for($i=0;$i<$numberColumns;$i++){
			echo str_repeat('-', $table['weight'][$i]+2);
			if($numberColumns!=($i+1)){
				echo '-';
			}
		}
		echo '+', PHP_EOL;
	}

	private function _showEmptySet(){
		$profiler = $this->_profiler->getLastProfile();
		if($profiler){
			echo 'Empty set (', LocaleMath::round($profiler->getTotalElapsedSeconds(), 2), ' sec)', PHP_EOL, PHP_EOL;
		} else {
			echo 'Empty set (0.00 sec)', PHP_EOL, PHP_EOL;
		}
	}

	private function _inputPrompt(){
		echo "db2> ";
	}

	private function _readLine(){
		$command = '';
		while(true){
			$line = readline();
			if(substr($line, -1)==';'){
				$command.=substr($line, 0, strlen($line)-1);
				break;
			} else {
				$command.=$line;
				if($command=='quit'||$command=='exit'){
					$this->_connection->close();
					exit;
				}
				$command.=' ';
				echo "   > ";
			}
		}
		readline_add_history($command.';');
		return trim($command);
	}

	private function _prepareReadLine(){
		if(!function_exists('readline')){
			$this->_inputHandler = fopen('php://stdin', 'r');
		}
	}

}

try {
	$script = new Db2CliScript();
	$script->run();
}
catch(CoreException $e){
	ScriptColor::lookSupportedShell();
	echo ScriptColor::colorize(get_class($e).' : '.$e->getConsoleMessage()."\n", ScriptColor::LIGHT_RED);
	if($script->getOption('debug')=='yes'){
		echo $e->getTraceAsString()."\n";
	}
}
catch(Exception $e){
	echo 'Exception : '.$e->getMessage()."\n";
	if($script->getOption('debug')=='yes'){
		echo $e->getTraceAsString()."\n";
	}
}

