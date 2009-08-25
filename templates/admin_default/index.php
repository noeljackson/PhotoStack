<?php

/**
 * Default singapore admin template.
 * 
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version 1.0
 */

//include header file
include $ps->config->base_path.$ps->config->pathto_admin_template."header.php";
//include selected file
include $ps->config->base_path.$ps->config->pathto_admin_template.$includeFile.".php";
//include footer file
include $ps->config->base_path.$ps->config->pathto_admin_template."footer.php";
?>
