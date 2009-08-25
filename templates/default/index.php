<?php

include $ps->config->base_path.$ps->config->pathto_current_template."header.php";

if($ps->is_image()) { 
	//this is an 'image' page so include the 'image' template file
   include $ps->config->base_path.$ps->config->pathto_current_template."image.php";
} elseif($ps->is_album()) {
	//this is an 'album' page so include the 'album' template file
   include $ps->config->base_path.$ps->config->pathto_current_template."album.php";
} else {
	//this is a 'gallery' page so include the 'gallery' template file
   include $ps->config->base_path.$ps->config->pathto_current_template."gallery.php";
}

include $ps->config->base_path.$ps->config->pathto_current_template."footer.php";

?>
