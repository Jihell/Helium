/**
 * Scripts de controle du paneau d'administration
 */

$(document).ready(function(){
	$('#HeSuButton').live('click', function(){
		$('#HeLogForm').hide();
		$('#HeSuPanel').slideToggle();
	});

	$('#HeSuTrace').live('click', function(){
		$("#HeCallstack").fadeToggle('fast');
	});
	
	$('#HeCallStackClose').live('click', function(){
		$("#HeCallstack").fadeToggle('fast');
	});
	
	$('#HeSuQuit').live('click', function(){
		ajaxForm($(this).children('form'));
	})
	
	$('#HeSuMD5').live('click', function(){
		openOverlay('<input type="text" value="" /><input type="button" value="Convertir" class="convert_to_md5" />');
	});
	
	$('.convert_to_md5').live('click', function(){
		var text_val = $(this).prev('input').val();
		$(this).after('<p>'+text_val+' : '+$.md5(text_val)+'</p>');
		$(this).prev('input').val('');
	});
});