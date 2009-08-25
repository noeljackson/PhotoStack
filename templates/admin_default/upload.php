	<h2>upload images</h2>
<form action="<?php echo $ps->format_admin_url("uploadsend", $ps->gallery->id_encoded); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
<p><input type="file" name="some_name" value="" id="some_name" /><input type="submit" value="Upload" /></p>
</form>
	<?php if($ps->gallery_has_sub()) echo "<p class=\"message notice\">This image will not be visible because this gallery is not an album: it contains child galleries.</p>"; ?>
<script type="text/javascript" charset="utf-8">
	function getApplet() {
		return document.jumpLoaderApplet;
	}
	function getViewConfig() {
		if( getApplet().getViewConfig ){
			return getApplet().getViewConfig();
		} else {
			document.write('<p class="message error">Make sure you have Java enabled.</p>');
		}
	}
	
	function getMainView() {
		return getApplet().getMainView();
	}
	function getFileTreeView() {
		return getMainView().getFileTreeView();
	}
	function getFileListView() {
		return getMainView().getFileListView();
	}
	function getUploadView() {
		return getMainView().getUploadView();
	}
</script>

	<applet name="jumpLoaderApplet"
			code="jmaster.jumploader.app.JumpLoaderApplet.class"
			archive="<?php echo $ps->config->pathto_admin_template ?>jumploader_z.jar"
			width="900"
			height="500"
			mayscript>
		<param name="uc_uploadUrl" value="<?php echo $ps->format_admin_url("uploadsend", $ps->gallery->id_encoded); ?>"/>
	</applet>

<script type="text/javascript" charset="utf-8">
if( getViewConfig() ){
	getViewConfig().setMainViewFileTreeViewVisible(false);
	getViewConfig().setMainViewFileListViewVisible(false);
	getMainView().updateView();
}
</script>
