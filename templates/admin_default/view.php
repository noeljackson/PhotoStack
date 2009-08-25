<?php 
  if($ps->is_image()) {
    include $ps->config->pathto_admin_template."image.php";
  } elseif($ps->is_album()) {
    include $ps->config->pathto_admin_template."album.php";
  } else {
    include $ps->config->pathto_admin_template."gallery.php";
  }
 ?>