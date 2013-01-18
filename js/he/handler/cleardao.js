/**
 * Fonctions d'affichage de diverses boites de dialogue pour clearDao
 */

function clearDaoIsDeleted(form, msg)
{
	form.after(msg);
	form.next().dialog({
		title: 'Commande de supression', 
		minHeight: 150, 
		minWidth: 350,
		modal: true,
		resizable: false,
		buttons: {
			Ok: function() {
				$( this ).dialog("close");
			}
		}
	});
	hideOverlay($('.overlay'));
}