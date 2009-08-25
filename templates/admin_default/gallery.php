<small>You are editing gallery:</small>
<h2><?php echo $ps->gallery_name(); ?></h2>
<small><?php echo $ps->gallery_count(); ?> galleries<?php if ($ps->gallery->id != '.') { ?> : <?php echo $ps->crumb_links() ?> &nbsp;<?php } ?>
<?php echo $ps->gallery_parent_link('Parent Gallery &#9650;'); ?>
</small>

<br />

<?php include $ps->config->base_path.$ps->config->pathto_admin_template.'edit_top.php'; ?>

<ul id="gallery_list">
<?php for($ps->index = $ps->startat; $ps->index < $ps->selected_gallery_count()+$ps->startat; $ps->index++): ?> 

<li>
	<a href="<?php echo $ps->format_admin_url("view",$ps->gallery_id_encoded()); ?>"><?php echo $ps->gallery_thumbnail();?><h2><?php echo $ps->gallery_name() ?><span> (<?php echo $ps->gallery_contents() ?>)</span></h2></a>

</li>
    <?php endfor; ?>
</ul>
<!--
	<div class="actions">
        <?php
echo '<a href="#" onclick="if(confirm(\'Are you sure you want to delete this gallery and it\\\'s contents? This is irreversible.\'))  window.location=\''.$ps->format_admin_url("deletegallery",$ps->gallery_id_encoded(), null, null, '&confirmed=true').'\'; return false;"><img src="'.$ps->config->pathto_admin_template.'images/del.gif" alt="Delete Gallery" title="Delete Gallery" /></a>';
        ?>
		</div>-->