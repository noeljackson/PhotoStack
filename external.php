<?php
/*
So you want to include photostack on your site?
Put this somewhere in your code: <?php include $_SERVER['DOCUMENT_ROOT'].'/path/to/install/external.php'; ?>
*/

# include main class
require_once "includes/photostack.class.php";
# create a wrapper
$ps = new photostack();
# Open the ?template OR open the current_template
include $ps->config->base_path.$ps->config->pathto_templates.'/external/'.'index.php';
?>