<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>NicoFramework</title>
<link href='/css/general.css' rel='stylesheet' type='text/css' />
<link href='/css/debug.css' rel='stylesheet' type='text/css' />

<script type='text/javascript' src='/scripts/<?php echo JQUERY?>.js'></script>
</head>
<body>
<!--[[notification]]-->
<div id="main">
	<!--[[body]]-->
	<hr />
	<div id="footer">
		Contact: nicolas.baptiste [at] gmail.com
	</div>
</div>

<?php if($debug) include('_debug.php'); ?>

<script type="text/javascript">
$(document).ready(function(){
	$("#notification a").live("click",onClickFermerNotification);

	if($.trim($("#notification").html()) != ""){
		$("#notification").append("<a href='javascript:;'>Fermer les notifications</a>");
		$("#notification").delay(6000).fadeOut(2000);
	}
});

function onClickFermerNotification(e){
	$("#notification").hide();
}
</script>
</body>
</html>
