/* 
 Gestion des infobulles
 @author Joseph Lemoine - lemoine.joseph@gmail.com
 @version 1
 */

/* Paramètres */
var infoLateral = false; // Place les infobulles à gauche de l'objet, ou en bas

/* Trigger */
$(document).ready( function () {
    $(".infoTrigger").live({
		mouseenter: function() {showInfoBulle($(this));},
		mouseleave: function() {closeInfoBulle($(this));}
	});
});

/* Affiche une infobulle */
function showInfoBulle(box) {
	/* Fermeture des autres infobulles */
	$(".infoBulle").fadeOut("fast", function(){
		$(this).remove();
	});
	lockInfo = false;
	
	/* Ajout de l'infobulle */
	box.after(
		"<div class='infoBulle layer'>"
		+"</div>"
	);
	box.next("div.infoBulle").html(box.attr("info"));
	boxLayer(box.next("div.infoBulle"));
	
	var posiTop		= 0;
	var posiLeft	= 0;
	if(infoLateral)
	{
		/* Gestion de sa position en latérale */
		posiTop = $(document).scrollTop() + box.position().top - box.next("div.infoBulle").height() / 2 + box.height() / 2;
		posiLeft = box.position().left + box.width();

		if(box.offset().left + box.width()+box.next("div.infoBulle").width()+20 >= $(window).width()){
			posiLeft = box.position().left - box.next("div.infoBulle").width() - 10;
		}
	}
	else
	{
		/* Gestion de sa position en verticale */
		posiTop = $(document).scrollTop() + box.position().top - box.next("div.infoBulle").height() - 15;
		posiLeft = box.position().left - 10;
		
		if(box.offset().top - box.next("div.infoBulle").height() - 15 < 0){
			posiTop = box.position().top + box.next("div.infoBulle").height();
		}
	}
	
	/* On applique les positions */
	box.next("div.infoBulle").offset({top: posiTop, left: posiLeft});
	
	/* Affichage */
	box.next("div.infoBulle").fadeIn("fast");
}

/* Fait disparaitre l'infobulle */
function closeInfoBulle(box){
	/* On ferme l'infobulle que si on à pas cliquer sur le trigger */
	if(!lockInfo) {
		box.next(".infoBulle").fadeOut("fast", function(){
			$(this).remove();
		});
	}
}