<p class="nav">
<?php echo $ps->gallery_showing(); ?>
<?php echo $ps->gallery_prev_link('&#8592; Prev'); ?>
<?php echo $ps->gallery_parent_link('Up'); ?>
<?php echo $ps->gallery_next_link('Next &#8594;'); ?>
</p>


<ul class="list">

<?php while($ps->loop()): ?>
	<li>
        <p class="hide"><a href="<?php echo $ps->gallery_url() ?>"><?php echo $ps->gallery_name(); ?></a></p>
        <span><?php echo $ps->gallery_thumbnail_linked($ps->gallery_name()); ?></span>
        <p class="hide"><?php echo $ps->gallery_desc(); ?></p>
        <p class="hide">[<?php echo $ps->gallery_contents(); ?>]</p>
	</li>
<?php endwhile; ?> 
</ul>

