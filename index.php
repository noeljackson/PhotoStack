<?php
# include main class
require_once "includes/photostack.class.php";
# create a wrapper
$ps = new photostack();

#Start Cache
$ps->cache_start();

#include the template.
include $ps->template();
	
# End Cache
$ps->cache_end();
?>