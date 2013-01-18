/* 
 Réorganise les div ayant pour class .layer pour leur attribuer un z-index
avec pour plus élevé celui de la div clické.

 @author Joseph Lemoine - lemoine.joseph@gmail.com
 @version 1
 */

/* Trigger */
$(document).ready( function ()
{
	$('.layer').live('click', function(){boxLayer($(this));});
});

function boxLayer(obj)
{
	/* Test au cas où on appel cette fonction manuellement */
	if(obj.hasClass("layer"))
	{
		var layer = 1;
		
		/* Génération ou régen des z-index */
		$(".layer").each(function()
		{
			/* Si on à pas déjà attribué un z-index */
			if($(this).css("z-index") == "auto")
			{
				/* On place l'objet au dessus des autres */
				$(this).css("z-index", layer);
			}
			layer++;
		});
		
		/* On enregistre la position de notre layer */
		var curLayer = obj.css("z-index");

		/* On diminue la hauteur des layers au dessus de 1 */
		$(".layer").each(function()
		{
			/* Si le layer est supérieur à celui de notre objet */
			if($(this).css("z-index") > curLayer)
			{
				/* On le dessend */
				$(this).css("z-index", $(this).css("z-index")-1);
			}
		});

		/* Notre objet prend le layer le plus haut */
		obj.css("z-index", layer);
	}

	$(".overlay").height($(document).height());
}