<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo $ps->page_title(); ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $ps->current_template_url(); ?>main.css" />

<?php echo $ps->nav_links(); ?>
<link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo $ps->gallery_rss_url(); ?>" />

<script type="text/javascript" src="<?php echo $ps->current_template_url(); ?>js/prototype.js"></script>
<script type="text/javascript" src="<?php echo $ps->current_template_url(); ?>js/scriptaculous.js?load=effects"></script>
<script type="text/javascript" src="<?php echo $ps->current_template_url(); ?>js/lightbox.js"></script>

<style type="text/css" media="screen">
/* <![CDATA[ */
	#lightbox{
	position: absolute;
	top: 40px;
	left: 0;
	width: 100%;
	z-index: 100;
	text-align: center;
	line-height: 0;
	}

#lightbox a img{ border: none; }

#outerImageContainer{
	position: relative;
	background-color: #fff;
	width: 250px;
	height: 250px;
	margin: 0 auto;
	}

#imageContainer{
	padding: 10px;
	}

#loading{
	position: absolute;
	top: 40%;
	left: 0%;
	height: 25%;
	width: 100%;
	text-align: center;
	line-height: 0;
	}
#hoverNav{
	position: absolute;
	top: 0;
	left: 0;
	height: 100%;
	width: 100%;
	z-index: 10;
	}
#imageContainer>#hoverNav{ left: 0;}
#hoverNav a{ outline: none;}

#prevLink, #nextLink{
	width: 49%;
	height: 100%;
	background: transparent url(<?php echo $ps->current_template_url(); ?>images/blank.jpg) no-repeat; /* Trick IE into showing hover */
	display: block;
	}
#prevLink { left: 0; float: left;}
#nextLink { right: 0; float: right;}
#prevLink:hover, #prevLink:visited:hover { background: url(<?php echo $ps->current_template_url(); ?>images/prevlabel.gif) left 15% no-repeat; }
#nextLink:hover, #nextLink:visited:hover { background: url(<?php echo $ps->current_template_url(); ?>images/nextlabel.gif) right 15% no-repeat; }


#imageDataContainer{
	font: 10px Verdana, Helvetica, sans-serif;
	background-color: #fff;
	margin: 0 auto;
	line-height: 1.4em;
	}

#imageData{
	padding:0 10px;
	}
#imageData #imageDetails{ width: 70%; float: left; text-align: left; }	
#imageData #caption{ font-weight: bold;	}
#imageData #numberDisplay{ display: block; clear: left; padding-bottom: 1.0em;	}			
#imageData #bottomNavClose{ width: 66px; float: right;  padding-bottom: 0.7em;	}	
		
#overlay{
	position: absolute;
	top: 0;
	left: 0;
	z-index: 90;
	width: 100%;
	height: 500px;
	background-color: #000;
	filter:alpha(opacity=60);
	-moz-opacity: 0.6;
	opacity: 0.6;
	}
	

.clearfix:after {
	content: "."; 
	display: block; 
	height: 0; 
	clear: both; 
	visibility: hidden;
	}

* html>body .clearfix {
	display: inline-block; 
	width: 100%;
	}

* html .clearfix {
	/* Hides from IE-mac \*/
	height: 1%;
	/* End hide from IE-mac */
	}	

/* ]]> */
</style>


</head>

<body>
<div id="topbar">
<h1><?php echo $ps->crumb_links(); ?></h1>
</div>

