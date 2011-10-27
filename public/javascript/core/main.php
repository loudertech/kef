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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: main.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */

@header('Content-Type: application/x-javascript', true);

echo "\$Kumbia = new Object();
\$Kumbia.app = \"".urldecode($_REQUEST['app']) ."\";
\$Kumbia.path = \"".urldecode($_REQUEST['path']) ."\";
\$Kumbia.controller = \"".$_REQUEST['controller'] ."\";
\$Kumbia.action = \"".$_REQUEST['action'] ."\";
\$Kumbia.id = \"".$_REQUEST['id']."\";\n";
