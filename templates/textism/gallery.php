<div id="holder">
<h1><?php echo $ps->crumb_links(); ?></h1>
	<ul class="galleries">
		<?php while($ps->loop()): ?>
		<li>
			<p>
				<?php echo $ps->gallery_thumbnail_linked(); ?>
				<a href="<?php echo $ps->gallery_url(); ?>"><?php echo $ps->gallery_name(); ?><small>&nbsp;&nbsp;<?php echo $ps->gallery_date();?></small></a>
			</p>
		</li>
		<?php endwhile; ?> 
	</ul>
</div>