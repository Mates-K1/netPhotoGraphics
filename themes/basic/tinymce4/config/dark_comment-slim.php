<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * Comment form plugin default light configuration
 */
$MCEcss = FULLWEBPATH . '/' . THEMEFOLDER . '/' . basename(dirname(dirname(dirname(__FILE__)))) . '/tinymce4/config/dark_content.css';
$MCEskin = "tundora";
$MCEselector = "textarea.textarea_inputbox";


$MCEplugins = "advlist autolink lists link image charmap print preview anchor " .
				"searchreplace visualblocks code " .
				"insertdatetime media contextmenu paste";
$MCEtoolbars[1] = "bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | code";
$MCEstatusbar = false;
$MCEmenubar = false;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/config.js.php');
