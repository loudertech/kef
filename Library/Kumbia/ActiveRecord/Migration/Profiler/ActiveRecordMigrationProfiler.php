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
 * @package 	ActiveRecord
 * @subpackage 	Migration
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * ActiveRecordMigrationProfiler
 *
 * Muestra en pantalla las operaciones realizadas sobre la BD y los tiempos que tomaron en ejecutarse
 *
 * @package 	ActiveRecord
 * @subpackage 	Migration
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id$
 */
class ActiveRecordMigrationProfiler extends DbProfiler {

	public function beforeStartProfile(DbProfilerItem $profile){
		echo $profile->getInitialTime(), ': ', str_replace(array("\n", "\t"), " ", $profile->getSQLStatement());
	}

	public function afterEndProfile($profile){
		echo '  => ', $profile->getFinalTime(), ' (', ($profile->getTotalElapsedSeconds()), ')', PHP_EOL;
	}

}