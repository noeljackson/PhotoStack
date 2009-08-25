<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $ps->crumbline() ?></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo $ps->config->pathto_admin_template ?>main.css" />
<script src="<?php echo $ps->config->pathto_admin_template ?>javascripts/beh.js" type="text/javascript"></script>
<script src="<?php echo $ps->config->pathto_admin_template ?>javascripts/beh_rules.js" type="text/javascript"></script>
<script src="<?php echo $ps->config->pathto_admin_template ?>javascripts/prototype.js" type="text/javascript"></script>
<script src="<?php echo $ps->config->pathto_admin_template ?>javascripts/scriptaculous.js" type="text/javascript"></script>
<script src="<?php echo $ps->config->pathto_admin_template ?>javascripts/photostack.js" type="text/javascript"></script>
</head>

<body>


<?php if($ps->is_logged_in()) { ?>
<div id="wrap">
	<div id="header">
		<div class="sleeve">
		<h1><img src="<?php echo $ps->config->pathto_admin_template ?>images/pslogo.png" width="154" height="28" alt="PhotoStack"><small> (<a href="index.php">View Site »</a>)</small></h1>
		<ul id="menu">
			<li<?php if( $_REQUEST['action'] != 'settings'
			 		&& $_REQUEST['action'] != 'config'
					&& $_REQUEST['action'] != 'editprofile'
					&& $_REQUEST['action'] != 'manageusers' 
					&& $_REQUEST['action'] != 'edituser'
					&& $_REQUEST['action'] != 'suspenduser') echo ' class="selected"'; ?>>
				<a href="<?php echo $ps->format_admin_url("view"); ?>">galleries</a>
			</li>
			<?php if($ps->is_admin()) { ?>
			<li<?php if($_REQUEST['action'] == 'manageusers' 
					or $_REQUEST['action'] == 'editprofile' 
					or $_REQUEST['action'] == 'edituser' 
					or $_REQUEST['action'] == 'suspenduser') echo ' class="selected"'; ?>>
				<a href="<?php echo $ps->format_admin_url("manageusers"); ?>">users</a>
				</li>
			<?php } ?>
			<li<?php if($_REQUEST['action'] == 'config' 
					or $_REQUEST['action'] == 'settings') echo ' class="selected"'; ?>>
			<a href="<?php echo $ps->format_admin_url("settings"); ?>">settings</a>
			</li>
			<li>
			<a href="<?php echo $ps->format_admin_url("logout"); ?>">logout</a>
			</li>
		</ul>
		</div>
	</div>
<div id="content">	
			<small>Logged in as <?php echo $_SESSION["photostack_user"]->username ?>, <a href="<?php echo $ps->format_admin_url("editprofile") ?>">edit your profile</a>.</small>

	<?php  if($_REQUEST['action'] != 'settings'
		 		&& $_REQUEST['action'] != 'config'
				&& $_REQUEST['action'] != 'editprofile' 
				&& $_REQUEST['action'] != 'manageusers' 
				&& $_REQUEST['action'] != 'suspenduser' 
				&& $_REQUEST['action'] != 'edituser'
				or $_REQUEST['image']) {  ?>
			<ul id="second_menu">
			<?php if($ps->is_album() && count($ps->gallery->images) > 0) { ?>
			<li><a href="#" id="reorder">reorder images</a></li>
			<li><a href="#" id="reorder_done" style="display: none;">done reordering</a></li>
			<?php } ?>
			<?php if ($ps->gallery->id != '.') { ?>
			<li><a href="#" id="edit">edit this <?php echo ($ps->is_album() or $ps->is_image()) ? 'album' : 'gallery'; ?></a></li>
			<li><a href="#" id="done" style="display: none;">close editing window</a></li>
			<?php } ?>
			<li><a href="<?php echo $ps->format_admin_url("newgallery",$ps->gallery->id_encoded); ?>" id="newgallery">new  sub-<?php echo ($ps->is_album()) ? 'album' : 'gallery'; ?></a></li>

			<?php if ($ps->gallery->id != '.') { ?>
			<li><a id="delete" href="<?php echo $ps->format_admin_url("deletegallery",$ps->gallery->id_encoded, null, null, '&confirmed=true'); ?>">delete <?php echo ($ps->is_album()) ? 'album' : 'gallery'; ?></a></li>
			<?php } ?>

			<li><a href="<?php echo $ps->format_admin_url("upload",$ps->gallery->id_encoded); ?>" id="showupload">upload images here</a></li>
			<li><a href="#" id="cancelupload" style="display: none;">cancel upload</a></li>
			
			</ul>
	<?php  } ?>
			
	<?php if($_SESSION["photostack_user"]->userpass == '21232f297a57a5a743894a0e4a801fc3') { ?><div class="message error">Error: Your <a href="<?php echo $ps->format_admin_url("editprofile") ?>">password needs to be changed</a>.</div><?php } ?>
    <?php if(!is_writeable('./cache/')) {?><div class="message error">Error: Your <em>cache</em> dir is <strong>not</strong> writeable.</div><?php } ?>
    <?php if(!is_writeable('./galleries/')) {?><div class="message error">Error: Your <em>galleries</em> dir is <strong>not</strong> writeable.</div><?php } ?>
    <?php if(!is_writeable('./config.php')) {?><div class="message error">Error: Your <em>config.php</em> file is <strong>not</strong> writeable.</div><?php } ?>
    <?php if(!is_writeable('./config.users.php')) {?><div class="message error">Error: Your <em>config.users.php</em> file is <strong>not</strong> writeable.</div><?php } ?>
	<?php if($ps->is_album() &&!is_writeable('./galleries/'.$ps->gallery->id)) {?><div class="message error">Error: This album dir is <strong>not</strong> writeable. (<?php echo realpath('./galleries/'.$ps->gallery->id); ?>)</div><?php } ?>
<?php if(isset($adminMessage)): ?>
<div class="message notice">
  <?php 
    echo $adminMessage; 
  ?>
</div>
<?php endif; ?>
<?php } ?>