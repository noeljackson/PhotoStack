<?php header('Content-type: application/xml'); echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<rss version="0.92">
		<channel>
		<title><?php echo $ps->gallery_name(); ?></title>
		<link><?php echo $ps->config->base_url; ?></link>
		<description><?php echo $ps->gallery_name(); ?> Photo Feed</description>
		<lastBuildDate>Fri, 13 Apr 2001 19:23:02 GMT</lastBuildDate>
		<docs>http://backend.userland.com/rss092</docs>		
<?php if($ps->is_album()) { ?>
	    <?php for($ps->index = $ps->startat; $ps->index < $ps->selected_images_count()+$ps->startat; $ps->index++): ?>
		<item>
		<title><?php echo $ps->image_name(); ?></title>
		<description>
			<![CDATA[ <?php echo $ps->image_thumbnail_linked(); ?> ]]>
		</description>
		</item>
		<?php endfor; ?>
<?php } elseif($ps->is_gallery()) { ?>
<?php for($ps->index = $ps->startat; $ps->index < $ps->selected_gallery_count()+$ps->startat; $ps->index++): ?> 
		<item>
		<title><?php echo $ps->gallery_name(); ?></title>
		<description>
		<![CDATA[ <p><strong><a href="<?php echo $ps->gallery_url() ?>"><?php echo $ps->gallery_name(); ?></a></strong></p>
		<p><?php echo $ps->gallery_thumbnail_linked(); ?></p> ]]>
		</description>
		</item>
	<?php endfor; ?> 
<?php } ?>

			</channel>
		</rss>
