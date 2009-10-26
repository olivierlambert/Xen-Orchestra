/**
 * Object which contains all dom0s which, theirselves contain their
 * domUs.
 */
var dom0s = {};

/**
 * TODO: write doc.
 */
var refresh_time;

/**
 * TODO: write doc.
 */
var portal;

/**
 * TODO: write doc.
 */
var dom0s_panels = [];

/**
 * TODO: write doc.
 */
var domUs_windows = {};

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
 * TODO: write doc.
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
		if (cpus[i] < 25)
		{
			result += '<img border=0 title="'+cpus[i]+'" src="img/cgreen.png">';
		}
		else if (cpus[i] < 50)
		{
			result += '<img border=0 title="'+cpus[i]+'" src="img/cyellow.png">';
		}
		else if (cpus[i] < 75)
		{
			result += '<img border=0 title="'+cpus[i]+'" src="img/corange.png">';
		}
		else
		{
			result += '<img border=0 title="'+cpus[i]+'" src="img/cred.png">';
		}
	}
	return result;
}

/**
 * TODO: write doc.
 */
function domU_window(dom0_id, domU_id)
{
	if (domUs_windows[domU_id] !== undefined)
	{
		// The window already exists, maybe we should put it on the
		// foreground.
		return;
	}

	domUs_windows[domU_id] = new Window({
		className: 'alphacube',
		showProgress: true,
		onClose: function ()
		{
			delete domUs_windows[domU_id];
		}
	});
	var domU = dom0s[dom0_id].domUs[domU_id];
	
	var date = new Date(domU.date * 1000);
	var html = '<div id="vm">';
	html+='<ul id="tabs_example_one" class="menuvm" >';
	html+='<li><a href="#overview"><b><img src="img/information.png" alt=""/>Overview</b></a></li>';
	html+='<li><a href="#cpu"><b><img src="img/cpu.png" alt=""/>CPU</a></b></li>';
	html+='<li><a href="#ram"><b><img src="img/ram.png" alt=""/>RAM</a></b></li>';
	html+='<li><a href="#network"><b><img src="img/world.png" alt=""/>Network</a></b></li>';
	html+='<li><a href="#storage"><b><img src="img/database_gear.png" alt=""/>Storage</a></b></li>';
	html+='<li><a href="#misc"><b><img src="img/wrench.png" alt=""/>Misc</a></b></li>';
	html+= '</ul><div id="overview" class="text">';
	
	html+='<br/><p><b>State: </b>'+domU.state+'</p>';
	html+='<p><b>System: </b>'+domU.kernel+'</p>';
	html+='<p><b>CPU installed: </b>'+domU.vcpu_number+'</p>';
	html+='<p><b>RAM installed: </b>'+domU.d_min_ram/(1024*1024)+' MB</p>';
	html+='<p><b>Date of creation: </b>' + date + '</p>';
	
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
		html += '<img src="img/' + actions[i]
			+ '.png" alt="" onclick="action_vm (\'' 
			+ dom0_id + '\', \'', + domU.name + '\', '
			+ '\'' + actions[i] + '\')" />';
	}
	html+='</p>';
	
	html+='<p><b>Live migration to: </b>xenb1 &nbsp xena2</p></div>';

	html+='<div id="cpu">';
	html+='<br/><p><b>VCPU use:</b> '+domU.vcpu_use+'</p>';
	html+='<p><b>VCPU at startup:</b> '+domU.vcpus_at_startup+'</p>';
	html+='<p><b>VCPU number:</b> '+domU.vcpu_number+'</p>';
	html+='<p><b>Cap:</b> '+domU.cap+'</p>';
	html+='<p><b>Weight:</b> '+domU.weight+'</p></div>';

	html+='<div id="ram">';
	html+='<br/><b><p>RAM:</b> '+domU.d_min_ram/(1024*1024)+' MB</p></div>';
	
	html+='<div id="network"></div>';
	
	html+='<div id="storage"></div>';
	
	html+='<div id="misc">';
	html+='<br/><b><p>On shutdown:</b> '+domU.actions_after_shutdown+'</p>';
	html+='<b><p>On reboot:</b> '+domU.actions_after_reboot+'</p>';
	html+='<b><p>On crash:</b> '+domU.actions_after_crash+'</p></div>';
	
	html+='</div>';
	
	domUs_windows[domU_id].setTitle('<b>'+domU.name+'</b> (' + dom0s[dom0_id].name + ')');
	domUs_windows[domU_id].setHTMLContent(html);
	new Control.Tabs('tabs_example_one');
	domUs_windows[domU_id].show();
	domUs_windows[domU_id].setSize(500, 203);
}

/**
 * Sends a request to XO to change the current of state of a domU,
 * then display/refresh the domU's window.
 * 
 * @param dom0   The identifier of the dom0 the domU belongs to.
 * @param domU   The domU's identifier.
 * @param action The action to do among (destroy, halt, pause, start).
 */
/*function action_vm(dom0_id, domU_id, action)
{
	var url = 'display_domU.php?dom0=' + dom0_id + '&domU=' + domU_id
		+ '&action=' + action;
	new Ajax.Request(url, {
		method: 'get',
		onComplete: function (transport)
		{
			var
}*/

/**
 * Gets information about a domU and display (or refresh) the domU's
 * window.
 * 
 * @param dom0_id The identifier of the dom0 the domU belongs to.
 * @param domU_id The domU's identifier.
 */
function display_vm(dom0_id, domU_id)
{
	// TO DO : prepare refresh if value or action is maded (give id of windows then refresh it)
	// see idwindow for that
	new Ajax.Request('display_domU.php?domU='+domU_id+'&dom0='+dom0_id, {
		method: 'get',
		onComplete: function (transport)
		{
			register_info(transport.responseText.evalJSON());
			domU_window (dom0_id, domU_id);
		},
		onFailure: function ()
		{
			alert('Something went wrong...');
		}
	}
	);
}

// New functions

/**
 * TODO: write doc.
 */
function refresh()
{
	new Ajax.Request('display_dom0.php', {
		method: 'get',
		onComplete: function(transport)
		{
			register_info(transport.responseText.evalJSON());
			setTimeout(refresh, refresh_time);
		},
		onFailure: function()
		{
			alert('Something went wrong...');
		}
	});
}

/**
 * TODO: write doc.
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
 * TODO: write doc.
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

/**
 * TODO: write doc.
 */
function register_info(info)
{
	if (info.exhaustive)
	{
		dom0s = {};
	}

	if (info.dom0s !== undefined)
	{
		Object.extendRecursively(dom0s, info.dom0s);
		if (portal !== undefined)
		{
			update_portal();
		}
	}
}

/**
 * TODO: write doc.
 */
document.observe('dom:loaded', function () {
	portal = new Xilinus.Portal("#main div");
	update_portal();

	setTimeout(refresh, refresh_time);
});
