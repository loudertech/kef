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
 * @package		Db
 * @subpackage	Profiler
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Interface.php 121 2010-02-06 22:43:30Z gutierrezandresfelipe $
 */

/**
 * DbProfilerInterface
 *
 * Interface que deben implementar todos los Profilers de Bases de Datos
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Profiler
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
interface DbProfilerInterface {

	public function startProfile($sqlStatement);
	public function stopProfile();
	public function getNumberTotalStatements();
	public function getTotalElapsedSeconds();
	public function getProfiles();
	public function reset();
	public function getLastProfile();

}
