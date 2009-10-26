<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';

$data = array(
	'dom0s' => array(), // Contains all the dom0s.
	
	// Tells JavaScript that this list is exhaustive and it has to
	// remove all dom0s/domUs not listed.
	'exhaustive' => true
);

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
		$data['dom0s'][$dom0->id] = $tmp;
	}
}

echo json_encode($data);
