$(function() {
	if ($.isFunction(jsToolBar)) {
		var tbUser = new jsToolBar(document.getElementById('construction_message'));
		tbUser.draw('xhtml');
	}
});