function detailFlash(id) {
		$('#listeFlash').fadeOut('fast',function(){
			saveFil = $('#listeFlash').html();
			contenu = "<strong>"+flash[id].titre+"</strong>";
			contenu += "<br />"+flash[id].dateFR+"<br />";
			contenu += "<p>"+flash[id].description+"</p>";
			contenu += "<a href='javascript:listeFlash();'>Retour à la liste</a>";
			$(this).fadeIn('fast').html(contenu);
		});
	}
function listeFlash() {
	$('#listeFlash').fadeOut('fast',function(){
		$(this).fadeIn('fast').html(saveFil);
	});
}