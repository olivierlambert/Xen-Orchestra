var win = null;

function disp_vm(id,domN,vm) {
	var url1 = 'vm.php?vm=';
	var url2 = '&dom0=';
	var urlfinal = url1+id+url2+domN;
	win = new Window({
		title : vm,
		url : urlfinal,
		className:"alphacube",
		width : 450,
		height : 450
	});
	win.show();
}

function disp_migrate(id,domN) {
	var url1 = 'migrate.php?vm=';
	var url2 = '&dom0=';
	var urlfinal = url1+id+url2+domN;
	win = new Window(
	{
		title : "Live Migration",
		url : urlfinal,
		className:"alphacube",
		showProgress: true,
		width : 400,
		//height : 100
	}
	);
win.show();

}

function close_simple() {
	top.win.close(true);
}

function close_reload() {
	top.win.close(true);
	setTimeout(function() {top.location.reload(true);},100);
}

function call_cpu_buttons(cpus)
{
	var n = cpus.length;
	if (n == 0)
	{
		return '';
	}
	else
	{
		var result = '';
		for (var i=0;i<n;i++) {
			if (cpus[i]<25) {
				result = result+'<img border=0 title="'+cpus[i]+'" src="img/cgreen.png">';
			}
			else if (cpus[i]<50) {
				result = result+'<img border=0 title="'+cpus[i]+'" src="img/cyellow.png">';
			}
			else if (cpus[i]<75) {
				result = result+'<img border=0 title="'+cpus[i]+'" src="img/corange.png">';
			}
			else {
				result = result+'<img border=0 title="'+cpus[i]+'" src="img/cred.png">';
			}
		}
		return result;
	}
}
function display_vm(name,state,id,n)
{
	var url = 'display_domU.php?name='+name+'&state='+state+'&id='+id+'';
	var req = new Ajax.Request(url,
	{
		method:'get',
		onComplete: function(transport)
		{
			var result = transport.responseText;
			var json = result.evalJSON();
			win = new Window(
			{
				className:"alphacube",
				showProgress: true,
			});
			var date = new Date(json.date*1000);
			var html = '<div id="vm"><h3>Overview</h3></div>';
			html+='<p>State : '+json.state+'</p>';
			html+='<p>Date of creation : '+date+'</p>';
			html+='<p>VCPU number : '+json.vcpu_number+'</p>';

			win.setTitle(name);
			win.setHTMLContent(html);
			win.show();
			win.updateWidth();
			win.updateHeight();
		},
		onFailure: function()
		{
			alert('Something went wrong...');
		}
	}
	);
}
function content_dom0(domUs,number,id)
{
	var result = '';
	if (number<1)
	{
		return '<p>No DomU detected</p>';
	}
	else
	{
		var table_templ =
		{
			tabletop : '<table><th>Name</th><th>State</th><th>Load</th>',
			tablebottom : '</table>'
		};
		domUs.each(function (domU)
		{
			result+='<tr id="'+domU.name+'" onclick="display_vm(\''+domU.name+'\',\''+domU.state+'\',\''+id+'\')">';
			result+='<td>'+domU.name+'</td>';
			result+='<td>'+domU.state+'</td>';
			result+='<td>'+call_cpu_buttons(domU.cpu_use)+'</td>';
			result+='</tr>';
		});
		var templ = new Template('#{tabletop}'+result+'#{tablebottom}');
		return templ.evaluate(table_templ);
	}
}

function set_effects(previousdomUs,domUs)
{
	if (previousdomUs != null)
	{
		var n_prev_domUs = previousdomUs.size();
		var n_domUs = domUs.size();
		var not_finded = true;

		if (n_domUs > 0)
		{
			previousdomUs.each(function (previousdomU)
			{
				domUs.each(function (domU)
				{
					if (previousdomU.name === domU.name)
					{
						not_finded = false;
						if (previousdomU.state != domU.state)
						{
							Effect.Pulsate(domU.name, { pulses: 4, duration: 4 });
						}
					}
				});
				if (not_finded)
				{
					Effect.Pulsate(domU.name, { pulses: 4, duration: 4 });
				}
			});
		}
	}
}

function refresh_windows(windows,response,previousjson)
{
	var url = 'display_dom0.php';
	var req = new Ajax.PeriodicalUpdater('page',url,
	{
		method:'get',
		frequency: response,
		decay: 1.2,
		onSuccess: function(transport)
		{
			var result = transport.responseText;
			var json = result.evalJSON();
			var n = json.size();
			for (var i=0;i<n;i++)
			{
				var content = content_dom0(json[i].domUs,json[i].vm_number,json[i].id);
				windows[i].setContent(content);
				windows[i].updateHeight();
				if (previousjson != null)
				{
				set_effects(previousjson[i].domUs,json[i].domUs);
				}
			}
			previousjson = json;
		}
	});
}

document.observe('dom:loaded',function(e)
{
	var portal = new Xilinus.Portal("#main div");
	var url = 'refresh_time.php';
	var req = new Ajax.Request(url,
	{
		method:'get',
		onComplete: function(transport)
		{
			response = transport.responseText || "20";
		},
		onFailure: function()
		{
			alert('Something went wrong...');
		}
	}
	);
	var url = 'display_dom0.php';
	var req = new Ajax.Request(url,
	{
		method:'get',
		onComplete: function(transport)
		{
			var result = transport.responseText;
			var json = result.evalJSON();
			var weightleft = 0;
			var weightright = 0;
			var windows = new Array();
			json.each(function(item)
			{
				var content = content_dom0(item.domUs,item.vm_number,item.id);
				var window = new Xilinus.Widget().setTitle(item.name).setContent(content);
				if (weightleft <= weightright)
				{
					weightleft = weightleft+item.vm_number+1;
					portal.add(window, 0);
				}
				else
				{
					weighright = weightright+item.vm_number+1;
					portal.add(window,1);
				}
				windows.push(window);
			});
			setTimeout(function()
			{
				refresh_windows(windows,response,json);
			},response*1000);

		},
		onFailure: function()
		{
			alert('Something went wrong...');
		}
	}
	);
}
);
