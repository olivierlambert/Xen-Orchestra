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
require_once dirname (__FILE__) . '/../libs/prepend.php';

function dom0s_json($msg = NULL)
{
	if ($msg === null)
	{
		$msg = new MessengerJSON();
	}

	$msg->dom0s = array(); // Contains all the dom0s.
	
	// Tells JavaScript that this list is exhaustive and it has to
	// remove all dom0s/domUs not listed.
	$msg->exhaustive = true;

	$u = Model::get_current_user();

	foreach (Model::get_dom0s() as $dom0)
	{
		// The array for this dom0.
		$tmp = array(
			'address' => $dom0->address,
			'domUs' => array(),
			'id' => $dom0->id,
			'ro' => !$u->can(ACL::WRITE, $dom0->id),
		);

		$domUs = $dom0->getDomUs();
		$n = count($domUs);
		if ($n !== 0)
		{
			foreach ($domUs as $domU)
			{
				if ($u->can(ACL::READ, $dom0->id, $domU->name))
				{
					$cpus = $domU->VCPUs_utilisation;
					foreach ($cpus as &$cpu)
					{
						$cpu = 100 * round($cpu, 4);
					}
					sort($cpus);

					$tmp['domUs'][] = array(
						'cpus' => $cpus,
						'id' => $domU->id,
						'name' => $domU->name,
						'ro' => !$u->can(ACL::WRITE, $dom0->id, $domU->name),
						'state' => $domU->power_state,
					);
				}
			}
		}
		if (!empty($tmp['domUs']) || $u->can(ACL::READ, $dom0->id))
		{
			$msg->dom0s[] = $tmp;
		}
	}
	return $msg;
}

if (!isset($_GET['a']))
{
	$v = new Gallic_View(ROOT_DIR . '/templates/index.php');

	$msg = dom0s_json();
	$msg->user = Model::get_current_user()->name;
	$msg->refresh = Config::get_instance()->global['refresh'] * 1000;

	$v->json = $msg->get();

	$v->render();
	exit;
}

$msg = new MessengerJSON(true);
if ($_GET['a'] === 'login')
{
	if (!isset($_GET['name']) || (($name = $_GET['name']) === ''))
	{
		$msg->error('Invalid name');
		exit;
	}

	if (!Database::is_enabled())
	{
		$msg->error('The database is disabled');
		exit;
	}

	$password = isset($_GET['password']) ? $_GET['password'] : '';
	$u = Model::register_current_user($name, $password, true);
	if ($u === false)
	{
		$msg->error('Incorrect username or password');
	}
	else
	{
		$msg->user = $u->name;
	}
}
elseif ($_GET['a'] === 'logout')
{
	Model::unregister_current_user();

	assert(Model::get_current_user()->name === 'guest');

	$msg->user = 'guest';
}
elseif ($_GET['a'] === 'dom0s')
{
	dom0s_json($msg);
}
elseif ($_GET['a'] === 'domU')
{
	$action = isset ($_GET['action']) ? $_GET['action'] : false;

	$dom0_id = isset ($_GET['dom0']) ? $_GET['dom0'] : false;
	$domU_id = isset ($_GET['domU']) ? $_GET['domU'] : false;

	$u = Model::get_current_user();
	if (!$u->can(ACL::READ, $dom0_id, $domU_id))
	{
		$msg->error('Access denied');
		exit;
	}

	$dom0 = Model::get_dom0($dom0_id);
	if ($dom0 === false)
	{
		$msg->error('No such dom0');
		exit;
	}

	$domU = $dom0->getDomU($domU_id);
	if ($domU === false)
	{
		$msg->error('No such domU');
		exit;
	}

	if ($action)
	{
		if (!$u->can(ACL::WRITE, $dom0_id, $domU_id))
		{
			$msg->error('Access denied');
			exit;
		}
		switch ($action)
		{
			case 'destroy':
			case 'pause':
			case 'play':
			case 'stop':
			case 'poweroff':
				$domU->$action();
		}

		$domU->refresh();
	}

	$cpus = $domU->VCPUs_utilisation;
	foreach ($cpus as &$cpu)
	{
		$cpu = 100 * round($cpu, 4);
	}
	sort($cpus);

	// In the future, the user may request info about more than one domU.
	$msg->domU = array(
		'cap' => $domU->VCPUs_params['cap'],
		'cpus' => $cpus,
		'd_min_ram' => $domU->memory_dynamic_min,
		'dom0_id' => $domU->dom0->id,
		'id' => $domU->id,
		'kernel' => $domU->PV_kernel,
		'name' => $domU->name,
		'ro' => !$u->can(ACL::WRITE, $dom0->id, $domU->name),
		'state' => $domU->power_state,
		'start_time' => $domU->start_time->timestamp,
		'weight' => $domU->VCPUs_params['weight'],
	);
}
else
{
	$msg->error('No such action');
}

