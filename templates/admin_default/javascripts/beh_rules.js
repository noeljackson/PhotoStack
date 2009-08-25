var rules = {
	'#reorder' : function(element) {
		element.onclick = function() {
			reorder_create(); disablelinks(); Effect.Appear('reorder_done', {duration: 0.1}); Element.hide(this);
		}
	},
	'#reorder_done' : function(element) {
		element.onclick = function() {
			update_order(); reorder_destroy(); enablelinks();  Effect.Appear('reorder', {duration: 0.1}); Element.hide(this);
		}
	},
	'#edit' : function(element) {
		element.onclick = function() {
			Effect.Appear('editgallery'); Effect.Appear('done', {duration: 0.1}); Element.hide(this);
		}
	},
	'#done' : function(element) {
		element.onclick = function() {
			Effect.Fade('editgallery'); Effect.Appear('edit', {duration: 0.1}); Element.hide(this);
		}
	},
	'#newgallery' : function(element) {
		element.onclick = function() {
			new_gal_name = prompt('Name of New Gallery','');
			if(new_gal_name) window.location= element.href + '&newgallery=' + encodeURIComponent(new_gal_name);
			return false;
		}
	},
	'#delete' : function(element) {
		element.onclick = function() {
			if(confirm('Are you sure you want to delete this gallery and its contents? This is irreversible.')) 
			window.location = element.href; return false;
		}
	},
	'#cancelupload' : function(element) {
		element.onclick = function() {
			Effect.Fade('upload'); Element.hide('cancelupload'); Effect.Appear('showupload', {duration: 0.3, from: 0.3, to: 1.0 });
		}	
	},
	'#generate_thumbs' : function(element) {
		element.onclick = function() {
			if(confirm('Are you sure you want to generate thumbnails for all files? (It may take a minute or two, so please, do be patient.)')) 
			window.location= element.href; return false;
		}
	},
	'#purge_cache' : function(element) {
		element.onclick = function() {
			if(confirm('Are you sure you want to delete all cached items?')) window.location='<?php echo $ps->format_admin_url("purgecache"); ?>'; return false;	
		}
	}
};

Behaviour.register(rules);
