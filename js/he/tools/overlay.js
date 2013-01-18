/* 
 Affiche les overlay.
Le fake overlay sert à afficher un curseur d'attente durant une action ajax
Attention lors de l'utilisation du fake overlay, penser à le détruire en cas
d'échec !

 DEPENDANCE : 
he/layer.js

 @author Joseph Lemoine - lemoine.joseph@gmail.com
 @version 1
 */

/* Trigger de fermeture des overlays */
$(document).ready( function () {
    $('div.overlay').live('click', function(event) {
		/* Test si la propagation de l'event est bien sur la cible et non un
		 * descendant */
		if(event.target == this)
		{
			hideOverlay($(this));
		}
	});
});

/* Création d'un overlay */
function openOverlay(content)
{
	$("footer").after("<div class='overlay layer'><div class='overlay_box'>"+content+"</div></div>", function(){
		$(".overlay").fadeIn("fast");
		boxLayer($("footer").next(".overlay"));
	});
}
/* Fermeture d'un overlay */
function hideOverlay(overlay) 
{
	overlay.fadeOut("fast", function(){$(this).remove();});
}
/* Création d'un overlay */
function openFakeOverlay() {
	$("footer").after("<div class='fakeOverlay'></div>");
	$(".fakeOverlay").css("top", window.pageYOffset);
}
/* Fermeture d'un overlay */
function hideFakeOverlay() {
	$(".fakeOverlay").remove();
}