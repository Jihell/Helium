/* 
 Gestion des formulaire en ajax de façon automatique.

	COMMON USAGE :
-> Créer un formulaire et lui passer comme class ".ajaxForm"
-> Le fichier cible est celui spécifié dans l'attribut action
-> On peu spécifier une fonction à exécuter en cas de réussite en ajoutant à la 
balise form l'attribut callback="mafonctionjavascript();uneautrefonciton();"
-> L'envoi de formualire se fait via les triggers ayant pour class ".submit"

	DEPENDANCES : 
he/overlay.js
he/forcemd5forpwd.js
he/md5.js


 @author Joseph Lemoine - lemoine.joseph@gmail.com
 @version 1
 */

/* Trigger */
$(document).ready( function ()
{
//	$('form.ajaxForm').live('submit', function(){ajaxForm($(this));});
	$('form.ajaxForm .submit').live('click', function(){ajaxForm($(this).closest("form"));});
	
	$('.ajaxCall').live('click', function(){ajaxCall($(this));});
});

/* Envoi de formulaire par ajax */
function ajaxForm(form) {
	// TODO ajouter test formulaire
	
	openFakeOverlay();
	var d = form.serialize();
	
	$.ajax({
		type: form.attr('method'),
		url: form.attr("action"),
		data: d,
		success: function(msg) {
			if(msg == "false")
			{
				alert("ECHEC LORS DE L'ENVOI DU FORMULAIRE !");
			}
			if(form.attr("callback") != undefined) {
				eval(form.attr("callback"));
			}
		},
		error: function(request, status, error) {
			alert( "Error ! File "+form.attr("action")+" not found ! message : '"+request.statusText+"'");
		},
		complete: function()
		{
			hideFakeOverlay();
		}
	});
	/* Pour ne pas suivre le lien */
	return false;
}

/* Appel une page ajax et l'envoi dans le container spécifier dans les attributs :
 * - Class trigger : "ajaxCall"
 * - action="ajax/controleur_ajax_cible"
 * - return="id_ou_class_de_la_div_de_retour"
 * - callback="liste de fonctions à exécuter" */
function ajaxCall(obj)
{
	openFakeOverlay();
	
	$.ajax({
		type: "POST",
		url: obj.attr("action"),
		data: obj.attr("data"),
		success: function(msg) {
			if(msg != "false")
			{
				/* Ecriture du message de retour */
				if(obj.attr('return') != undefined) {
					$(obj.attr('return')).html(msg);
				}
				
				/* Appel du callback */
				if(obj.attr("callback") != undefined) {
					eval(obj.attr("callback"));
				}
			}
			else
			{
				alert("ECHEC LORS DE L'ENVOI DU FORMULAIRE !");
			}
			return false;
		},
		error: function() {
			alert( "Error ! File not found");
		},
		complete: function()
		{
			hideFakeOverlay();
		}
	});
	/* Pour ne pas suivre le lien */
	return false;
}