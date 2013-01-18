/*
 * Affiche ou masque les différentes catégories de trace
 */

$(document).ready(function(){
	$('.HeCallstackCat li').live('click', function(){
		/* On masque toute les traces */
		$('.HeCallStackSequence').hide();
		
		/* On affiche la nouvelle */
		var div = $(this).attr('rel');
		$('#'+div).show();
	})
});

function replaceTrace(html)
{
	$('#HeCallstack').remove();
	$('#HeSuBar').after(html);
	$('#HeCallstack').show();
}