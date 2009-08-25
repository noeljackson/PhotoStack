<?php 
echo $ps->image->index + 1; 
echo ' of '.$ps->image_count(); ?>

<p class="nav" >
<?php echo $ps->image_prev_link("&#8592; Prev",'class="prev" accesskey="p"'); ?> 
<?php echo $ps->image_parent_link("Up A Level",'class="up" accesskey="u"'); ?> 
<?php echo $ps->image_next_link('Next &#8594;','class="next" accesskey="n"'); ?> 
</p>
  
<h2 class="hide"><?php echo $ps->gallery_name(); ?></h2>
<h3 class="hide"><?php echo $ps->image_name(); ?></h3>

<p id="image" style="width:<?php echo $ps->image_width(); ?>px;">
    <?php echo $ps->image('id="theimage"') ?> 
</p>

<?php echo $ps->image_desc('','<p class="metadata">Description: ','</p>'); ?>
<?php echo $ps->image_desc_long('','<p class="metadata">Long Description: ','</p>'); ?>
