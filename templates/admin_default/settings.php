<h2>settings</h2>

<h3>install info</h3>
<ul>
    <li>photostack version: <?php echo $ps->version; ?></li>
    <?php if(!is_writeable('./cache/')) {?><li><strong>Your cache dir is not writeable.</strong></li><?php } ?>
    <?php if(!is_writeable('./galleries/')) {?><li><strong>Your cache dir is not writeable.</strong></li><?php } ?>
</ul>

<h3>external template</h3>

<code>&lt;?php include '<?php echo realpath('.').'/external.php'; ?>'; ?&gt;</code>


<h3>cache control</h3>
<ul>
		<li><a href="#" id="generate_thumbs">generate thumbnails</a></li>
		<li><a href="#" id="purge_cache">purge cache</a></li>
</ul>

<h3>config options</h3>
<?php if(!is_writeable('config.php')) { ?><p class="message">Your <code>config.php</code> file is uneditable. Please make sure it is writeable by your webserver before using the control panel to make changes.</p><?php } ?>
<?php $configfile = parse_ini_file('config.php');?>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="config" />
<ul class="gray">
<li>
<h4>Basic</h4>
<ul>
<li><strong>Gallery Name</strong>: <input type="input" name="config[gallery_name]" id="gallery_name" value="<?php echo $configfile['gallery_name']; ?>" /></li>

<li><strong>Separator</strong>: <input type="input" name="config[separator]" id="separator" value="<?php echo $configfile['separator']; ?>" />

<li><strong>URL to Install</strong>: <input type="input" name="config[base_url]" id="base_url" value="<?php echo $configfile['base_url']; ?>" />

<p><small>leave blank if this is alright: <?php echo'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) ?></small></li>



<li><strong>Current Template</strong>:

    <select name="config[template]" id="template"><?php $dirs = $ps->get_listing($ps->config->base_path.$ps->config->pathto_templates,"dirs");

foreach($dirs->dirs as $key => $dir) {
    if($dir == $configfile['template']) $selected = ' selected="selected"'; else $selected = '';
    if($dir != 'admin_default' && $dir != 'rss') echo '<option value="'.$dir.'"'.$selected.'>'.$dir.'</option>';
}

?></select></li>

<li><strong>Thumbnail Software</strong>: <select name="config[thumbnail_software]" id="thumbnail_software">     <option value="gd2"<?php if($configfile['thumbnail_software'] == "gd2") echo ' selected="selected"'; ?>>GD v2.x </option>
     <option value="im"<?php if($configfile['thumbnail_software'] == "im") echo ' selected="selected"'; ?>>ImageMagick</option></select></li>

<li><strong>Gallery Sort Order</strong>: <select name="config[gallery_sort_order]" id="gallery_sort_order">
     <option value="p"<?php if($configfile['gallery_sort_order'] == "p") echo ' selected="selected"'; ?>>sort by directory name (ascending)</option>
     <option value="P"<?php if($configfile['gallery_sort_order'] == "P") echo ' selected="selected"'; ?>>sort by directory name (descending)</option>
     <option value="n"<?php if($configfile['gallery_sort_order'] == "n") echo ' selected="selected"'; ?>>sort by gallery name (ascending)</option>
     <option value="N"<?php if($configfile['gallery_sort_order'] == "N") echo ' selected="selected"'; ?>>sort by gallery name (descending)</option>
     <option value="i"<?php if($configfile['gallery_sort_order'] == "i") echo ' selected="selected"'; ?>>sort by gallery name (case insensitive, ascending)</option>
     <option value="I"<?php if($configfile['gallery_sort_order'] == "I") echo ' selected="selected"'; ?>>sort by gallery name (case insensitive, descending)</option>
     <option value="x"<?php if($configfile['gallery_sort_order'] == "x") echo ' selected="selected"'; ?>>do not sort</option></select></li>

<li><strong>Image Sort Order</strong>:
    <select name="config[image_sort_order]" id="image_sort_order">
    <option value="f"<?php if($configfile['image_sort_order'] == "f") echo ' selected="selected"'; ?>>sort by file name (ascending)</option>
    <option value="F"<?php if($configfile['image_sort_order'] == "F") echo ' selected="selected"'; ?>>sort by file name (descending)</option>
    <option value="l"<?php if($configfile['image_sort_order'] == "l") echo ' selected="selected"'; ?>>sort by file name (case insensitive, ascending)</option>
    <option value="L"<?php if($configfile['image_sort_order'] == "L") echo ' selected="selected"'; ?>>sort by file name (case insensitive, descending)</option>
    <option value="n"<?php if($configfile['image_sort_order'] == "n") echo ' selected="selected"'; ?>>sort by image name (ascending)</option>
    <option value="N"<?php if($configfile['image_sort_order'] == "N") echo ' selected="selected"'; ?>>sort by image name (descending)</option>
    <option value="i"<?php if($configfile['image_sort_order'] == "i") echo ' selected="selected"'; ?>>sort by image name (case insensitive, ascending)</option>
    <option value="I"<?php if($configfile['image_sort_order'] == "I") echo ' selected="selected"'; ?>>sort by image name (case insensitive, descending)</option>
    <option value="d"<?php if($configfile['image_sort_order'] == "d") echo ' selected="selected"'; ?>>sort by date (ascending)</option>
    <option value="D"<?php if($configfile['image_sort_order'] == "D") echo ' selected="selected"'; ?>>sort by date (descending)</option>
    <option value="x"<?php if($configfile['image_sort_order'] == "x") echo ' selected="selected"'; ?>>do not sort</option>
    </select>
</li>
<li><input type="submit" class="button" value="Save Changes" /></li>
</ul>
</li><li>

<h4>Functionality</h4>
<ul>
<li><strong>Use caching</strong>: <select name="config[cache]" id="cache"><option value="on"<?php if($configfile['cache'] == "on") echo ' selected="selected"'; ?>>On</option><option value="off"<?php if($configfile['cache'] == "off" or $configfile['cache'] == "") echo ' selected="selected"'; ?>>Off</option></select><p>For the cache.</p></li>

<li><strong>Use mod_rewrite</strong>: <select name="config[use_mod_rewrite]" id="use_mod_rewrite"><option value="on"<?php if($configfile['use_mod_rewrite'] == "on") echo ' selected="selected"'; ?>>On</option><option value="off"<?php if($configfile['use_mod_rewrite'] == "off" or $configfile['use_mod_rewrite'] == "") echo ' selected="selected"'; ?>>Off</option></select><p>Format generated URLs for use with Apache mod_rewrite. You need to enable <a href="http://httpd.apache.org/docs/2.0/mod/mod_rewrite.html">mod_rewrite</a>. (If your .htaccess file is not writeable by the webserver, you'll need to rename mod_rewrite.htaccess to .htaccess)</p></li>

<li><strong>Full Image Resize</strong>: <select name="config[full_image_resize]" id="full_image_resize"><option value="on"<?php if($configfile['full_image_resize'] == "on") echo ' selected="selected"'; ?>>On</option><option value="off"<?php if($configfile['full_image_resize'] == "off" or $configfile['full_image_resize'] == "") echo ' selected="selected"'; ?>>Off</option></select><p>Turn on to force all full-size images to be resized to the size specified in the current template's template.ini.</p></li>

<li><strong>Enable Clickable URLs</strong>: <select name="config[enable_clickable_urls]" id="enable_clickable_urls"><option value="on"<?php if($configfile['enable_clickable_urls'] == "on") echo ' selected="selected"'; ?>>On</option><option value="off"<?php if($configfile['enable_clickable_urls'] == "off" or $configfile['enable_clickable_urls'] == "") echo ' selected="selected"'; ?>>Off</option></select></li>

<li><strong>Remove JPEG Profile</strong>: <select name="config[remove_jpeg_profile]" id="remove_jpeg_profile"><option value="on"<?php if($configfile['remove_jpeg_profile'] == "on") echo ' selected="selected"'; ?>>On</option><option value="off"<?php if($configfile['remove_jpeg_profile'] == "off" or $configfile['remove_jpeg_profile'] == "") echo ' selected="selected"'; ?>>Off</option></select><p>Tells ImageMagick to remove any profile information from generated thumbnails. This has been known to cause some problems, hence it is turned Off by default.</p></li>

<li><strong>Progressive Thumbs</strong>: <select name="config[progressive_thumbs]" id="progressive_thumbs"><option value="on"<?php if($configfile['progressive_thumbs'] == "on") echo ' selected="selected"'; ?>>On</option><option value="off"<?php if($configfile['progressive_thumbs'] == "off" or $configfile['progressive_thumbs'] == "") echo ' selected="selected"'; ?>>Off</option></select><p>Turn On to create thumbnails as progressive JPEGs. Progressive JPEGs are displayed in alternating lines, then filled in on a second pass.</li>

<li><strong>Use PclZip or Unzip</strong>: <select name="config[unzip_method]" id="unzip_method"><option value="pclzip"<?php if($configfile['unzip_method'] == "pclzip") echo ' selected="selected"'; ?>>PclZip</option><option value="unzip"<?php if($configfile['unzip_method'] == "unzip" or $configfile['unzip_method'] == "") echo ' selected="selected"'; ?>>UnZip</option></select><p>Use PclZip (php-library) or UnZip (command line executable)</p></li>

<li><input type="submit" class="button" value="Save Changes" /></li>
</ul>
 </li><li>
<h4>Paths</h4>
<ul>
<li><strong>Path to Templates</strong>: <input type="input" name="config[pathto_templates]" id="pathto_templates" value="<?php echo $configfile['pathto_templates']; ?>" /><p>path to directory containing templates must be specified relative to install root</p></li>

<li><strong>Path to Cache</strong>: <input type="input" name="config[pathto_cache]" id="pathto_cache" value="<?php echo $configfile['pathto_cache']; ?>" /><p>path to writable cache directory must be specified relative to install root</p></li>

<li><strong>Path to Galleries</strong>: <input type="input" name="config[pathto_galleries]" id="pathto_galleries" value="<?php echo $configfile['pathto_galleries']; ?>" /><p>path to galleries directory must be specified relative to install root</p></li>

<li><strong>Path to Convert</strong>: <input type="input" name="config[pathto_convert]" id="pathto_convert" value="<?php echo $configfile['pathto_convert']; ?>" /><p>full path to ImageMagick convert utility *probably fine as is*</p></li>

<li><strong>Path to FFMpeg</strong>: <input type="input" name="config[pathto_ffmpeg]" id="pathto_unzip" value="<?php echo $configfile['pathto_ffmpeg']; ?>" /><p>full path to ffmpeg utility *probably fine as is*</p></li>
<li><input type="submit" class="button" value="Save Changes" /></li>

<li><strong>Path to Unzip</strong>: <input type="input" name="config[pathto_unzip]" id="pathto_unzip" value="<?php echo $configfile['pathto_unzip']; ?>" /><p>full path to unzip utility or equivalent *probably fine as is*</p></li>
<li><input type="submit" class="button" value="Save Changes" /></li>
</ul>
</li>
<?php /*
<li>

<h4>FTP</h4>
<ul>
<li><strong>Use FTP</strong>: <select name="config[use_ftp]"><option value="on"<?php if($configfile['use_ftp'] == "on") echo ' selected="selected"'; ?>>On</option><option value="off"<?php if($configfile['use_ftp'] == "off" or $configfile['use_ftp'] == "") echo ' selected="selected"'; ?>>Off</option></select></li>
<li><strong>Use Secure FTP</strong>: <select name="config[ftp_secure]"><option value="on"<?php if($configfile['ftp_secure'] == "on") echo ' selected="selected"'; ?>>On</option><option value="off"<?php if($configfile['ftp_secure'] == "off" or $configfile['ftp_secure'] == "") echo ' selected="selected"'; ?>>Off</option></select></li>
<li><strong>FTP Server Name</strong>: <input type="input" name="config[ftp_server]" value="<?php echo $configfile['ftp_server']; ?>" /></li>
<li><strong>FTP User Name</strong>: <input type="input" name="config[ftp_user]" value="<?php echo $configfile['ftp_user']; ?>" /></li>
<li><strong>FTP Password</strong>: <input type="password" name="config[ftp_pass]" value="<?php echo $configfile['ftp_pass']; ?>" /></li>
<li><strong>FTP directory mode</strong>: <input type="input" name="config[directory_mode]" value="<?php echo $configfile['directory_mode']; ?>" /></li>
<li><strong>FTP Path to Galleries</strong>: <input type="input" name="config[ftp_pathto_galleries]" value="<?php echo $configfile['ftp_pathto_galleries']; ?>" /></li>
<li><input type="submit" class="button" value="Save Changes" /></li>
</ul>
 </li>
*/ ?>

<li>
<h4>Advanced</h4>
<ul>

<li><strong>Uploaded File Permissions</strong>: <input type="input" name="config[chmod_value]" id="chmod_value" value="<?php echo $configfile['chmod_value']; ?>" /><p>The <strong>octal</strong> value to use when uploading or editing files - starts with a zero.</p></li>

<input type="hidden" name="config[io_handler]" id="io_handler" value="<?php echo $configfile['io_handler']; ?>" />

<li><strong>Upload Overwrite</strong> <select name="config[upload_overwrite]" id="upload_overwrite">
    <option value="0">do not overwrite, raise an error (default)</option>
    <option value="1" <?php echo(($configfile['upload_overwrite']==1)?'selected':'');?>>overwrite without prompting</option>
    <option value="2" <?php echo(($configfile['upload_overwrite']==2)?'selected':'');?>>attempt to generate a new unique name</option></select>
    <p>What to do when uploading an image or gallery that already exists.</p>
</li>


<li><strong>Admin Template Name</strong>: <input type="input" name="config[admin_template_name]" id="admin_template_name" value="<?php echo $configfile['admin_template_name']; ?>" /><p>The name of the admin template to use.</p>
</li>
<li><strong>RSS Template Name</strong>: <input type="input" name="config[rss_template_name]" id="rss_template_name" value="<?php echo $configfile['rss_template_name']; ?>" /><p>The name of the rss template to use.</p></li>

<li><strong>Thumbnail Quality</strong>: <input type="input" name="config[thumbnail_quality]" id="thumbnail_quality" value="<?php echo $configfile['thumbnail_quality']; ?>" /><p>The JPEG quality of generated thumbnails 100 is the highest quality 0 is the lowest.</p></li>

<li><strong>Index File URL</strong>: <input type="input" name="config[index_file_url]" id="index_file_url" value="<?php echo $configfile['index_file_url']; ?>" /><p>Filename of index file plus any query-string if a path is specified it must be absolute.</p></li>

<li><strong>Base Path</strong>: <input type="input" name="config[base_path]" id="base_path" value="<?php echo $configfile['base_path']; ?>" /><p>The file-system-view absolute or relative path to installation can usually be left blank.</p></li>

<li><strong>Recognized Extensions</strong>: <input type="input" name="config[recognised_extensions]" id="recognised_extensions" value="<?php echo $configfile['recognised_extensions']; ?>" /><p>A list of file extensions that will be recognised as images, separate with a vertical bar |</p></li>

<li><strong>Ignored Text</strong>: <input type="input" name="config[ignored_text]" id="ignored_text" value="<?php echo $configfile['ignored_text']; ?>" /><p>Any text that if contained in an image name will be ignored, separate with a vertical bar |</p></li>

<li><strong>Allowed Tags</strong>: <input type="input" name="config[allowed_tags]" id="allowed_tags" value="<?php echo $configfile['allowed_tags']; ?>" /><p>A list of HTML tags which will be allowed in multi-line database entries such as description and summary.</p></li>

<li><strong>Character Set</strong>: <input type="input" name="config[charset]" id="charset" value="<?php echo $configfile['charset']; ?>" /><p>UTF-8 is best.</p></li>
<li><input type="submit" class="button" value="Save Changes" /></li>
    </ul>
    </li>

</ul>

</form>