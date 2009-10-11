<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';

$result = array();

$u = Model::get_current_user();

foreach (Model::get_dom0s() as $dom0)
{
	// The array for this dom0.
	$tmp = array(
		'id' => $dom0->id,
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
				$tmp['domUs'][] = array(
					'name' => $domU->name,
					'state' => $domU->state,
					'cpu_number' => $domU->vcpu_number,
					'cpu_use' => $domU->vcpu_use
				);
			}
		}
	}
	if (!empty($tmp['domUs']) || $u->can(ACL::READ, $dom0->id))
	{
		$result[] = $tmp;
	}
}

echo json_encode($result);
