<script type="text/javascript">
		// <![CDATA[
		function MM_jumpMenu(targ,selObj,restore){ //v3.0
  		eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  		if (restore) selObj.selectedIndex=0;
		}
		// ]]>
	</script>

<div id="holder" style="width: 400px;">
	<ul class="images">
	<?php $ps->is_album = true; while($ps->loop()): ?>
	<li><?php echo $ps->image_thumbnail_linked($ps->image_date()); ?></li>
	<?php endwhile; $ps->rewind_loop(); ?>
	</ul>

	<p id="photo">
		<a href="<?php echo $ps->image_next_url(); ?>"><?php echo $ps->image(); ?></a>
	</p>
	
	<div class="navigation">
		<?php 
		// set the current gallery title
		$current = $ps->gallery_id(); ?>
		<select onchange="MM_jumpMenu('parent',this,0);">
			<option value="<?php echo $ps->format_url('.'); ?>">Main Gallery</option>
			<?php
			// rewind and select the top gallery
			$ps->rewind_loop(); 
			$ps->select_gallery('.');
			while($ps->loop()): ?>
				<option value="<?php echo $ps->gallery_url(); ?>"<?php if($current == $ps->gallery_id()) { echo ' selected="selected"';} ?>>
				<?php echo $ps->gallery_name(); ?>
				</option>
			<?php endwhile; ?>
		</select>
	</div>
</div>