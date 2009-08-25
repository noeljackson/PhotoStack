<p class="nav">
<?php echo $ps->gallery_showing(); ?>
<?php echo $ps->gallery_prev_link('&#8592; Prev'); ?>
<?php echo $ps->gallery_parent_link('Up'); ?>
<?php echo $ps->gallery_next_link('Next &#8594;'); ?>
</p>

<ul class="list">
	<?php while($ps->loop()): ?>
	<li>
		<span>
			<a href="<?php echo $ps->image_url(); ?>" rel="lightbox[set]" title="<?php echo $ps->image_name(); ?> : <?php echo $ps->image_date('','','','j M Y'); ?>&lt;br /&gt;<?php echo $ps->image_desc(); ?>">
			<?php echo $ps->image_thumbnail_image(); ?>
			</a>
		</span>
	</li>
	<?php endwhile; ?>
</ul>