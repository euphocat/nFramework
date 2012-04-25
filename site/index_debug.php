<?php
$debut = microtime();
include_once '../librairie/frontcontroller.php';
$ctrl = FrontController::getInstance();
$ctrl->debug = true ;
$ctrl->dispatch();
$fin = microtime();
?>
<p style="text-align: center;">Temps : <?php echo round($fin-$debut,5)?> sec.</p>