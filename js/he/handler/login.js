/**
 * Handler de controle du paneau de login
 */

$(document).ready(function(){
	$('#HelogButton').live('click', function(){
		$('#HeSuPanel').hide();
		$('#HeLogForm').slideToggle();
	});
});