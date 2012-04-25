<div id="debug">
	<a href="javascript:;" name="debugbar" id="debug_control">Debug Bar</a>
	<div id="debug_infos">
<?php
$trace = debug_backtrace();
//var_dump($trace);
unset($trace[0],$trace[1],$trace[2]);
krsort($trace);

// Trace
echo "<div><strong>Trace :</strong>";
foreach ($trace as $appel)
{
	echo "<b>{$appel['file']}</b><br />";
	echo "<span>ligne {$appel['line']} :</span> {$appel['class']}{$appel['type']}{$appel['function']}<br />";

	if($appel['class'] == 'Controller')
		$options = $appel['object']->options;
		
	if($appel['class'] == 'view'){
		$data = $appel['object']->data;
		$templateData = $appel['object']->templateData;
	}
}
echo "</div>";

// Data
echo "<div><strong>Data :</strong>";
	var_dump($this->data);
echo "</div>";

// Templates data
echo "<div><strong>Templates data :</strong>";
	var_dump($this->templateData);
echo "</div>";

// COOKIES
echo "<div><strong>Cookies :</strong>";
	var_dump($_COOKIE);
echo "</div>";

// $_POST
echo "<div><strong>Collection POST :</strong>";
	var_dump($_POST);
echo "</div>";

// $_SESSION
echo "<div><strong>Collection SESSION :</strong>";
	var_dump($_SESSION);
echo "</div>";

// GET => URL
echo "<div><strong>URL :</strong>";
	echo $_GET['url'];
echo "</div>";

// Requête HTTP
echo "<div><strong>Requête HTTP :</strong>";
	var_dump($this->requete);
echo "</div>";

// Requêtes SQL


?>
	</div>
	
</div>

<script type="text/javascript">
$(document).ready(function(){
	$("a[name='debugbar']").bind("click",onClickDebugBar);
});

function onClickDebugBar(e){
	$("#debug_infos").slideToggle("slow");
}
</script>