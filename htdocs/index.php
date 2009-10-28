<?php
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
			'name' => $dom0->address
		);

		$domUs = $dom0->getDomUs();
		if (empty($domUs))
		{
			$tmp['vm_number'] = 0;
			$tmp['domUs'] = null;
		}
		else
		{
			$tmp['vm_number'] = count($domUs);
			$tmp['domUs'] = array();
			foreach ($domUs as $domU)
			{
				if ($u->can(ACL::READ, $dom0->id, $domU->name))
				{
					$tmp['domUs'][$domU->id] = array(
						'name' => $domU->name,
						'state' => $domU->state,
						'vcpu_number' => $domU->vcpu_number,
						'vcpu_use' => $domU->vcpu_use
					);
				}
			}
		}
		if (!empty($tmp['domUs']) || $u->can(ACL::READ, $dom0->id))
		{
			$msg->dom0s[$dom0->id] = $tmp;
		}
	}
	return $msg;
}

if (!isset($_GET['a']))
{
	$v = new Gallic_View(ROOT_DIR . '/templates/index.php');

	$v->user = Model::get_current_user()->name;
	$v->json = dom0s_json()->get();
	$v->refresh = (Config::get_instance()->global['refresh'] * 1000);

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
			case 'delete':
			case 'pause':
			case 'play':
			case 'stop':
				$domU->$action();
		}

		$domU->refresh();
	}

	// In the future, the user may request info about more than one domU.
	$msg->dom0s = array(
		$dom0_id => array(
			'domUs' => array(
				$domU_id => $domU->get_all_infos()
			)
		)
	);

	// Tells JavaScript that this list is exhaustive and it has to
	// keep all dom0s/domUs not listed.
	$msg->exhaustive = false;
}
else
{
	$msg->error('No such action');
}

