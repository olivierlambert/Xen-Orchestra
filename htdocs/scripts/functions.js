var data;
var refresh_time;
var portal;
var windows = [];
var win = null;

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

function display_vm(name, id, dom0, idwindow)
{
	// TO DO : prepare refresh if value or action is maded (give id of windows then refresh it)
	// see idwindow for that
	var url = 'display_domU.php?name='+name+'&id='+id+'';
	var req = new Ajax.Request(url,
	{
		method:'get',
		onComplete: function(transport)
		{
			var result = transport.responseText;
			var json = result.evalJSON();
			if (idwindow == null)
			{
				win = new Window(
				{
					className:"alphacube",
					showProgress: true,
				});
				var idwindow = win.getId();
			}
			var date = new Date(json.date*1000);
			var date2 = new Date(json.lastupdate*1000);
			var html = '<div id="vm">';
			html+='<ul id="tabs_example_one" class="menuvm" >';
			html+='<li><a href="#overview"><b><img src="img/information.png" alt=""/>Overview</b></a></li>';
			html+='<li><a href="#cpu"><b><img src="img/cpu.png" alt=""/>CPU</a></b></li>';
			html+='<li><a href="#ram"><b><img src="img/ram.png" alt=""/>RAM</a></b></li>';
			html+='<li><a href="#network"><b><img src="img/world.png" alt=""/>Network</a></b></li>';
			html+='<li><a href="#storage"><b><img src="img/database_gear.png" alt=""/>Storage</a></b></li>';
			html+='<li><a href="#misc"><b><img src="img/wrench.png" alt=""/>Misc</a></b></li>';
			html+= '</ul><div id="overview" class="text">';
			
			html+='<br/><p><b>State: </b>'+json.state+'</p>';
			html+='<p><b>System: </b>'+json.kernel+'</p>';
			html+='<p><b>CPU installed: </b>'+json.vcpu_number+'</p>';
			html+='<p><b>RAM installed: </b>'+json.d_min_ram/(1024*1024)+' MB</p>';
			html+='<p><b>Date of creation: </b>'+date+'</p>';

			html+='<p><b>Actions: </b><br/><img src="img/pause.png" alt=""/><img src="img/stop.png" alt=""/><img src="img/destroy.png" alt=""/></p>';
			html+='<p><b>Live migration to: </b>xenb1 &nbsp xena2</p></div>';

			
			html+='<div id="cpu">';
			html+='<br/><p><b>VCPU use:</b> '+json.vcpu_use+'</p>';
			html+='<p><b>VCPU at startup:</b> '+json.vcpus_at_startup+'</p>';
			html+='<p><b>VCPU number:</b> '+json.vcpu_number+'</p>';
			html+='<p><b>Cap:</b> '+json.cap+'</p>';
			html+='<p><b>Weight:</b> '+json.weight+'</p></div>';

			
			html+='<div id="ram">';
			html+='<br/><b><p>RAM:</b> '+json.d_min_ram/(1024*1024)+' MB</p></div>';
			
			html+='<div id="network"></div>';
			
			html+='<div id="storage"></div>';
			
			html+='<div id="misc">';
			html+='<br/><b><p>On shutdown:</b> '+json.actions_after_shutdown+'</p>';
			html+='<b><p>On reboot:</b> '+json.actions_after_reboot+'</p>';
			html+='<b><p>On crash:</b> '+json.actions_after_crash+'</p></div>';
			
			html+='</div>';
			win.setTitle('<b>'+name+'</b> ('+dom0+')');
			win.setHTMLContent(html);
			new Control.Tabs('tabs_example_one');
			win.show();
			win.setSize(500,203);
		},
		onFailure: function()
		{
			alert('Something went wrong...');
		}
	}
	);
}

// New functions

function refresh()
{
	new Ajax.Request('display_dom0.php', {
		method: 'get',
		onComplete: function(transport)
		{
			update_portal(transport.responseText.evalJSON());
			setTimeout(refresh, refresh_time);
		},
		onFailure: function()
		{
			alert('Something went wrong...');
		}
	});
}

function update_portal()
{
	while (w = windows.pop())
	{
		portal.remove(w);
	}
	
	var weightleft = 0;
	var weightright = 0;
	data.each(function (dom0)
	{
		var w = new Xilinus.Widget()
			.setTitle(dom0.name)
			.setContent(content_dom0(dom0));
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
		windows.push(w);
	});
}

function content_dom0(dom0)
{
	if (dom0.vm_number === 0)
	{
		return '<p>No DomU detected</p>';
	}
	
	var result = '<table><tr><th>Name</th><th>State</th><th>Load</th></tr>';
	dom0.domUs.each(function (domU)
	{
		  
		result += '<tr id="' + domU.name
			+ '" onclick="display_vm(\'' + domU.name + '\',\'' + dom0.id
			+ '\',\'' + dom0.name +'\')"><td>' + domU.name + '</td><td>' + domU.state
			+ '</td><td>' + html_cpu_meters(domU.cpu_use)
			+ '</td></tr>';
	});
	return result + '</table>';
}

document.observe('dom:loaded', function ()
{


	portal = new Xilinus.Portal("#main div");
	update_portal();

	setTimeout(refresh, refresh_time);

});
