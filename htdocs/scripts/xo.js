/**
 * Object which contains all dom0s indexed by their id.
 */
var dom0s = {};

/**
 * Object which contains all dom0s indexed by their id.
 */
var domUs = {};

/**
 * Time between each refresh (in seconds)
 */
var refresh_time = 10;
/**
 * The portal object : refer to class portal, which displays dom0s in
 * panels, which can be moved.
 */
var portal;

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
 * Number of running asynchronous tasks.
 */
var tasks = 0;

/**
 * Helps us distribute the panels.
 */
var balance = 0;


function Dom0(id, address, ro)
{
	this.id = id;
	this.domUs = {};

	this._panel = new Xilinus.Widget(); // Panel associated to this Dom0.
	portal.add(this._panel, balance++ % 2);

	this.update(address, ro);
}
Dom0.prototype = {
	finalize: function ()
	{
		portal.remove(this._panel);
	},
	update: function (address, ro)
	{
		this.address = address;
		this.ro = ro;

		this._panel.setTitle(this.address).setContent(content_dom0(this));
	},
	addDomU: function (domU)
	{
		this.domUs[domU.id] = domU;
		
		this._panel.setContent(content_dom0(this));
		this._panel.updateHeight();
	},
	removeDomU: function (domU_id)
	{
		if (this.domUs[domU_id] === undefined)
		{
			return;
		}

		delete this.domUs[domU_id];

		this._panel.setContent(content_dom0(this));
		this._panel.updateHeight();
	},
};


function DomU(id, dom0, name, cpus, state, ro)
{
	this.id = id;
	this.dom0 = null;
	this.window = null; // Window associated to this DomU.

	this.update(dom0, name, cpus, state, ro);
}
DomU.prototype = {
	finalize: function ()
	{
		if (this.window !== null)
		{
			// Closes the window.
		}
	},
	update: function (dom0, name, cpus, state, ro, cap, d_min_ram, kernel,
		on_crash, on_reboot, on_shutdown, start_time, weight)
	{
		this.name = name;
		this.cpus = cpus;
		this.state = state;
		this.ro = ro;

		if (cap !== undefined) // The second part of arguments is optional.
		{
			this.cap = cap;
			this.d_min_ram = d_min_ram;
			this.kernel = kernel;
			this.on_crash = on_crash;
			this.on_reboot = on_reboot;
			this.on_shutdown = on_shutdown;
			this.start_time = start_time;
			this.weight = weight;
		}

		if (this.dom0 !== null)
		{
			this.dom0.removeDomU(this.id);
		}
		this.dom0 = dom0;
		this.dom0.addDomU(this);
		//this._panel.updateHeight();

		if (this.window !== null)
		{
			this._refresh_window();
			this._panel.updateHeight();
		}
	},
	open_window: function ()
	{
		_this = this;
		this.window = new Window({
			className: 'alphacube',
			showProgress: true,
			onClose: function ()
			{
				_this.window = null;
			}
		});

		this._refresh_window();

		this.window.setSize(500, 206);
		this.window.show();
	},
	_refresh_window: function ()
	{
		var html_id = escape(this.id).replace(/\.|#|%/g, '_');

		var date = new Date(this.start_time * 1000);
		var html = '<div id="vm">'
			+ '<ul id="tabs_' + html_id + '" class="menuvm">'
			+ '<li><a href="#overview_' + html_id + '"><b><img src="img/information.png" alt=""/>Overview</b></a></li>'
			+ '<li><a href="#cpu_' + html_id + '"><b><img src="img/cpu.png" alt=""/>CPU</a></b></li>'
			+ '<li><a href="#ram_' + html_id + '"><b><img src="img/ram.png" alt=""/>RAM</a></b></li>'
			+ '<li><a href="#network_' + html_id + '"><b><img src="img/world.png" alt=""/>Network</a></b></li>'
			+ '<li><a href="#storage_' + html_id + '"><b><img src="img/database_gear.png" alt=""/>Storage</a></b></li>'
			+ '<li><a href="#misc_' + html_id + '"><b><img src="img/wrench.png" alt=""/>Misc</a></b></li>'
			+  '</ul><div id="overview_' + html_id + '" class="text">';

		html+='<br/><p><b>State: </b>' + this.state + '</p>'
			+ '<p><b>System: </b>' + this.kernel + '</p>'
			+ '<p><b>CPU installed: </b>' + this.cpus.length + '</p>'
			+ '<p><b>RAM installed: </b>' + this.d_min_ram/(1<<20) + ' MB</p>'
			+ '<p><b>Date of creation: </b>' + date + '</p>';

		if (!this.ro)
		{
			if (this.state === 'Running')
			{
				var actions = ['pause', 'stop'];
			}
			else if (this.state === 'Paused')
			{
				var actions = ['play', 'stop'];
			}
			else if (this.state === 'Halted')
			{
				var actions = ['play'];
			}
			actions.push('destroy');

			html += '<p><b>Actions: </b><br/>';
			for (var i = 0; i < actions.length; ++i)
			{
				html += ('<img class="button" src="img/' + actions[i]
					+ '.png" alt="" onclick="action_vm (\'' + this.id + '\', \''
					+ actions[i] + '\')" />');
			}
			html += '</p>';
		}
		html += '</div>';

		html+='<div id="cpu_' + html_id + '">'
			+ '<br/><p><b>VCPU use:</b> '+html_cpu_values(this.cpus)+'</p>'
			+ '<p><b>VCPU number:</b> '+this.cpus.length+'</p>'
			+ '<p><b>Cap:</b> '+this.cap+'</p>'
			+ '<p><b>Weight:</b> '+this.weight+'</p></div>';

		html+='<div id="ram_' + html_id + '">'
			+ '<br/><b><p>RAM:</b> '+this.d_min_ram/(1024*1024)+' MB</p></div>';

		html+='<div id="network_' + html_id + '"></div>';

		html+='<div id="storage_' + html_id + '"></div>';

		html+='<div id="misc_' + html_id + '">'
			+ '<br/><b><p>On shutdown:</b> '+this.actions_after_shutdown+'</p>'
			+ '<b><p>On reboot:</b> '+this.actions_after_reboot+'</p>'
			+ '<b><p>On crash:</b> '+this.actions_after_crash+'</p></div>';

		html+='</div>';

		title = '<b>' + this.name + '</b> (' + this.dom0.address + ')';

		this.window.setTitle(title);
		this.window.setHTMLContent(html);
		new Control.Tabs('tabs_' + html_id);
	},
};


/**
 * TODO: write doc.
 */
function task_start()
{
	if (tasks++ === 0) // First task to start.
	{
		$(document.documentElement).setStyle ({'cursor': 'progress'});
	}
}

/**
 * TODO: write doc.
 */
function task_stop()
{
	if (--tasks === 0) // Last task to end.
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
 * Sends a request to XO to change the current of state of a domU,
 * then display/refresh the domU's window.
 *
 * @param domU   The domU's identifier.
 * @param action The action to do among (destroy, halt, pause, start).
 */
function action_vm(domU_id, action)
{
	var dom0_id = domUs[domU_id].dom0.id;
	send_request('domU', {'action': action, 'dom0': dom0_id, 'domU': domU_id}, {
		onFailure: function ()
		{
			notify('The request did not succeed.');
		}
	});
}

/**
 * Gets information about a domU and display (or refresh) the domU's
 * window.
 *
 * @param domU_id The domU's identifier.
 */
function display_vm(domU_id)
{
	var domU = domUs[domU_id];
	if (domU.window !== null)
	{
		// The window already exists, maybe we should put it on the
		// foreground.
		return;
	}

	domU.open_window();
	var dom0_id = domU.dom0.id;
	send_request('domU', {'dom0': dom0_id, 'domU': domU_id}, {
		onFailure: function ()
		{
			notify('The request did not succeed.');
		}
	});
}

// New functions

/**
 * Every "refresh_time" seconds, gets fresher info and updates display.
 */
function refresh()
{
	send_request('dom0s', undefined, {
		onComplete: function ()
		{
			setTimeout(refresh, refresh_time);
		}
	});
}

/**
 * Fill a Dom0 panel with its information : each row contain a domU.
 *
 * @param dom0_id The identifier of the dom0.
 */
function content_dom0(dom0)
{
	if (dom0.domUs.length === 0)
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
			+ '</td><td>' + html_cpu_meters(domU.cpus)
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
		notify('The name field is mandatory.');
		return;
	}

	send_request('login', {
		'name': name,
		'password': MD5($F('password'))
	});
}

function logout(event)
{
	event.stop();

	send_request('logout');
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
 * Sends a request with AJAX and once completed transmits the results to
 * register_info.
 *
 * @param action The action (string).
 * @param [parameters] Parameters to transmit (object).
 * @param [options] Options (object).
 */
function send_request(action, parameters, options)
{
	var ar_options = {
		'method': 'get',
		'onComplete': function (data)
		{
			task_stop();
			if ((options !== undefined)
				&& (typeof(options.onComplete) === 'function'))
			{
				options.onComplete(data);
			}
		},
		'onSuccess': function (data)
		{
			register_info(data.responseText.evalJSON());
			if ((options !== undefined)
				&& (typeof(options.onSuccess) === 'function'))
			{
				options.onSuccess(data);
			}
		}
	};
	if (parameters !== undefined)
	{
		ar_options.parameters = parameters;
	}
	if (options !== undefined)
	{
		if (typeof(options.onFailure) === 'function')
		{
			ar_options.onFailure = options.onFailure;
		}
		if (typeof(options.method) === 'string')
		{
			ar_options.method = options.method;
		}
	}

	task_start();
	new Ajax.Request('index.php?a=' + encodeURIComponent(action), ar_options);
}

/**
 * TODO: write doc.
 */
function register_info(info)
{
	if (info.error_code !== 0)
	{
		notify('Error: ' + info.error_message);
	}

	if (info.refresh !== undefined)
	{
		refresh_time = info.refresh;
	}

	if ((info.user !== undefined)
		&& (info.user !== user))
	{
		user = info.user;
		draw_log_area();
	}

	if (info.dom0s !== undefined)
	{
		// Allows us to see at the end which Dom0s are not present anymore.
		var dom0s_diff = Object.clone(dom0s);

		// Allows us to see at the end which DomUs are not present anymore.
		var domUs_diff = Object.clone(domUs);

		for (var i = 0; i < info.dom0s.length; ++i)
		{
			var record = info.dom0s[i];
			if (dom0s[record.id] === undefined)
			{
				dom0s[record.id] = new Dom0(record.id, record.address, record.ro);
			}
			else
			{
				dom0s[record.id].update(record.address, record.ro);
			}

			for (var j = 0; j < record.domUs.length; ++j)
			{
				var r = record.domUs[j];
				if (domUs[r.id] === undefined)
				{
					domUs[r.id] = new DomU(r.id, dom0s[record.id], r.name,
						r.cpus, r.state, r.ro);
				}
				else
				{
					domUs[r.id].update(dom0s[record.id], r.name, r.cpus,
						r.state, r.ro);
				}

				delete domUs_diff[r.id];
			}

			delete dom0s_diff[record.id];
		}

		if (info.exhaustive)
		{
			// This list is exhaustive, we have to remove the Dom0s and DomUs
			// which are in dom0s_diff and domUs_diff.
			for (var id in dom0s_diff)
			{
				dom0s_diff[id].finalize();
				delete dom0s[id];
			}
			for (var id in domUs_diff)
			{
				domUs_diff[id].finalize();
				delete domUs[id];
			}
		}
	}

	if (info.domU !== undefined)
	{
		// DomU and Dom0 have to already exist.
		var domU = domUs[info.domU.id];
		var dom0 = dom0s[info.domU.dom0_id];

		domU.update(dom0, info.domU.name, info.domU.cpus, info.domU.state,
			info.domU.ro, info.domU.cap, info.domU.d_min_ram, info.domU.kernel,
			info.domU.on_crash, info.domU.on_reboot, info.domU.on_shutdown,
			info.domU.start_time, info.domU.weight);
	}
}

/**
 * Presents a notification message to the user.
 *
 * For the moment, it uses an alert box, but in the future it may be prettier.
 */
function notify(message)
{
	alert(message);
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

	setTimeout(refresh, refresh_time);
});

