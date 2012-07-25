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
 * @subpackage	WebService
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: WebServiceController.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * Transactionable
 *
 * Defines methods must be implementable by Transaction-Based Webservices
 *
 * @category	Kumbia
 * @package		Controller
 * @subpackage	WebService
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 */

interface Transactionable {

	public function onRollbackAction();
	public function onCommitAction();

}