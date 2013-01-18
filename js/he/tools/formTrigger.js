/* 
 * Gestion de triggers des formulaires
 */

$(document).ready(function(){
	$('form:not(.ajaxForm)').live('submit', function(){
		alert('submit ok');
		return true;
	});
});