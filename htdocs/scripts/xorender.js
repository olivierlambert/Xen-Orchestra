/**
 * Display a ram bar, to provide quick information about free ram
 * + the % free
 *
 * @param cpus_max Maximum amout of CPU installed on a Dom0
 */
function ram_bar(free_ram,total_ram,id)
{
	result = '<div id="ProgressBar_'+id+'" class="progressBar">';
	var pb = $('ProgressBar_'+id);
	free_ratio = Math.round(free_ram/total_ram * 100);
	occuped_ratio = -(100 - free_ratio);
	if (pb)
	{
		pb.setStyle({
			background:'url(img/progress.png)',
			border:'1px solid grey',
			width: '100px',
			height: '15px',
			padding: '0px',
			marginLeft: '20em',
			marginTop: '-1.6em',
			backgroundPosition:'-200px'
			});
		pb.morph({backgroundPosition: occuped_ratio+'px'}, { duration: 10 });
		//alert(pb.getStyle('background'));
	}
	return result += '&nbsp&nbsp&nbsp '+ free_ratio + ' % free</div>';
}

/**
 * Display a CPU_max list of a dom0
 * 
 *
 * @param cpus_max Maximum amout of CPU installed on a Dom0
 */
function html_cpu_select(cpus_max)
{
	result = '<select>';
	for (var i = 1; i <= cpus_max; i++)
	{
		result += '<option>'+i+'</option>';
	}
	return result += '</select>';
}

/**
 * Display CPU meters in function of their load
 * (green/yellow/orange/red)
 *
 * @param cpus Table of cpus with their respective load
 */
function html_cpu_meters(cpus)
{
	var n = cpus.length;
	if (n === 0)
	{
		return '&nbsp;';
	}

	var result = '';
	for (var i = 0; i < n; i++)
	{
		result += '<img title="'+cpus[i]+'" src="img/c';
		if (cpus[i] < 25)
		{
			result += 'green';
		}
		else if (cpus[i] < 50)
		{
			result += 'yellow';
		}
		else if (cpus[i] < 75)
		{
			result += 'orange';
		}
		else
		{
			result += 'red';
		}
		result += '.png">';
	}
	return result;
}

/**
 * Display CPU values in digital
 *
 * @param cpus Table of cpus with their respective load
 */
function html_cpu_values(cpus)
{
	var n = cpus.length;
	if (n === 0)
	{
		return '&nbsp;';
	}
	result=' ';
	for (var i = 0; i < n; i++)
	{
		result += cpus[i]+'% ';
	}
	return result;
}

/**
 * Fill a Dom0 panel with its information : each row contain a domU.
 *
 * @param dom0_id The identifier of the dom0.
 */
function content_dom0(dom0)
{
	if (Object.isEmpty(dom0.domUs))
	{
		return '<p>No DomU detected</p>';
	}
	var result = '<table><tr><th>Name</th><th>State</th><th>Load</th></tr>';
	for (domU_id in dom0.domUs)
	{
		var domU = dom0.domUs[domU_id];
		result += '<tr id="' + domU.name
			+ '" onclick="display_vm(\'' + domU_id + '\')"><td>' + domU.name
			+ '</td><td>' + domU.state
			+ '</td><td>' + html_cpu_meters(domU.vcpus)
			+ '</td></tr>';
	}
	return result + '</table>';
}

/**
 * Draw the logging area
 */
function draw_log_area()
{
	var d = $('login');
	if (user === 'guest') // The user is able to log in.
	{
		d.update(new Element('form')
			.insert(new Element('p')
				.insert('<label for="name">User: </label>')
				.insert(new Element('input', {
					'type': 'text',
					'name': 'name',
					'id': 'name'
				}))
				.insert(' <label for="password">Password: </label>')
				.insert(new Element('input', {
					'type': 'password',
					'name': 'password',
					'id': 'password'
				}))
				.insert(' ')
				.insert(new Element('input', {
					'type': 'submit',
					'value': 'Log in'
				}))
			)
			.observe('submit', function (e)
			{
				e.stop();

				var name = $F('name');

				if (name === '')
				{
					notify('The name field is mandatory.');
					return;
				}

				send_request('login', {
					'name': name,
					'password': MD5($F('password'))
				});
			})
		);
	}
	else
	{
		d.update(new Element('p')
			.insert('Logged as <em>' + user + '</em>. ')
			.insert(new Element('input', {'type': 'button', 'value': 'Log out'})
				.observe('click', function (e)
				{
					e.stop();
					send_request('logout');
				})
			)
		);
	}
}
