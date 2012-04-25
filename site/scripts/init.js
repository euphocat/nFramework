//A placer après jQuery
$(document).ready(function(){
	if($('#notification').length == 0)
		$('body').append('<p id="notification" style="display:none;"></p>');
});

function notif(texte){
	texte = "<img src='/icones/ok.png' alt='erreur' style='vertical-align:middle; margin-right:10px;' />"+texte;
	$('#notification').html(texte).css({'display':'block','background':'green','color':'white'});
	$("#notification").oneTime(1000, function() {
	     $(this).fadeOut(2000);
		});	
}
function erreur(texte) {
	texte = "<img src='/icones/erreur.png' alt='erreur' style='vertical-align:middle; margin-right:10px;' />"+texte;
	$('#notification').html(texte).css({'display':'block','background':'red','color':'white'});
	$("#notification").oneTime(1000, function() {
	     $(this).fadeOut(2000);
		});	
}
function info(texte) {
	texte = "<img src='/icones/info.png' alt='info' style='vertical-align:middle; margin-right:10px;' />"+texte;
	$('#notification').html(texte).css({'display':'block','background':'#ebe9ed','color':'black'});
	$("#notification").oneTime(1000, function() {
	     $(this).fadeOut(2000);
		});	
}