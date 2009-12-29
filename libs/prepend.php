<?php
/**
 * This file is a part of Xen Orchesrta.
 *
 * Xen Orchestra is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Xen Orchestra is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Xen Orchestra. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Xen Orchestra
 * @license http://www.gnu.org/licenses/gpl-3.0-standalone.html GPLv3
 **/

ini_set('default_socket_timeout', 4);

// The session is used for users management.
session_name('XO');
session_set_cookie_params(31*24*3600); // One month.
session_start();

define ('ROOT_DIR', dirname (dirname (__FILE__)));

set_include_path (ROOT_DIR . PATH_SEPARATOR . get_include_path ());

// autoload classes
function __autoload($class_name)
{
	require_once 'libs/' . $class_name . '.php';
}

