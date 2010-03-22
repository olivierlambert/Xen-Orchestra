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

/**
 * Object which contains all dom0s indexed by their id.
 */
var dom0s = {};

/**
 * Object which contains all dom0s indexed by their id.
 */
var domUs = {};

/**
 * Time between each refresh (in milliseconds)
 */
var refresh_time = 10000;
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
 * Helps us distribute the panels : ISOS feature
 */
var right = 0;
var left = 0;
var weight = 0;
var isos = 0;
var content;



Object.isEmpty = function(object)
{
	for (var prop in object)
	{
		if (object.hasOwnProperty(prop))
		{
			return false;
		}
	}
	return true;
};

function Dom0(id, address, cpus, freeram, totalram, ro)
{
	this.id = id;
	this.domUs = {};

	this._panel = new Xilinus.Widget(); // Panel associated to this Dom0.
	this.update(address, cpus, freeram, totalram, ro);

}
Dom0.prototype = {
	finalize: function ()
	{
		portal.remove(this._panel);
	},
	update: function (address, cpus, freeram, totalram, ro)
	{
		this.address = address;
		this.cpus = cpus;
		this.ro = ro;
		this.freeram = freeram;
		this.totalram = totalram;

		this._panel.setTitle(this.address).setContent(content_dom0(this));
		this._panel.updateHeight();
	},
	addDomU: function (domU)
	{
		this.domUs[domU.id] = domU;
		this._panel.setContent(content_dom0(this));
		this._panel.updateHeight();
	},
	isos: function ()
	{
		portal.add(this._panel, 0);
		var content = this._panel.getContent();
		var weight = content.getHeight();

		if (left <= right)
		{
			portal.add(this._panel, 0);
			left = weight+left;
		}
		else
		{
			portal.add(this._panel, 1);
			right = weight+right;
		}
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


function DomU(id, dom0, name, vcpus, state, ro)
{
	this.id = id;
	this.dom0 = null;
	this.window = null; // Window associated to this DomU.
	this.cap = 'N/A';
	this.d_min_ram = 'N/A';
	this.kernel = 'N/A';
	this.on_crash = 'N/A';
	this.on_reboot = 'N/A';
	this.on_shutdown = 'N/A';
	this.start_time = 'N/A';
	this.weight = 'N/A';
	this.d_max_ram = 'N/A';
	this.s_min_ram = 'N/A';
	this.s_max_ram = 'N/A';

	this.update(dom0, name, vcpus, state, ro);
}
DomU.prototype = {
	finalize: function ()
	{
		if (this.window !== null)
		{
			// Close the window
			this.window.close();
		}
		this.dom0.removeDomU(this.id);
	},
	update: function (dom0, name, vcpus, state, ro, cap, d_min_ram, kernel, on_crash, on_reboot,
		on_shutdown, start_time, weight, d_max_ram, s_min_ram, s_max_ram)
	{
		this.name = name;
		this.vcpus = vcpus;
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
			this.d_max_ram = d_max_ram;
			this.s_min_ram = s_min_ram;
			this.s_max_ram = s_max_ram;
		}

		if (dom0 !== this.dom0)
		{
			if (this.dom0 !== null)
			{
				this.dom0.removeDomU(this.id);
			}
			this.dom0 = dom0;
			this.dom0.addDomU(this);
		}

		if (this.window !== null)
		{
			this._refresh_window();
		}
	},
	open_window: function ()
	{
		var _this = this;
		this.window = new Window({
			className: 'alphacube',
			showProgress: true,
			showEffect:Effect.Appear,
			width: 500,
			height: 250,
			resizable: false,
			onClose: function ()
			{
				_this.window = null;
			}
		});
		this._refresh_window();
		this.window.show();
		//this.window.showCenter();
	},
	_refresh_window: function ()
	{
		var html_id = escape(this.id).replace(/\.|#|%/g, '_');
		var date = new Date(this.start_time * 1000);

		if (!this.ro)
		{
			if (this.state === 'Running')
			{
				var actions = ['pause', 'stop', 'poweroff'];
			}
			else if (this.state === 'Paused')
			{
				var actions = ['play', 'stop', 'poweroff'];
			}
			else if (this.state === 'Halted')
			{
				var actions = ['play', 'destroy'];
			}
			else if (this.state === 'Crashed')
			{
				var actions = ['poweroff'];
			}
		}
		var targets = find_possible_targets(this);

		// if a tab is currently set
		if ($('tabs_' + html_id) !== null)
		{
			// get the current tab instance
			var current_instance = Control.Tabs.findByTabId('overview_' + html_id);
			// save the current link
			var active_link = current_instance.activeLink;
			// and remove the current instance from the array of instances
			// done because instances are not destroyed when new html is set.
			Control.Tabs.instances = Control.Tabs.instances.without(current_instance);
		}
		// set windows title and html
		this.window.setTitle('<b id="title"></b> (' + this.dom0.address + ')');
		$('title').update(this.name);
		$('title').innerHTML;
		
		this.window.setHTMLContent('<div id="vm"><ul id="tabs_' + html_id + '" class="menuvm"></ul></div>');
		var staticContent = render_vm(this.id,html_id,this.state,this.kernel,this.vcpus,this.d_min_ram,date,actions,this.on_shutdown,this.on_reboot,this.on_crash,this.weight,this.cap,targets);
		// fill the content with correct data from render_vm
		$('vm').update(staticContent);
		$('vm').innerHTML;
		// Provides effects on different places
		render_live_vm(targets);

		// set tabs on the new html
		var tabs = new Control.Tabs('tabs_' + html_id);
		// and set the active tab to the saved one
		tabs.setActiveTab(active_link);
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
 * TODO: write doc.
 */
function find_possible_targets(domU)
{
	var targets = [];

	for (var dom0_id in dom0s)
	{
		var dom0 = dom0s[dom0_id];
		if (!dom0.ro && (dom0 !== domU.dom0))
		{
			targets.push(dom0_id);
		}
	}
	return targets;
}

/**
 * Sends a request to XO to change the current of state of a domU,
 * then display/refresh the domU's window.
 *
 * @param domU       The domU's identifier.
 * @param action     The action to do among (destroy, halt, pause, start).
 * @param parameters Optional parameters (useful for migration).
 */
function action_vm(domU_id, action, parameters)
{
	var dom0_id = domUs[domU_id].dom0.id;
	if (parameters === undefined)
	{
		parameters = {};
	}
	Object.extend(parameters, {'action': action, 'dom0': dom0_id, 'domU': domU_id});
	send_request('domU', parameters, {
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
		domU.window.toFront();
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
				dom0s[record.id] = new Dom0(record.id, record.address,
				record.cpus, record.freeram, record.totalram, record.ro);
			}
			else
			{
				dom0s[record.id].update(record.address, record.cpus,
				record.freeram, record.totalram, record.ro);
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

			if (isos === 0) // call Intelligent Space Occupation System
			{
				dom0s[record.id].isos();
			}
		}
		isos = 1; // ISOS is done once, it's enough

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
 * Initialize all listeners and Portal (to call when the Dom is loaded).
 */
function init()
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

	portal = new Xilinus.Portal('#main div', {removeEffect: Effect.SwitchOff});

	setTimeout(refresh, refresh_time);
}

/**
 * Initialize all listeners for static pages (users.php etc.)
 */
function init_static()
{
	draw_log_area();
}
