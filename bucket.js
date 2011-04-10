$(document).ready(function() {
	//find the correct asset path for the plugin
	var assetPath = $('script[src*="ui.datepicker.js"]').attr('src').replace('/ui.datepicker.js', '');
	//add date picker css
	$('head').append('<link rel="stylesheet" href="'+assetPath+'/jquery-ui-1.7.3.custom.css" type="text/css" />');

	$('#bucket-start').datepicker();
	$('#bucket-end').datepicker();
});
