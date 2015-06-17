<?php

// Operator autoloading
$eZTemplateOperatorArray = array();
$eZTemplateOperatorArray[] =
	array(
		'script' => 'extension/mugo_dam/classes/mugodamtemplateoperators.php',
		'class' => 'MugoDamTemplateOperators',
		'operator_names' => array( 'image_url' ),
);
