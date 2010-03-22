/**
 * Display the entire VM window
 *
 * @param TODO
 */
function render_vm(id,html_id,state,kernel,vcpus,d_min_ram,date,actions,
					on_shutdown,on_reboot,on_crash,weight,cap,targets)
{
	var html = '<ul id="tabs_' + html_id + '" class="menuvm">'
		+ '<li><a href="#overview_' + html_id + '"><b><img src="img/information.png" alt=""/>Overview</b></a></li>'
		+ '<li><a href="#livemig_' + html_id + '"><b><img src="img/livemig.png" alt=""/>Live Mig</b></a></li>'
		+ '<li><a href="#cpu_' + html_id + '"><b><img src="img/cpu.png" alt=""/>CPU</a></b></li>'
		+ '<li><a href="#ram_' + html_id + '"><b><img src="img/ram.png" alt=""/>RAM</a></b></li>'
		+ '<li><a href="#network_' + html_id + '"><b><img src="img/world.png" alt=""/>Network</a></b></li>'
		+ '<li><a href="#storage_' + html_id + '"><b><img src="img/drive.png" alt=""/>Storage</a></b></li>'
		+ '<li><a href="#misc_' + html_id + '"><b><img src="img/wrench.png" alt=""/>Misc</a></b></li>'
		+  '</ul><div id="overview_' + html_id + '" class="text">';

	html+='<br/><p><b>State: </b>' + state + '</p>'
		+ '<p><b>System: </b>' + kernel + '</p>'
		+ '<p><b>CPU installed: </b>' + vcpus.length + '</p>'
		+ '<p><b>RAM installed: </b>' + d_min_ram/(1<<20) + ' MB</p>'
		+ '<p><b>Date of creation: </b>' + date + '</p>';
		
	html += '<p><b>Actions: </b><br/>';
	for (var i = 0; i < actions.length; ++i)
	{
		html += ('<img class="button" src="img/' + actions[i]
			+ '.png" alt="" title="'+ actions[i] +'" onclick="action_vm (\'' + id + '\', \''
			+ actions[i] + '\')" />');
	}
	html += '</p>';
	html += '</div>';

	html+='<div id="livemig_' + html_id + '">';
	
	if (targets.length !== 0)
	{
		html += '<br/><p><b>Live Migration Target: </b></p><br/>';
		for (var i = 0; i < targets.length; ++i)
		{
			html += '<p><a href="#" onclick="action_vm(\'' + id
				+ '\', \'migrate\', {\'t\': \'' + targets[i] + '\'})">'
				+ dom0s[targets[i]].address + '</a> ('
				+ Math.round(dom0s[targets[i]].freeram/1073741824) +' GB left) '
				+ '<div id="ProgressBar_'+i+'" class="progressBar"></div> </p><br/>';
		}
	}
	html += '</div>';

	html +='<div id="cpu_' + html_id + '">'
		+ '<form id="cpu_' + html_id + '">'
		+ '<table class="center">'
		+ '<tr>'
		+ '<td>VCPU use:</td><td>'+html_cpu_values(vcpus)+'</td>'
		+'</tr>'
		+ '<tr>'
		+ '<td>VCPU number:</td><td>'+vcpus.length+'</td>'
		+'</tr>'
		//+ '<tr>'
		//+ '<td>Set VCPU number:</td><td>'+html_cpu_select(dom0.cpus)+'</td>'
		//+'</tr>'
		+ '<tr>'
		+ '<td>Set Cap:</td><td><input type="text" size="2" value="'+cap+'"></td>'
		+'</tr>'
		+ '<tr>'
		+ '<td>Set Weight:</td><td><input type="text" size="2" value="'+weight+'"></td>'
		+'</tr>'
		+'</table><br/><p class="center"><input type="submit" value="OK"></p>'
		+ '</form></div>';

		html+='<div id="ram_' + html_id + '">'
			+ '<form id="ram_' + html_id + '">'
			+ '<table class="center">'
			+ '<tr>'
			+ '<td>Current RAM:</td><td><input type="text" size="2" value="'+d_min_ram/(1024*1024)+'"> MB</td>'
			+'</tr>'
			//+ '<tr>'
			//+ '<td>Maximum RAM:</td><td><input type="text" size="2" value="'+s_max_ram/(1024*1024)+'"> MB</td>'
			//+'</tr>'
			//+ '<tr>'
			//+ '<td>Minimum RAM:</td><td><input type="text" size="2" value="'+s_min_ram/(1024*1024)+'"> MB</td>'
			//+'</tr>'
			+'</table><br/><p class="center"><input type="submit" value="OK"></p>'
			+ '</form></div>';

		html+='<div id="network_' + html_id + '"></div>';

		html+='<div id="storage_' + html_id + '"></div>';

		html+='<div id="misc_' + html_id + '">'
			+ '<br/><b><p>On shutdown:</b> '+on_shutdown+'</p>'
			+ '<b><p>On reboot:</b> '+on_reboot+'</p>'
			+ '<b><p>On crash:</b> '+on_crash+'</p></div>';

return html;
}

/**
 * Display a ram bar, to provide quick information about free ram
 * + the % free
 *
 * @param targets, others Xen servers which are possible target to live mig
 */
function render_live_vm(targets)
{
	if (targets.length !== 0)
	{
		for (var i = 0; i < targets.length; ++i)
		{
			var pb = $('ProgressBar_'+i);
			var free_ram = dom0s[targets[i]].freeram;
			var total_ram = dom0s[targets[i]].totalram;
			free_ratio = Math.round(free_ram/total_ram * 100);
			occuped_ratio = -(100 - free_ratio);
			if (pb)
			{
				pb.update('&nbsp &nbsp &nbsp '+-occuped_ratio+ '% used');
				pb.innerHTML;
				Element.setStyle(pb,{
					background:'url(img/progress.png) left no-repeat',
					border:'1px solid grey',
					width: '100px',
					height: '15px',
					padding: '0px',
					marginLeft: '20em',
					marginTop: '-1.6em',
					backgroundPosition:'-100px'
					});
		// after, if it works, use morph to animate the bar
		pb.morph({backgroundPosition: ''+-free_ratio+'px'}, {duration: 1});
			}
		}
	}
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
