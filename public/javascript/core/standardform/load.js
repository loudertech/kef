
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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (C) 2005-2008 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 */

function $C(element){
	return $('flid_'+element);
}

function $V(element){
	return $F('flid_'+element);
}

function enable_browse(obj, action){
	window.location = Utils.getKumbiaURL(action+"/browse/");
}
