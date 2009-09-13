function manualreload() {
	new Ajax.Updater('main', 'naked.php');
	//this.blur();
}

function autoreload(sec) {
	new Ajax.PeriodicalUpdater('main', 'naked.php', {frequency: sec, decay: 1.2});
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
			autoreload(response);
		},
		onFailure: function() {
			alert('Something went wrong...')
		}
	});
} // initPage : add observers, refresh time etc.

document.observe('dom:loaded', initPage);
