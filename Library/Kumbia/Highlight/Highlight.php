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
 * @package 	Highlight
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * Highlight
 *
 * Permite colorear código PHP a diferentes tipos de salidas (html, consola)
 *
 * @category 	Kumbia
 * @package 	Script
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class Highlight {

	/**
	 * Token que agrupa palabras reservadas
	 *
	 */
	const T_RESERVED_WORD = -1;

	/**
	 * Token que agrupa tokens no relevante para el resaltador
	 *
	 */
	const T_OTHER = -2;

	/**
	 * Adaptadores instanciados
	 *
	 * @var array
	 */
	private static $_adapters;

	/**
	 * Indica si el token es una palabra reservada
	 *
	 * @param	int $token
	 * @return 	boolean
	 */
	private static function _isReservedWord($token){
		switch($token){
			case T_NEW;
			case T_IF:
			case T_WHILE:
			case T_FOR:
			case T_FOREACH:
			case T_STATIC:
			case T_PUBLIC:
			case T_ECHO:
			case T_PRINT:
			case T_REQUIRE:
			case T_REQUIRE_ONCE:
			case T_RETURN:
			case T_ARRAY:
			case T_FINAL:
			case T_CATCH:
			case T_TRY:
			case T_FUNCTION:
			case T_THROW:
			case T_AS:
			case T_ELSE:
			case T_GLOBAL:
			case T_ISSET:
			case T_UNSET:
			case T_BREAK:
				return true;
			default:
				return false;
		}
	}

	/**
	 * Obtiene una cadena coloreada
	 *
	 * @param	string $phpCode
	 * @param 	string $adapter
	 * @param 	array $options
	 * @return	boolean
	 */
	public static function getString($phpCode, $adapter='html', $options=array()){

		if(!isset(self::$_adapters[$adapter])){
			$className = $adapter.'Highlight';
			if(class_exists($className, false)==false){
				$path = 'Library/Kumbia/Highlight/Adapters/'.ucfirst($adapter).'.php';
				if(file_exists($path)){
					require KEF_ABS_PATH.$path;
				} else {
					throw new HighlightException('No existe un adaptador de Highlight que se llame "'.$adapter.'"');
				}
			}
			self::$_adapters[$adapter] = new $className();
		}

		if(!isset($options['firstLine'])){
			$options['firstLine'] = 1;
		}

		$highString = '';
		$numberLine = 1;
		$phpLines = explode("\n", $phpCode);
		$numberPad = strlen(count($phpLines));

		if(!isset($options['lastLine'])){
			$options['lastLine'] = count($phpLines);
		}

		foreach($phpLines as $phpLine){

			if($numberLine<$options['firstLine']){
				$numberLine++;
				continue;
			}

			if($numberLine>$options['lastLine']){
				$numberLine++;
				continue;
			}

			$prependPHPTag = false;
			if(preg_match('/<?php /', $phpLine)===0){
				$phpLine = '<?php '.$phpLine;
				$prependPHPTag = true;
			}
			$tokens = @token_get_all($phpLine);
			$numberTokens = count($tokens);
			$highLine = '';
			for($i=0;$i<$numberTokens;++$i){
				$token = $tokens[$i];
				if(isset($token[1])){
					$token[1] = self::$_adapters[$adapter]->prepareStringBefore($token[1]);
					//echo token_name($token[0]), "\n";
				} else {
					$token[0] = self::$_adapters[$adapter]->prepareStringBefore($token[0]);
				}
				if($token[0]==T_COMMENT){
					$highLine.=self::$_adapters[$adapter]->getToken($token[1], T_COMMENT);
				} else {
					if($token[0]==T_CONSTANT_ENCAPSED_STRING||$token[0]==T_ENCAPSED_AND_WHITESPACE){
						$highLine.=self::$_adapters[$adapter]->getToken($token[1], T_CONSTANT_ENCAPSED_STRING);
					} else {
						if($token[0]==T_LNUMBER||$token[0]==T_NUM_STRING){
							$highLine.=self::$_adapters[$adapter]->getToken($token[1], T_LNUMBER);
						} else {
							if($token[0]==T_VARIABLE){
								$highLine.=self::$_adapters[$adapter]->getToken($token[1], T_VARIABLE);
							} else {
								if(self::_isReservedWord($token[0])){
									$highLine.=self::$_adapters[$adapter]->getToken($token[1], self::T_RESERVED_WORD);
								} else {
									if(isset($token[1])){
										$highLine.=self::$_adapters[$adapter]->getToken($token[1], self::T_OTHER);
									} else {
										if($token[0]=="\t"){
											$token[0] = ' ';
										}
										$highLine.= $token[0];
										//$highLine.= '<span class="tOther">'.$token[0].'</span>';
									}
								}
							}
						}
					}
				}
			}
			if($prependPHPTag==true){
				$highLine = substr($highLine, 5);
			}
			$highLine = self::$_adapters[$adapter]->getLineNumber(sprintf('%0'.$numberPad.'s', $numberLine).' ').' '.$highLine.' '.PHP_EOL;
			$highString.=$highLine;
			$numberLine++;
		}

		return self::$_adapters[$adapter]->prepareStringAfter($highString);
	}

}