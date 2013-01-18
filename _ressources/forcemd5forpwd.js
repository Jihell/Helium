/*
 * Force la convertion des champs de type password en md5 avant la transmission
 */

$(document).ready(function()
{
	$('form:not(.ajaxForm)').each(function(){
		$(this).attr('OnSubmit', 'changePWDtoMD5($(this));');
	});
});

function changePWDtoMD5(form)
{
	form.getChildrens('input[type="password"]').each(function(){
		var md5 = $.md5($(this).value());
		$(this).value(md5);
	});
	return false;
}