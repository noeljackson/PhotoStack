<small>You are editing album:</small>
<h2><?php echo $ps->gallery_name(); ?></h2>
<small><?php echo $ps->image_count(); ?> images<?php if ($ps->gallery->id != '.') { ?> : <?php echo $ps->crumb_links() ?> &nbsp;<?php } ?> <?php echo $ps->gallery_prev_link('&#8592; Prev'); ?>
<?php echo $ps->gallery_parent_link('Parent Gallery &#9650;'); ?>
<?php echo $ps->gallery_next_link('Next &#8594;'); ?></small>

<?php include $ps->config->base_path.$ps->config->pathto_admin_template.'edit_top.php'; ?>

<div id="reorder_msg" class="message alert" style="display: none;">Reordering...</div>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" id="bulk" name="bulk">
<input type="hidden" name="action" value="bulk" />
<input type="hidden" name="gallery" value="<?php echo htmlspecialchars($ps->gallery->id) ?>" />
<ul id="album_list">
<?php for($ps->index = $ps->startat; $ps->index < $ps->selected_images_count()+$ps->startat; $ps->index++): ?>
		<li id ="img_<?php echo $ps->index; ?>">
		<?php echo $ps->image_thumbnail_linked('') ?>
		<div class="actions">
				<input name="images[]" id="<?php $ps->gallery->images[$ps->index]->filename; ?>" type="checkbox" value="<?php echo $ps->gallery->images[$ps->index]->filename; ?>" />
		</div>
		</li>
<?php endfor; ?>
</ul>

<script type="text/javascript" language="javascript">
	function reorder_create() {
		Sortable.create('album_list',{tag:'li',overlap:true,constraint:false, hoverclass: 'drophover'})
		new Effect.Appear('reorder_msg');
		$('reorder_msg').innerHTML = '<a href="#" onclick="update_order(); reorder_destroy(); enablelinks();  Effect.Appear(\'reorder\', {duration: 0.1});">Click here to apply your reordering changes.</a>';
	}
	function reorder_destroy() {
		Sortable.destroy('album_list');
	}
  	function update_order()
	{
 		new Effect.Appear('reorder_msg');
    	var options = {
    		method : 'get',
			parameters : 'action=updategallery&' + Sortable.serialize('album_list') + '&gallery=<?php echo $ps->gallery->id_entities; ?>'
		};

    	new Ajax.Request('organize.php', options);
		new Effect.Fade('reorder_msg');
	}
</script>

<?php if($ps->index > 0) { ?>
<br style="clear: both;" />
<p style="margin: 0px; padding: 0px;">
<input name="allbox" type="checkbox" value="Check All" onclick="CheckAll(document.bulk);" /> Select/Unselect All<br />
<input type="hidden" id="function" name="function" />
<input type="submit" class="button"  value="Delete Images" onclick="document.getElementById('function').value = 'delete'; return confirm('Are you sure you want to delete these images?');"/>
<input type="submit" class="button"  value="Rotate Images 90&deg; Right" onclick="document.getElementById('function').value = 'rotateright'; return confirm('Are you sure you want to rotate these images?');"/>
<input type="submit" class="button"  value="Rotate Images 90&deg; Left" onclick="document.getElementById('function').value = 'rotateleft'; return confirm('Are you sure you want to rotate these images?');"/>
</p>
<?php  } ?>

</form>