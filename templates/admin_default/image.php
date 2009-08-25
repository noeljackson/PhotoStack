<?php include $ps->config->base_path.$ps->config->pathto_admin_template.'/edit_top.php'; ?>

<small>You are editing image:</small>
	<h2><?php echo $ps->image_name(); ?></h2>
<small><?php echo $ps->crumb_links() ?></small>
<div id="image" class="image_image">
<?php echo $ps->image() ?>
</div>

<div class="image_form">
	<p><?php echo $ps->image_preview_thumbs(6); ?></p>
	<p><a href="#" onclick="if(confirm('Are you sure you want to delete this image?')) window.location='<?php echo $ps->format_admin_url("deleteimage",$ps->gallery->id_encoded,rawurlencode($ps->image->filename)); ?>&amp;confirmed=true'; return false;"><img src="<?php echo $ps->config->pathto_admin_template ?>/images/del.gif" alt="Delete Image" title="Delete Image" /></a> | Actual Dimensions: <?php echo $ps->image_real_width(); ?> &times; <?php echo $ps->image_real_height(); ?></p>

	<p><?php echo $ps->image_prev_link(); ?>
	<?php echo $ps->image_parent_link(); ?>
	<?php echo $ps->image_next_link(); ?></p>
	
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="saveimage" />
<input type="hidden" name="gallery" value="<?php echo htmlspecialchars($ps->gallery->id) ?>" />
<input type="hidden" name="image" value="<?php echo htmlspecialchars($ps->image->filename) ?>" />
<input type="hidden" name="sort" value="<?php echo htmlspecialchars($ps->image->sort) ?>" />
<p>Image <em>filename</em> <br /><input type="text" name="image_filename" value="<?php echo $ps->image->filename ?>" size="35" /></p>
<p>Image name <br /><input type="text" name="image_name" value="<?php echo $ps->image->name ?>" size="35" /></p>
<p>Date <br /><input type="text" name="date" value="<?php echo $ps->image_date(); ?>" size="35" /></p>
<p>Description <br /><textarea name="desc" cols="38" rows="2"><?php echo str_replace("<br />","\n",$ps->image->desc) ?></textarea></p>
<p>Long Description <br /><textarea name="long_desc" cols="38" rows="8"><?php echo str_replace("<br />","\n",$ps->image->long_desc) ?></textarea></p>
<p><input type="submit" class="button" value="Save Changes" /></p>
</form>

<p><input type="submit" class="button" value="Delete Image" onclick="if(confirm('Are you sure you want to delete this image?')) window.location='<?php echo $ps->format_admin_url("deleteimage",$ps->gallery->id_encoded,rawurlencode($ps->image->filename)); ?>&amp;confirmed=true'; return false;"/></p>
<p><input type="submit" class="button" value="Rotate Image 90&deg; Right" onclick="if(confirm('Are you sure you want to rotate this image 90&deg; right?')) window.location='<?php echo $ps->format_admin_url("rotateimage",$ps->gallery->id_encoded,rawurlencode($ps->image->filename)); ?>&amp;degree=270'; return false;"/></p>
<p><input type="submit" class="button" value="Rotate Image 90&deg; Left" onclick="if(confirm('Are you sure you want to rotate this image 90&deg; left?')) window.location='<?php echo $ps->format_admin_url("rotateimage",$ps->gallery->id_encoded,rawurlencode($ps->image->filename)); ?>&amp;degree=90'; return false;"/></p>

</div>