<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo $ps->page_title(); ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $ps->current_template_url(); ?>main.css" />

<?php echo $ps->nav_links(); ?>
<link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo $ps->gallery_rss_url(); ?>" />

</head>

<body>
<div id="topbar">
<h1><?php echo $ps->crumb_links(); ?></h1>
</div>

