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

require_once dirname (__FILE__) . '/../includes/prepend.php';
require 'includes/header.php';
?>
<div id="main"
<h3>Available API method on Dom0's (debug purpose)</h3>
<?php
foreach (Model::get_dom0s() as $dom0)
{
	$dom0_array = $dom0->host_record();
	echo '<h4>'.$dom0->id.'</h4>';
	echo '<p>';
	foreach ($dom0_array as $dom0) {
		echo $dom0.'  &nbsp|&nbsp  ';
	}
	echo '</p>';
}
?>
</div>
