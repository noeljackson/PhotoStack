<div style="position: relative; z-index: 1;">

<div id="editgallery" style="display: none;">
<?php if(!is_writeable('./galleries/'.$ps->gallery->id)) {?><div class="message error">Error: This album dir is <strong>not</strong> writeable. Trying to edit any of these values will result in failure.</div><?php } ?>
<h2>edit gallery</h2>

<?php 
	if($ps->gallery->filename == "__none__")
		echo '<img id="thumbnail" src="'.$ps->config->pathto_admin_template.'images/blank.gif" style="display: none;" />';
	else
      echo $ps->gallery_thumbnail('id="thumbnail"', 50, 50); 
  ?>
<?php if ($ps->gallery->id != '.') { ?>
	<p><strong>Gallery Name: </strong><span id="name"><?php echo (!empty($ps->gallery->name)) ? $ps->gallery->name : 'no gallery name set'; ?></span></p>
	<script language="JavaScript">
	 new Ajax.InPlaceEditor('name', '<?php echo $_SERVER['PHP_SELF'] ?>?gallery=<?php echo $ps->gallery->id_entities ?>&action=savegallery&field=name');
	</script>
<?php } ?>
<?php if ($ps->gallery->id != '.') { ?>
	<p><strong>Rename Folder:</strong><input name="folder" width = "10" id="folder" value="<?php echo substr(strrchr($ps->gallery->id, "/"), 1); ?>"/><input type="submit" name="submit" value="ok" onclick="var value = $('folder').value; window.location='<?php echo $_SERVER['PHP_SELF'] ?>?gallery=<?php echo $ps->gallery->id_entities ?>&action=savegallery&field=folder&value=' + value"/></p>
<?php  } ?>
	<p><strong>Date: </strong><span id="date"><?php 
	if($ps->gallery->date != null) {
	echo date('F j, Y', (!empty($ps->gallery->date)) ? $ps->gallery->date : 'no date set'); 
	} else {
		echo "No Date Set";
	}
	?></span></p>
	<script language="JavaScript">
	 new Ajax.InPlaceEditor('date', '<?php echo $_SERVER['PHP_SELF'] ?>?gallery=<?php echo $ps->gallery->id_entities ?>&action=savegallery&field=date');
	</script>

	<p><strong>Description: </strong><span id="desc"><?php if($ps->gallery_desc() != null) { echo $ps->format_stripped($ps->gallery_desc()); } else { echo 'no description set'; } ?></span></p>
	<script language="JavaScript">
	 new Ajax.InPlaceEditor('desc', '<?php echo $_SERVER['PHP_SELF'] ?>?gallery=<?php echo $ps->gallery->id_entities ?>&action=savegallery&field=desc',{rows:2,cols:40});
	</script>

	<p><strong>Long Description: </strong><span id="long_desc"><?php if($ps->gallery_desc_long() != null) { echo $ps->format_stripped($ps->gallery_desc_long()); } else { echo 'no long description set'; } ?></span></p>
	<script language="JavaScript">
	 new Ajax.InPlaceEditor('long_desc', '<?php echo $_SERVER['PHP_SELF'] ?>?gallery=<?php echo $ps->gallery->id_entities ?>&action=savegallery&field=long_desc',{rows:4,cols:40});
	</script>
	
	<script language="JavaScript">

	function setThumb()
	{
	var url = '<?php echo $_SERVER['PHP_SELF'] ?>';
	var value = $('thumbnail_value').value;
	var pars = 'action=savegallery&field=thumbnail&width=50&height=50&value=' + encodeURIComponent(value) + '&gallery=<?php echo $ps->gallery->id_entities; ?>';

	var myAjax = new Ajax.Updater(
		{success: $('thumbnail')}, 
	   url, 
	   {method: 'get', parameters: pars, onFailure: reportError, onSuccess: onInsertion});
	}

	function onInsertion(string) {
		if(string.responseText == '__none__' || string.responseText == '__random__' ) {
			var thumbnail = new Effect.Fade('thumbnail');
		} else {
			var thumbnail = new Effect.Appear('thumbnail');
			$('thumbnail').src = string.responseText;
		}
	}

	function reportError(request)
	{
	alert('Sorry. There was an error.');
	}

	</script>
<?php if ($ps->gallery->id != '.') { ?>
	  <p><strong>Thumbnail:</strong><select name="value" id="thumbnail_value" onchange="setThumb()">
	    <option value="__none__">Not Set</option>
	    <?php 
	      foreach($ps->gallery->images as $img)
	        if($ps->gallery->filename == $img->filename or $img->filename == $ps->gallery->images[0]->filename)
	          echo "<option value=\"".htmlspecialchars($img->filename)."\" selected=\"true\">$img->name ($img->filename)</option>\n  ";
	        else
	          echo "<option value=\"".htmlspecialchars($img->filename)."\">$img->name ($img->filename)</option>\n  ";
	    ?>
	  </select></p>
<?php } ?>
	<input type="button" onclick="Effect.Fade('editgallery');Effect.Appear('edit', {duration: 0.3, from: 0.3, to: 1.0 }); Element.hide('done');"  value="close editing window" />
	<br style="clear: all;"/>
</div>
</div>