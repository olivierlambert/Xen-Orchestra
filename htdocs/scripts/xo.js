/**
 * Object which contains all dom0s which, theirselves contain their
 * domUs.
 */
var dom0s = {};

/**
 * Time between each refresh (in seconds)
 */
var refresh_time;
/**
 * The portal object : refer to class portal, which displays dom0s in
 * panels, which can be moved.
 */
var portal;

/**
 * Table which contains all dom0s panels
 */
var dom0s_panels = [];

/**
 * TODO: write doc.
 */
var domUs_windows = {};

/**
 * Contains the name of the current user.
 */
var user = 'guest';

/**
 * Indicates whether the user is dragging a panel.
 *
 * Used to prevent the panel "doubling effect": the panels are not
 * updated when on_drag is true.
 */
var on_drag = false;

/**
 * TODO: write doc.
 */
Object.extendRecursively = function (destination, source)
{
	for (var property in source)
	{
		if ((typeof(source[property]) === 'object')
			&& (typeof(destination[property]) === 'object'))
		{
			Object.extendRecursively(destination[property], source[property]);
		}
		else
		{
			destination[property] = source[property];
		}
	}
	return destination;
};

/**
 * Number of running asynchronous tasks.
 */
tasks = 0;

/**
 * TODO: write doc.
 */
function task_start()
{
	if (tasks++ === 0)
	{
		$(document.documentElement).setStyle ({'cursor': 'progress'});
	}
}

/**
 * TODO: write doc.
 */
function task_stop()
{
	if (--tasks === 0)
	{
		$(document.documentElement).setStyle ({'cursor': 'auto'});
	}
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
 * Display a new window with all informations about a selectionned domU
 *
 * @param dom0_id The identifier of the dom0 the domU belongs to.
 * @param domU_id The domU's identifier.
 */
function domU_window(dom0_id, domU_id)
{
	var domU = dom0s[dom0_id].domUs[domU_id];

	var html_id = escape(dom0_id + domU_id).replace(/\.|#|%/g, '_');

	var date = new Date(domU.date * 1000);
	var html = '<div id="vm">'
		+ '<ul id="tabs_' + html_id + '" class="menuvm">'
		+ '<li><a href="#overview_' + html_id + '"><b><img src="img/information.png" alt=""/>Overview</b></a></li>'
		+ '<li><a href="#cpu_' + html_id + '"><b><img src="img/cpu.png" alt=""/>CPU</a></b></li>'
		+ '<li><a href="#ram_' + html_id + '"><b><img src="img/ram.png" alt=""/>RAM</a></b></li>'
		+ '<li><a href="#network_' + html_id + '"><b><img src="img/world.png" alt=""/>Network</a></b></li>'
		+ '<li><a href="#storage_' + html_id + '"><b><img src="img/database_gear.png" alt=""/>Storage</a></b></li>'
		+ '<li><a href="#misc_' + html_id + '"><b><img src="img/wrench.png" alt=""/>Misc</a></b></li>'
		+  '</ul><div id="overview_' + html_id + '" class="text">';

	html+='<br/><p><b>State: </b>'+domU.state+'</p>'
		+ '<p><b>System: </b>'+domU.kernel+'</p>'
		+ '<p><b>CPU installed: </b>'+domU.vcpu_number+'</p>'
		+ '<p><b>RAM installed: </b>'+domU.d_min_ram/(1024*1024)+' MB</p>'
		+ '<p><b>Date of creation: </b>' + date + '</p>';

	html += '<p><b>Actions: </b><br/>';
	if (domU.state === 'Running')
	{
		var actions = ['pause', 'stop'];
	}
	else if (domU.state === 'Paused')
	{
		var actions = ['play', 'stop'];
	}
	else if (domU.state === 'Halted')
	{
		var actions = ['play'];
	}
	actions.push('destroy');
	for (var i = 0; i < actions.length; ++i)
	{
		html += ('<img class="button" src="img/' + actions[i]
			+ '.png" alt="" onclick="action_vm (\'' + dom0_id
			+ '\', \'' + domU.name + '\', \'' + actions[i] + '\')" />');
	}
	html+='</p></div>';

	html+='<div id="cpu_' + html_id + '">'
		+ '<br/><p><b>VCPU use:</b> '+domU.vcpu_use+'</p>'
		+ '<p><b>VCPU at startup:</b> '+domU.vcpus_at_startup+'</p>'
		+ '<p><b>VCPU number:</b> '+domU.vcpu_number+'</p>'
		+ '<p><b>Cap:</b> '+domU.cap+'</p>'
		+ '<p><b>Weight:</b> '+domU.weight+'</p></div>';

	html+='<div id="ram_' + html_id + '">'
		+ '<br/><b><p>RAM:</b> '+domU.d_min_ram/(1024*1024)+' MB</p></div>';

	html+='<div id="network_' + html_id + '"></div>';

	html+='<div id="storage_' + html_id + '"></div>';

	html+='<div id="misc_' + html_id + '">'
		+ '<br/><b><p>On shutdown:</b> '+domU.actions_after_shutdown+'</p>'
		+ '<b><p>On reboot:</b> '+domU.actions_after_reboot+'</p>'
		+ '<b><p>On crash:</b> '+domU.actions_after_crash+'</p></div>';

	html+='</div>';

	title = '<b>'+domU.name+'</b> (' + dom0s[dom0_id].name + ')';

	if (domUs_windows[domU_id] === undefined)
	{
		domUs_windows[domU_id] = new Window({
			className: 'alphacube',
			showProgress: true,
			onClose: function ()
			{
				delete domUs_windows[domU_id];
			}
		});
		domUs_windows[domU_id].show();
		domUs_windows[domU_id].setSize(500, 204);
	}
	domUs_windows[domU_id].setTitle(title);
	domUs_windows[domU_id].setHTMLContent(html);
	new Control.Tabs('tabs_' + html_id);
}

/**
 * Sends a request to XO to change the current of state of a domU,
 * then display/refresh the domU's window.
 *
 * @param dom0   The identifier of the dom0 the domU belongs to.
 * @param domU   The domU's identifier.
 * @param action The action to do among (destroy, halt, pause, start).
 */
function action_vm(dom0_id, domU_id, action)
{
	task_start();
	new Ajax.Request('index.php', {
		method: 'get',
		parameters: {
			'a': 'domU',
			'action': action,
			'dom0': dom0_id,
			'domU': domU_id
		},
		onComplete: function (transport)
		{
			register_info(transport.responseText.evalJSON());
			domU_window (dom0_id, domU_id);
			task_stop();
		},
		onFailure: function ()
		{
			task_stop();
			alert('Something went wrong...');
		}
	});
}

/**
 * Gets information about a domU and display (or refresh) the domU's
 * window.
 *
 * @param dom0_id The identifier of the dom0 the domU belongs to.
 * @param domU_id The domU's identifier.
 */
function display_vm(dom0_id, domU_id)
{
	if (domUs_windows[domU_id] !== undefined)
	{
		// The window already exists, maybe we should put it on the
		// foreground.
		return;
	}

	task_start();
	new Ajax.Request('index.php', {
		method: 'get',
		parameters: {
			'a': 'domU',
			'dom0': dom0_id,
			'domU': domU_id
		},
		onComplete: function (transport)
		{
			register_info(transport.responseText.evalJSON());
			domU_window (dom0_id, domU_id);
			task_stop();
		},
		onFailure: function ()
		{
			task_stop();
			alert('Something went wrong...');
		}
	});
}

// New functions

/**
 * Every "refresh_time" seconds, gets fresher info and updates display.
 */
function refresh()
{
	task_start();
	new Ajax.Request('index.php', {
		method: 'get',
		parameters: {
			'a': 'dom0s'
		},
		onComplete: function(transport)
		{
			register_info(transport.responseText.evalJSON());
			setTimeout(refresh, refresh_time);
			task_stop();
		},
		onFailure: function()
		{
			task_stop();
			alert('Something went wrong...');
		}
	});
}

/**
 * Draw and display dom0s panels. Place them in order to optimize
 * space on the screen.
 */
function update_portal()
{
	while (w = dom0s_panels.pop())
	{
		portal.remove(w);
	}

	var weightleft = 0;
	var weightright = 0;
	for (dom0_id in dom0s)
	{
		var dom0 = dom0s[dom0_id];
		var w = new Xilinus.Widget()
			.setTitle(dom0.name)
			.setContent(content_dom0(dom0_id));
		if (weightleft <= weightright)
		{
			weightleft += dom0.vm_number + 1;
			portal.add(w, 0);
		}
		else
		{
			weightright += dom0.vm_number + 1;
			portal.add(w, 1);
		}
		dom0s_panels.push(w);
	};
}

/**
 * Fill a Dom0 panel with its information : each row contain a domU.
 *
 * @param dom0_id The identifier of the dom0.
 */
function content_dom0(dom0_id)
{
	var dom0 = dom0s[dom0_id];
	if (dom0.vm_number === 0)
	{
		return '<p>No DomU detected</p>';
	}

	var result = '<table><tr><th>Name</th><th>State</th><th>Load</th></tr>';
	for (domU_id in dom0.domUs)
	{
		var domU = dom0.domUs[domU_id];
		result += '<tr id="' + domU.name
			+ '" onclick="display_vm(\'' + dom0_id + '\',\'' + domU_id
			+'\')"><td>' + domU.name + '</td><td>' + domU.state
			+ '</td><td>' + html_cpu_meters(domU.vcpu_use)
			+ '</td></tr>';
	}
	return result + '</table>';
}

function login(event)
{
	event.stop();

	var name = $F('name');

	if (name === '')
	{
		alert('The name field is mandatory.');
		return;
	}

	var password = $F('password');
	new Ajax.Request('index.php', {
		method: 'get',
		parameters: {
			'a': 'login',
			'name': name,
			'password': MD5(password)
		},
		onComplete: function (data)
		{
			var json = data.responseText.evalJSON();
			if (json.error_code !== 0)
			{
				alert('Error: ' + json.error_message);
				return;
			}

			register_info(json);
			draw_log_area();
		}
	});
}

function logout(event)
{
	event.stop();

	new Ajax.Request('index.php', {
		method: 'get',
		parameters: {
			'a': 'logout'
		},
		onComplete: function (data)
		{
			var json = data.responseText.evalJSON();
			if (json.error_code !== 0)
			{
				alert('Error: ' + json.error_message);
				return;
			}

			register_info(json);
			draw_log_area();
		}
	});
}

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
			.observe('submit', login)
		);
	}
	else
	{
		d.update(new Element('p')
			.insert('Logged as <em>' + user + '</em>. ')
			.insert(new Element('input', {'type': 'button', 'value': 'Log out'})
				.observe('click', logout)
			)
		);
	}
}

/**
 * TODO: write doc.
 */
function register_info(info)
{
	if ((info.user !== undefined)
		&& (info.user !== user))
	{
		user = info.user;
		draw_log_area();
	}

	if (info.exhaustive)
	{
		dom0s = {};
	}

	if ((info.dom0s !== undefined) && !(info.dom0s instanceof Array))
	{
		Object.extendRecursively(dom0s, info.dom0s);
	}

	if ((portal !== undefined) && !on_drag)
	{
		update_portal();
	}
}

/**
 * When the DOM is fully loaded, initialize all listeners and Portal.
 */
document.observe('dom:loaded', function ()
{
	draw_log_area();

	Xilinus.Portal.prototype.startDrag_old = Xilinus.Portal.prototype.startDrag;
	Xilinus.Portal.prototype.startDrag = function (eventName, draggable)
	{
		on_drag = true;
		this.startDrag_old(eventName, draggable);
	}
	Xilinus.Portal.prototype.endDrag_old = Xilinus.Portal.prototype.endDrag;
	Xilinus.Portal.prototype.endDrag = function (eventName, draggable)
	{
		on_drag = false;
		this.endDrag_old(eventName, draggable);
	}

	portal = new Xilinus.Portal('#main div');
	update_portal();

	setTimeout(refresh, refresh_time);
});

