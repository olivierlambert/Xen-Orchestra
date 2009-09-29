function manualreload() {
	new Ajax.Updater('main', 'naked.php');
	//this.blur();
}

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
)
win.show();

}

function close_simple() {
	top.win.close(true);
}

function close_reload() {
	top.win.close(true);
	setTimeout(function() {top.location.reload(true);},100);
}

function testalert() {
	alert("TOTO");
}

function initPage(e) {
	$('btnReload').observe('click',manualreload);
	var url = 'refresh_time.php';
	var req = new Ajax.Request(url,
	{
		method:'get',
		onComplete: function(transport) {
			var response = transport.responseText || "20";
			new Ajax.PeriodicalUpdater('main', 'naked.php',
				{frequency: response, decay: 1.2});
		},
		onFailure: function() {
			alert('Something went wrong...')
		}
	});
} // initPage : add observers, refresh time etc.


function call_cpu_buttons(cpus) {
	/*var t = new Template {
		row :'<img border=0 title=#{action1} src=#{icon1}><img border=0 title=#{action2} src=#{icon2}><img border=0 title="Edit this DomU" onclick="disp_vm('+i+',\''.$this->id.'\',\''.$title_window.'\')" src="img/action.png">'
	};*/
	var n = cpus.length;

	if (n == 0) {
		return '';
	}
	else {
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
// todo : switch state pour bon lien (cf display_frame_vm) etc. bien parti !!!
function content_dom0(domUs,number) {
var n = domUs.length;
var result = '';
var table_templ = {
	tabletop : '<table><tr><th>Name</th><th>State</th><th>Load</th><th>More...</th></tr>',
	tablebottom : '</table>'
	};
	
	for (var i=0;i<n;i++) {
		result = result+'<tr>';
		result = result+'<td>'+domUs[i].name+'</td>';
		result = result+'<td>'+domUs[i].state+'</td>';
		result = result+'<td>'+call_cpu_buttons(domUs[i].cpu_use)+'</td>';
		result = result+'<td><img border=0 title="Edit this DomU" onclick="disp_vm('+i+','+i+','+domUs[i].name+')" src="img/action.png"></td>';
		result = result+'<tr>';
	}
var templ = new Template('#{tabletop}'+result+'#{tablebottom}');
return templ.evaluate(table_templ);
//result = result+'</table>';
//return result;
}

function display_dom0(dom0,row,id,number) {
	var url = 'display_dom0.php';

	var req = new Ajax.Request(url,
	{
		method:'post',
		postBody:'id='+id,
		onComplete: function(transport) {
		var json = transport.responseText.evalJSON();
		var content = content_dom0(json.domUs,json.vm_number);
		portal.add(new Xilinus.Widget().setTitle(json.name).setContent(content), row);
		},
		onFailure: function() {
			alert('Something went wrong...')
		}
	});
}

document.observe('dom:loaded', initPage);
