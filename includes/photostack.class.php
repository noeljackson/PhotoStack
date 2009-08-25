<?php
//define constants for regular expressions
define('FORMAT_REGEXP_PROTOCOLURL', '(?:http://|https://|ftp://|mailto:)(?:[a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,4}(?::[0-9]+)?(?:/[^ \n\r\"\'<]+)?');
define('FORMAT_REGEXP_WWWURL',      'www\.(?:[a-zA-Z0-9\-]+\.)*[a-zA-Z]{2,4}(?:/[^ \n\r\"\'<]+)?');
define('FORMAT_REGEXP_EMAILURL',    '(?:[\w][\w\.\-]+)+@(?:[\w\-]+\.)+[a-zA-Z]{2,4}');

class photostack
{
var $cachefile;
var $index;
var $first_time;
var $version = "3.0b12152007";
  function photostack($basePath = "")
  {
	// Make sure we are running at least PHP 4.1.0 Stolen from Punbb.
	if (intval(str_replace('.', '', phpversion())) < 410)
		exit('
			You are running PHP version '.PHP_VERSION.'.
			PhotoStack requires at least PHP 4.1.0 to run properly.
			You must upgrade your PHP installation before you can continue.
		');

	// Disable error reporting for uninitialized variables
	#error_reporting(E_ERROR | E_WARNING | E_PARSE);
	@error_reporting(0);

	// Turn off PHP time limit
	@set_time_limit(0);

    //import class definitions
    require_once $basePath."includes/gallery.class.php";
    require_once $basePath."includes/config.class.php"; //loads io.class.php
    require_once $basePath."includes/image.class.php";
    require_once $basePath."includes/user.class.php";
    require_once $basePath."includes/exif.php";

    //remove slashes
    if(get_magic_quotes_gpc()) $_REQUEST = array_map(array("photostack","arraystripslashes"), $_REQUEST);

    //load config from root directory
    $this->config = new configuration($basePath."config.php");

    //set current gallery to root if not specified in url
    $_REQUEST[$this->config->url_gallery] =
		( isset($_REQUEST[$this->config->url_gallery]) &&
		  file_exists(realpath($this->config->base_path.$this->config->pathto_galleries.$_REQUEST[$this->config->url_gallery]))
		) ? $_REQUEST[$this->config->url_gallery] : ".";
	$gallery_name = $_REQUEST[$this->config->url_gallery];

    //load config from gallery ini file (gallery.ini) if present
 	$this->config->load_config($this->config->base_path.$this->config->pathto_galleries.$gallery_name."/gallery.ini");

    //set current template
    $this->config->pathto_current_template = $this->config->pathto_templates.$this->config->template.'/';

    //load template ini
    $this->config->load_config($this->config->base_path.$this->config->pathto_current_template."template.ini");

	// sets path to admin template
    $this->config->pathto_admin_template = $this->config->pathto_templates.$this->config->admin_template_name."/";
	$this->config->pathto_rss_template = $this->config->pathto_templates.$this->config->rss_template_name."/";

    //include IO handler class and create instance
    require_once $this->config->base_path."includes/io_".$this->config->io_handler.".class.php";
    $ioClassName = "IO_".$this->config->io_handler;
    $this->io = new $ioClassName($this->config);

	// set the cachefile name
    if($this->config->cache == "on")$this->cache_filename();

    //load gallery and image info if there isn't a cachefile

	if(!file_exists($this->cachefile) or $this->config->cache == "off") $this->select_gallery($gallery_name);
}

function template() {
	//is template in templates dir?
	$tplPath = realpath($this->config->pathto_templates);
	if(substr(realpath($this->config->pathto_templates.$_REQUEST['template']), 0, strlen($tplPath)) != $tplPath){
		return $this->config->pathto_current_template."index.php";
	}

	//simple wrapper to select the correct template
	return $this->config->base_path.
	((isset($_REQUEST['template']) && $_REQUEST['template'] != '') ?
	$this->config->pathto_templates.$_REQUEST['template'].'/'."index.php"
	: $this->config->pathto_current_template."index.php");
}

function cache_filename() {
	@$this->cachefile = $this->config->base_path.'cache'.'/php-cache-'.md5($_SERVER['SCRIPT_NAME'].$_SERVER['REQUEST_URI'].$_REQUEST[$this->config->url_startat].$_REQUEST[$this->config->url_gallery].$_REQUEST[$this->config->url_image].$_REQUEST[$this->config->url_action]);
}

function cache_start() {
	if($this->config->cache == "on") :
	$filesToCheck = array(
		realpath($this->config->pathto_galleries.$_REQUEST[$this->config->url_gallery].'/'),
		realpath($this->config->pathto_galleries.$_REQUEST[$this->config->url_gallery].'/metadata.csv'),
		realpath($this->config->base_path.$this->config->pathto_current_template),
		realpath($this->config->base_path.$this->config->pathto_current_template.'gallery.php'),
		realpath($this->config->base_path.$this->config->pathto_current_template.'album.php'),
		realpath($this->config->base_path.$this->config->pathto_current_template.'index.php'),
		realpath($this->config->base_path.$this->config->pathto_current_template.'footer.php'),
		realpath($this->config->base_path.$this->config->pathto_current_template.'header.php'),
		realpath($this->config->base_path.$this->config->pathto_current_template.'/template.ini'),
		$this->config->base_path.'includes/photostack.class.php',
		$this->config->base_path."index.php",
		$this->config->base_path.'config.php',
		$this->config->base_path.'config.users.php'
	);

	$create = 'no';
	foreach($filesToCheck as $key => $value) {
		#check to make sure it exists before checking filemtime on $key
		if(file_exists($value)) {
			# If the modified time of any of $files is newer (read: greater) than the cached page then flag it to make a new one.
			if (!file_exists($this->cachefile) or filemtime($value) > filemtime($this->cachefile)) {
				if(file_exists($this->cachefile)) $this->select_gallery($_REQUEST[$this->config->url_gallery]);
				$create = 'yes';
			}
		}
	}

	# if there is no need to create a new cache file and the cachefile exists, just read from that.
	if ($create != 'yes' && file_exists($this->cachefile)) {
	#	echo 'reading from cache';
	 	readfile($this->cachefile);
	 	exit;
	}
	#otherwise start grabbing the output.
	ob_start();
	endif;
}

function cache_end() {
	if($this->config->cache == "on") :
	# spill the beans
	$data = ob_get_contents();
	ob_end_flush();
	if($fp = fopen($this->cachefile, 'w')) {
		fwrite($fp, $data);fclose($fp);clearstatcache();
	}
	endif;
}


function select_gallery($gallery_name = "")
{
	//set gallery_name
    if(empty($gallery_name))
		$gallery_name =
		isset($_REQUEST[$this->config->url_gallery]) ?
		$_REQUEST[$this->config->url_gallery] : ".";

    //try to validate gallery id
    if(strlen($gallery_name)>1 && $gallery_name{1} != '/') $gallery_name = './'.$gallery_name;

    //detect back-references to avoid file-system walking
    if(strpos($gallery_name,"../")!==false) $gallery_name = ".";


    //fetch the gallery and image info
    $this->gallery = $this->io->get_gallery($gallery_name);

    //check if gallery was successfully fetched
    if($this->gallery == null) {
      $this->gallery = new gallery($gallery_name);
      $this->gallery->name = "Gallery not found ".htmlspecialchars($gallery_name);
    }

	//reindex the galleries
	if(!$this->reindexed) $this->reindex();

    //sort galleries and images
    if($this->config->gallery_sort_order!="x")
		usort($this->gallery->galleries, array($this,'gallery_sort'));
    if($this->config->image_sort_order!="x")
		usort($this->gallery->images, array($this,'image_sort'));

    //if startat is set the cast to int otherwise startat 0
    $this->startat =
		isset($_REQUEST[$this->config->url_startat]) ?
		(int)$_REQUEST[$this->config->url_startat] : 0;

    //encode the gallery name
    $this->gallery->id_encoded = $this->encode_id($this->gallery->id);

    $this->gallery->id_entities = htmlspecialchars($this->gallery->id);

    //find the parent
    $this->gallery->parent = substr($this->gallery->id_encoded, 0, strrpos($this->gallery->id_encoded, "/"));
    $this->gallery->parentName = urldecode(substr($this->gallery->parent,strrpos($this->gallery->parent,"/")+1));
    if($this->gallery->parentName == "") $this->gallery->parentName = $this->config->gallery_name;

    if(!empty($_REQUEST[$this->config->url_image])) $this->select_image($_REQUEST[$this->config->url_image]);
}
function reindex() {
	$this->reindexed = true;
	$to_delete = 0;
	$images_changed = 0;

  	$dir = $this->get_listing($this->config->pathto_galleries.$this->gallery->id,"images");
	usort($this->gallery->images, array($this, "quicksort"));
	foreach($this->gallery->images as $key => $value) $filenames[] = $value->filename;
	if(is_array($filenames) && is_array($dir->files)) $unset = array_diff($filenames, $dir->files);
	if($unset) {
	foreach($unset as $key => $value) {
		for($i=0; $i <= count($this->gallery->images); $i++) {
			if($this->gallery->images[$i]->filename == $unset[$key]) {
				unset($this->gallery->images[$i]);
				$to_delete++;
				$this->reindex();
			}
		}
	}
	}

	if($to_delete) $this->reindex();

	// if there aren't any files to delete
	if(!$to_delete or count($dir->files) > count($this->gallery->images)) :
		for($i=0; $i<count($dir->files); $i++) :
			$found = true;
			for($j=0; $j<count($this->gallery->images); $j++) {
				if($dir->files[$i] == $this->gallery->images[$j]->filename) {
		      		$found = false;
		      		break;
		   		}
			}

			// if changed files are found or if there is no metadata
			if($found or $_REQUEST['reindex']) :

				// set csv path
				$path = $this->config->pathto_galleries.$this->gallery->id."/".$this->gallery->images[$j]->filename;

				// set new image data
				$this->gallery->images[$j] = new image();
				$this->gallery->images[$j]->filename = $dir->files[$i];
				$this->gallery->images[$j]->name = $dir->files[$i];
				list($this->gallery->images[$j]->width, $this->gallery->images[$j]->height, $this->gallery->images[$j]->type) = @GetImageSize($path);

				/* get exif*/
				$exif = read_exif_data_raw($path,0);

				/* set exif time pointer */
				$time = $exif['SubIFD']['DateTimeOriginal'];
				if(!$time) $time = $exif['IFD0']['DateTime'];
				// Parse the time
				if($time != '') {
					preg_match('/([0-9]+):([0-9]+):([0-9]+) ([0-9]+):([0-9]+):([0-9]+)/', $time, $time_array);
					$time_date = mktime($time_array[4],$time_array[5],$time_array[6],$time_array[2],$time_array[3],$time_array[1]);
					$this->gallery->images[$j]->date = $time_date;
				}
       			$images_changed++;
   			endif;
    	endfor;
	endif;

	//sort the items how they originally were sorted
	usort($this->gallery->images, array($this, "image_sort"));
	if($images_changed) $this->reindex();
	$this->io->put_gallery($this->gallery);
	$this->last_error = "Could not save gallery info";
	return false;
}

function quicksort($a,$b) {
	return strnatcmp($a->filename, $b->filename);
}

function select_image($image) {
	if(is_string($image)) {
		foreach($this->gallery->images as $index => $img)
        	if($img->filename == $image) {
          		$this->image =& $this->gallery->images[$index];
          		$this->image->index = $index;
          		return true;
        	}
	} elseif(is_int($image) && $image >= 0 && $image < count($this->gallery->images)) {
    	$this->image =& $this->gallery->images[$image];
      	$this->image->index = $image;
      	return true;
	}

    $this->image = new image();
    $this->image->name = "Image not found ".htmlspecialchars($image);
	return false;
}

function gallery_sort($a, $b) {
	switch($this->config->gallery_sort_order) {
    case "p" : return strnatcmp($a->id, $b->id); //path
    case "P" : return strnatcmp($b->id, $a->id); //path (reverse)
    case "n" : return strnatcmp($a->name, $b->name); //name
    case "N" : return strnatcmp($b->name, $a->name); //name (reverse)
    case "i" : return strnatcasecmp($a->name, $b->name); //case-insensitive name
    case "I" : return strcasecmp($b->name, $a->name); //case-insensitive name (reverse)
    }
}

function image_sort($a, $b) {
	if($a->sort != null)
		return strnatcasecmp($a->sort, $b->sort);

    switch($this->config->image_sort_order) {
    case "n" : return strnatcmp($a->name, $b->name); //name
    case "N" : return strnatcmp($b->name, $a->name); //name (reverse)
    case "i" : return strnatcasecmp($a->name, $b->name); //case-insensitive name
    case "I" : return strnatcasecmp($b->name, $a->name); //case-insensitive name (reverse)
    case "f" : return strnatcmp($a->filename, $b->filename); //filename
    case "F" : return strnatcmp($b->filename, $a->filename); //filename (reverse)
    case "l" : return strnatcasecmp($a->filename, $b->filename); //filename
    case "L" : return strnatcasecmp($b->filename, $a->filename); //filename (reverse)
    case "d" : return strnatcmp($a->date, $b->date); //date
    case "D" : return strnatcmp($b->date, $a->date); //date (reverse)
	}
}

function arraystripslashes($toStrip) {
	if(is_array($toStrip))
    	return array_map(array("photostack","arraystripslashes"), $toStrip);
    else
    	return stripslashes($toStrip);
  	}

function encode_id($id) {
	$bits = explode("/",$id);
    for($i=1;$i<count($bits);$i++)
		$bits[$i] = rawurlencode($bits[$i]);
    //unset($bits[0]);
    return implode("/",$bits);
}

function format_url($gallery, $image = null, $startat = null, $action = null, $template = null) {
	if($this->config->use_mod_rewrite) {
    //format url for use with mod_rewrite
    $gallery = ltrim($gallery, './');
	if($gallery == '' && $startat) $gallery = '.';
    $ret = $this->config->base_url.$gallery;

    if($startat) $ret .= ','.$startat;
    if($gallery != '') $ret .= '/';
    if($image)   $ret .= rawurlencode($image);
    if($template) $ret .= $template.'/';

    $query = array();
    if($action)  $query[] = $this->config->url_action."=".$action;
    if(!empty($query))
    	$ret .= '?'.implode('&amp;', $query);
    } else {
		//format plain url
     	$ret  = $this->config->index_file_url;
      	$ret .= $this->config->url_gallery."=".$gallery;
      	if($startat) $ret .= "&amp;".$this->config->url_startat."=".$startat;
      	if($image)   $ret .= "&amp;".$this->config->url_image."=".rawurlencode($image);
      	if($action)  $ret .= "&amp;".$this->config->url_action."=".$action;
		if($template) $ret .= "&amp;".$this->config->url_template."=".$template;
	}
	return $ret;
}

function organize_url() {
		echo $this->config->base_url.'organize.php';
}

function page_title() {
	if($this->is_image() && $this->image_name()!="")
    	$image = $this->config->separator.' '.$this->image_name();

    if($this->gallery_name()!="" && $this->gallery_id_encoded() != '.')
     	$gal = $this->config->separator.' '.$this->gallery_name();

	return $this->config->gallery_name.' '.@$gal.' '.@$image;
}

function thumb_url($gallery, $image, $width, $height, $forceSize) {
	$ret = $this->config->base_url;
    $ret .= "thumb.php";
    $ret .= "?gallery=".$gallery."&amp;image=".rawurlencode($image);
    $ret .= "&amp;width=".$width."&amp;height=".$height;
    if($forceSize) $ret .= "&amp;force=1";
	return $ret;
}

function raw_thumb_url($gallery, $image, $width, $height, $forceSize) {
	$ret = $this->config->base_url;
    $ret .= "thumb.php";
    $ret .= "?gallery=".$gallery."&image=".rawurlencode($image);
    $ret .= "&width=".$width."&height=".$height;
    if($forceSize) $ret .= "&force=1";
	return $ret;
}

function movie_cache_path() {
	$file = fopen(str_replace('amp;','',$this->image_url()).'&movie=true', 'r');
	$image = ($this->index===null) ? $this->image->filename : $this->gallery->images[$this->index]->filename;
	return $this->config->base_url.$this->config->pathto_cache.md5(strtr('-'.$this->gallery->id_encoded.'-'.$image,":/?\\","----")).'.flv';
}

function is_image() {
    return !empty($this->image);
}

function is_gallery() {
	return $this->gallery && $this->gallery_has_sub();
}

function is_album() {
	return !$this->is_gallery() && !$this->is_image() && !empty($this->gallery);
}

function get_listing($wd, $type = "dirs") {
	$dir = new stdClass;
    $dir->path = realpath($wd).DIRECTORY_SEPARATOR;
    $dir->files = array();
    $dir->dirs = array();
    $dp = opendir($dir->path);

    if(!$dp) return false;

    switch($type) {
		case "images" :
    	  	while(false !== ($entry = readdir($dp)))
    	    	if(!is_dir($entry) && preg_match("/\.(".$this->config->recognised_extensions.")$/i",$entry) && !strstr($entry, '._')) {
					if($this->config->ignored_text != '') {
						if (!preg_match("/(".$this->config->ignored_text.")/i",$entry)) $dir->files[] = $entry;
					} else {
						$dir->files[] = $entry;
					}
					if((count($dir->files)) <= count(@$this->config->thumb_number_gallery)) continue;
				}
    	    sort($dir->files);
    	    rewinddir($dp);
  		//run on and get dirs too (no break)
		case "dirs" :
    		while(false !== ($entry = readdir($dp)))
    	    	if(is_dir($dir->path.$entry) && $entry{0} != '.') $dir->dirs[] = $entry;
    	    sort($dir->dirs);
		break;

		case "all" :
    		while(false !== ($entry = readdir($dp)))
    	    	if(is_dir($dir->path.$entry)) $dir->dirs[] = $entry;
    	      	else $dir->files[] = $entry;
    	    sort($dir->dirs);
    	    sort($dir->files);
		break;

    	default :
    		while(false !== ($entry = readdir($dp)))
    	    	if(strpos(strtolower($entry),$type)) $dir->files[] = $entry;
    	    sort($dir->files);
    }
    closedir($dp);
    return $dir;
}

function is_logged_in() {
	if(isset($_SESSION["photostack_user"]) && $_SESSION["photostack_user"]->check == md5($_SERVER["REMOTE_ADDR"]) && (time() - $_SESSION["photostack_user"]->login_time < 1800)) {
  		$_SESSION["photostack_user"]->login_time = time();
  		return true;
    }
    return false;
}

function crumb_array() {
	$crumb[0] = new stdClass;
    $crumb[0]->id = ".";
    $crumb[0]->path = ".";
    $crumb[0]->name = $this->config->gallery_name;

    if(!isset($this->gallery->id)) return $crumb;

    $galleries = explode("/",$this->gallery->id);

    for($i=1;$i<count($galleries);$i++) {
      $crumb[$i] = new stdClass;
      $crumb[$i]->id = $galleries[$i];
      $crumb[$i]->path = $crumb[$i-1]->path."/".rawurlencode($galleries[$i]);
      $crumb[$i]->name = $galleries[$i];
    }

    if($this->is_image()) {
      $crumb[$i] = new stdClass;
      $crumb[$i]->id = "";
      $crumb[$i]->path = "";
      $crumb[$i]->name = $this->image->name;
    }

    return $crumb;
}

function crumbline() {
    $separator = $this->config->separator;
    $crumbArray = $this->crumb_array();
    $ret = "";
    for($i=0;$i<count($crumbArray)-1;$i++) {
      $ret .= $crumbArray[$i]->name.' '.$separator.' ';
    }
    $ret .= $crumbArray[$i]->name;
    return $ret;
}

function crumb_links()
  {
    $separator = $this->config->separator;
    $crumbArray = $this->crumb_array();
    $ret = "";
    for($i=0;$i<count($crumbArray)-1;$i++) {
      $ret .= "<a href=\"".$this->format_url($crumbArray[$i]->path)."\">".$crumbArray[$i]->name."</a> ".$separator."\n";
    }
    $ret .= $crumbArray[$i]->name;
    return $ret;
  }

  /////////////////////////////
  //////gallery functions//////
  /////////////////////////////

  function gallery_id_encoded()
  {
   if($this->index === null or empty($this->gallery->galleries[$this->index]))
      return $this->gallery->id_encoded;
    else
      return $this->encode_id($this->gallery->galleries[$this->index]->id);
  }

function gallery_id()
  {
   if($this->index === null or empty($this->gallery->galleries[$this->index]))
      return $this->gallery->id;
    else
      return $this->gallery->galleries[$this->index]->id;
  }

function gallery_contents()
{
  if($this->is_album() or $this->gallery_count_text() == 0)
    return $this->image_count_text();
  else
    return $this->gallery_count_text();
}

function gallery_url()
{
 if($this->index === null or empty($this->gallery->galleries[$this->index]))
    return $this->format_url($this->gallery->id_encoded);
  else
    return $this->format_url($this->encode_id($this->gallery->galleries[$this->index]->id));
}
function gallery_path()
{
  if($this->index === null or empty($this->gallery->galleries[$this->index]))
  	return realpath($this->config->pathto_galleries.$this->gallery->id_encoded).'/';
  else
    return realpath($this->config->pathto_galleries.$this->encode_id($this->gallery->galleries[$this->index]->id));
}

  /**
   * @return string the name of the gallery
   */
function gallery_name()
{
if($this->gallery->id == '.' && $this->index === null) return $this->config->gallery_name;
  if($this->index === null or empty($this->gallery->galleries[$this->index]))
    return htmlentities($this->gallery->name, ENT_COMPAT, $this->config->charset);
  else
	return htmlentities($this->gallery->galleries[$this->index]->name, ENT_COMPAT, $this->config->charset);
}


  /**
   * @return string the description of the gallery
   */
  function gallery_desc($before = '', $after = '')
  {
    return ($this->index === null or empty($this->gallery->galleries[$this->index])) ?
    	$before.$this->gallery->desc.$after :
		$before.$this->gallery->galleries[$this->index]->desc.$after;
  }

  /**
   * @return string the long description field of the gallery
   */
  function gallery_desc_long($before = '', $after = '')
  {
    return ($this->index === null or empty($this->gallery->galleries[$this->index])) ?
      	$before.$this->gallery->long_desc.$after :
		$before.$this->gallery->galleries[$this->index]->long_desc.$after;
  }


  function gallery_date($before = '', $after = '', $format = 'F j, Y')
    {


        if($this->gallery->date != null) return $before.date($format, $this->gallery->date).$after;

		return ($this->gallery->galleries[$this->index]->date != null) ?
			$before.date($format, $this->gallery->galleries[$this->index]->date).$after :
			$before.date($format,filemtime($this->gallery_path())).$after;

    }


 /**
* @param int the index of the sub gallery to count (optional)
* @return int the number of galleries in the specified gallery
* or of the current gallery if $index is not specified
*/
  function gallery_count()
  {

    if($this->index === null or empty($this->gallery->galleries[$this->index]))
      $count = count($this->gallery->galleries);
    else
      $count = count($this->gallery->galleries[$this->index]->galleries);
    return $count;

  }

  /**
   * @return string the number of galleries in the specified gallery
   */
  function gallery_count_text()
  {
    return sprintf("%s&nbsp;galleries", $this->gallery_count());
  }

  /**
   * @param int the index of the sub gallery to count (optional)
   * @return boolean true if the specified gallery (or the current gallery
	 * if $index is not specified) has sub-galleries; false otherwise
   */
	function gallery_has_sub()
	{

    return count($this->gallery->galleries) > 0;
	}

  /**
   * @param int the index of the sub gallery to check (optional)
   * @return boolean true if the specified gallery (or the current gallery
	 * if $index is not specified) contains one or more images; false otherwise
   */
	function gallery_has_images()
	{
	  return count($this->image_count())>0;
	}

  /**
   * @uses galleryThumbnailImage
   * @return string
   */

  function gallery_thumbnail_linked()
  {
    $ret  = "<a href=\"".$this->gallery_url()."\">";
    $ret .= $this->gallery_thumbnail();
    $ret .= "</a>";
    return $ret;
  }

  function gallery_thumbnail($extrahtml = '', $width = null, $height = null)
  {

    if($this->index === null or empty($this->gallery->galleries[$this->index])):
    $gal = $this->gallery;
    else:
    $gal = $this->gallery->galleries[$this->index];
    endif;
    $image = $gal->filename;

    if(!empty($extrahtml)) { $extrahtml = $extrahtml.' '; }

    if(!isset($gal->images[0]->filename) or $gal->filename == '__none__') {
	     return "";
	 }

	 if($gal->filename == '' && isset($gal->images[0]->filename)) {
	 	$image = $gal->images[0]->filename;
	 }
	 $width = (!$width)?$this->config->thumb_width_gallery : $width;
	 $height = (!$height)?$this->config->thumb_height_gallery : $height;

     $ret  = "<img ".$extrahtml."src=\"".$this->thumb_url(rawurlencode($gal->id), $image,
                                       $width,
                                       $height,
                                       $this->config->thumb_force_size_gallery);
     $ret .= '" ';
     $ret .= 'alt="Sample image from gallery" />';

    return $ret;
  }

  /**
   * returns "Showing & Links"
   */

  /**
   * @return string
   */
  function gallery_showing($text = 'Showing ', $before = '', $after = '')
  {
    if($this->is_album()) {
      $total = $this->image_count();
      $perPage = $this->config->thumb_number_album;
    } else {
      $total = $this->gallery_count();
      $perPage = $this->config->thumb_number_gallery;
    }

    if($this->startat+$perPage > $total)
      $last = $total;
    else
      $last = $this->startat+$perPage;

    if($total != 0) return $before.sprintf($text."%s-%s&nbsp;of&nbsp;%s",($this->startat+1),$last,$total).$after;
    else return "$before $text 0 of 0 $after";


  }

  /**
   * @return int the number of 'pages' or 'screen-fulls'
   */
   function gallery_page_count() {
 if($this->is_album())
      return intval($this->image_count()/$this->config->thumb_number_album)+1;
    else
      return intval($this->gallery_count()/$this->config->thumb_number_gallery)+1;
  }

  /**
   * @return int
   */
  function last_page_index() {
    if($this->is_album())
     return ($this->gallery_page_count()-1)*
        ($this->is_album()?$this->config->thumb_number_album:$this->config->thumb_number_gallery);
  }

function gallery_parent_link($text = "Up", $separator = ' | ') {
		if($this->gallery->id != ".")
	      return "<a href=\"".$this->format_url($this->gallery->parent)."\" title=\"Up one level\">".$text."</a>";
		elseif($this->gallery_has_next() && $this->gallery_has_prev())
			return $separator;
}

function gallery_has_next() {
    if($this->is_album()) {
    	return count($this->gallery->images) > $this->startat+$this->config->thumb_number_album;
    } else {
      	return count($this->gallery->galleries)>$this->startat+$this->config->thumb_number_gallery;
	}
}

function gallery_has_prev() {
	if($this->startat>0) return $this->startat>0;
    return false;
}

function gallery_has_pages() {
	if($this->gallery->id == ".") {
		if(!$this->gallery_has_prev() && !$this->gallery_has_next()) {
			return false;
		}
	}
	return true;
}

function gallery_rss_url() {
	return $this->format_url($this->gallery->id_encoded, null,null,null, $this->config->rss_template_name);
}

function gallery_next_url() {
	return $this->format_url($this->gallery->id_encoded, null, ($this->startat + ($this->is_album()?$this->config->thumb_number_album:$this->config->thumb_number_gallery)));
}

function gallery_next_link($default = 'Next Page', $extrahtml = null, $separator = ' | ') {
	if($extrahtml) $extrahtml = $extrahtml.' ';
	if($this->gallery_has_next())
    return $separator."<a ".$extrahtml."href=\"".$this->gallery_next_url()."\">".$default."</a>";
}
function gallery_prev_url() {
	return $this->format_url($this->gallery->id_encoded, null, ($this->startat-
    ($this->is_album()?$this->config->thumb_number_album:$this->config->thumb_number_gallery)));
}

function gallery_prev_link($default = "Prev Page", $extrahtml = null, $separator = ' | ') {
	if($extrahtml) $extrahtml = $extrahtml.' ';
	if($this->gallery_has_prev())
    return "<a ".$extrahtml."href=\"".$this->gallery_prev_url()."\">".$default."</a>".$separator;
}

function format_stripped($ret) {
    $ret = str_replace("<br />","\n",$ret);
    if($this->config->enable_clickable_urls) {
      //strip off html from autodetected URLs
      $ret = preg_replace('{<a href="('.FORMAT_REGEXP_PROTOCOLURL.')\">\1</a>}', '\1', $ret);
      $ret = preg_replace('{<a href="http://('.FORMAT_REGEXP_WWWURL.')">\1</a>}', '\1', $ret);
      $ret = preg_replace('{<a href="mailto:('.FORMAT_REGEXP_EMAILURL.')">\1</a>}', '\1', $ret);
    }
    return $ret;
}

function loop($kind = null) {
	/* Thanks Matt & Wordpress! */

	if(!$this->index) { $this->index = $this->startat; }
 	if($this->first_time) { $this->index++; } else { $this->index_rewind = $this->index + $this->startat; $this->first_time = true; }
	if($this->is_gallery() && $kind != 'images' or $kind == 'albums') {
		$selected = $this->selected_gallery_count();
	} elseif($this->is_album() or $this->is_image() or $kind == 'images') {
		$selected = $this->selected_images_count();
	}
	if($this->index < $selected + $this->startat ) return true;
}

function rewind_loop() {
	$this->first_time = false;
	if(!$this->gallery->galleries[$this->index]) { $this->index = -1; }
}

function current_template_url() {
	echo $this->config->base_url.$this->config->pathto_current_template;
}

function template_url() {
	return $this->config->base_url.$this->config->pathto_current_template;
}

function selected_images_array() {
	return array_slice($this->gallery->images, $this->startat, $this->config->thumb_number_album);
}

function selected_images_count() {
	return min(count($this->gallery->images) - $this->startat, $this->config->thumb_number_album);
}

function selected_gallery_array() {
	return array_slice($this->gallery->galleries, $this->startat, $this->config->thumb_number_gallery);
}

function selected_gallery_count() {
	return min(count($this->gallery->galleries) - $this->startat, $this->config->thumb_number_gallery);
}

function nav_links() {
	$ret = "<link rel=\"Top\" title=\"".$this->config->gallery_name."\" href=\"".$this->format_url(".")."\" />\n";

    if($this->is_image()) {
		$ret .= "<link rel=\"Up\" title=\"".$this->gallery_name()."\" href=\"".$this->image_parent_url()."\" />\n";
      	if ($this->image_has_prev()) {
        	$ret .= "<link rel=\"First\" title=\"".$this->image_name(0)."\" href=\"".$this->image_first_url()."\" />\n";
        	$ret .= "<link rel=\"Prev\" title=\"".$this->image_name($this->image->index-1)."\" href=\"".$this->image_prev_url()."\" />\n";
      	}
		if ($this->image_has_next()) {
        	$ret .= "<link rel=\"Next\" title=\"".$this->image_name($this->image->index+1)."\" href=\"".$this->image_next_url()."\" />\n";

        	$ret .= "<link rel=\"Last\" title=\"".$this->image_name($this->image_count()-1)."\" href=\"".$this->image_last_url()."\" />\n";
      	}
    } else {
      	if($this->gallery->id != ".")
        	$ret .= "<link rel=\"Up\" title=\"".$this->gallery->parentName."\" href=\"".$this->format_url($this->gallery->parent)."\" />\n";
      	if($this->gallery_has_prev()) {
        	$ret .= "<link rel=\"Prev\" title=\"Previous\" href=\"".$this->gallery_prev_url()."\" />\n";
        	$ret .= "<link rel=\"First\" title=\"First\" href=\"".$this->format_url($this->gallery->id_encoded, null, 0)."\" />\n";
      	}
      	if($this->gallery_has_next()) {
        $ret .= "<link rel=\"Next\" title=\"Next\" href=\"".$this->gallery_next_url()."\" />\n";
        $ret .= "<link rel=\"Last\" title=\"Last\" href=\"".$this->format_url($this->gallery->id_encoded, null, $this->last_page_index())."\" />\n";
		}
    }
    return $ret;
}

function image($extrahtml = '') {
	$ext = strtolower(pathinfo($this->image_filename(), PATHINFO_EXTENSION));
	$movie_extensions = explode("|", strtolower($this->config->movie_extensions));
	if(in_array($ext, $movie_extensions)) $is_movie = true;
	
	if(!$this->is_image()) $this->index = 0;
	if($this->is_image() && $is_movie) {
		$ret .= '<script type="text/javascript" src="'.$this->config->base_url.'/includes/flowplayer/swfobject.js"></script>';
		$ret .= '<script type="text/javascript">
		// <![CDATA[
		var fo = new SWFObject("'.$this->config->base_url.'/includes/flowplayer/FlowPlayer.swf", "FlowPlayer", "468", "350", "7", "#ffffff", true);
		fo.addParam("allowScriptAccess", "always");';
		$ret .= '
		fo.addVariable("config", "{ playList: [	{ url: \''.$this->movie_cache_path().'\' }, { overlayId: \'play\' } ], initialScale: \'scale\' }");
		fo.write("image");
		// ]]>
		</script>';
	} else {
		if($extrahtml) $extrahtml = $extrahtml.' ';
    	$ret = '<img '.$extrahtml.'src="'.$this->image_url().'" ';
    	if($this->image_width() && $this->image_height()) $ret .= 'width="'.$this->image_width().'" height="'.$this->image_height().'" ';
    	$ret .= 'alt="'.$this->image_name().'" />';
	}
    return $ret;
}

function image_url() {
	if($this->config->full_image_resize) {
    	return $this->thumb_url(
		$this->gallery->id_encoded,
        ($this->index===null) ? $this->image->filename : $this->gallery->images[$this->index]->filename,
        $this->config->thumb_width_image,
        $this->config->thumb_height_image,
        $this->config->thumb_force_size_image);
	}
	return $this->image_real_url();
}

function image_real_url() {
	$image = ($this->index===null) ? $this->image->filename : $this->gallery->images[$this->index]->filename;

    //check if image is local (filename does not start with 'http://')
    if(substr($image,0,7)!="http://")
      	return $this->config->base_url.$this->config->pathto_galleries.
        ltrim($this->gallery->id_encoded, './')."/".rawurlencode($image);

	return $image;
}

  function image_name($index = '')
  {
    if($index == '') $index = $this->index;

    return ($index===null) ? $this->image->name : $this->gallery->images[$index]->name;

  }

  function image_filename($index = '')
  {
    if($index == '') $index = $this->index;

    return ($index===null) ? $this->image->filename : $this->gallery->images[$index]->filename;

  }

  /**
   * @return string
   */
  function image_desc($index = '', $before = '', $after = '')
  {
	 if($index == '') $index = $this->index;
     if(@$this->image->desc != null) return $before.$this->image->desc.$after;
     if(@$this->gallery->images[$this->index]->desc != null) return $before.$this->gallery->images[$this->index]->desc.$after;
  }

  function image_desc_long($index = '', $before = '', $after = '')
  {
 	if($index == '') $index = $this->index;
      if(@$this->image->long_desc != null) return $before.$this->image->long_desc.$after;
      if(@$this->gallery->images[$this->index]->long_desc != null) return $before.$this->gallery->images[$this->index]->long_desc.$after;
  }


  function image_date($index = '', $before = '', $after = '', $format = 'F j , Y - h:m:s')
  {
 	if($index == '') $index = $this->index;
	$pointer = ($index===null) ? $this->image->date : $this->gallery->images[$this->index]->date;
		$path = $this->image_path();
			if($pointer == '') { $pointer = filemtime($path); }


	return $before.date($format, $pointer).$after;
  }

function image_path($index = '') {
	if($index == '') $index = $this->index;

	if(!$this->is_image())
    return realpath($this->config->pathto_galleries.$this->gallery->id.'/'.$this->gallery->images[$index]->filename);
	else
	return realpath($this->config->pathto_galleries.$this->gallery->id.'/'.$this->image->filename);

}


  /**
   * @return string the html to display the preview thumbnails
   */
  function image_preview_thumbs()
  {
    $ret = "";
    for($i = ($this->image->index - $this->config->thumb_number_preview ) ; $i <= $this->image->index + $this->config->thumb_number_preview; $i++) {
      if(!isset($this->gallery->images[$i]))
        continue;

      $ret .= '<a href="'.$this->format_url($this->gallery->id_encoded, $this->gallery->images[$i]->filename).'">';
      $ret .= '<img src="'.$this->thumb_url(
                             $this->gallery->id_encoded, $this->gallery->images[$i]->filename,
                             $this->config->thumb_width_preview,
                             $this->config->thumb_height_preview,
                             $this->config->thumb_force_size_preview).'" ';
      $ret .= 'width="'.$this->thumbnail_width(
                             $this->image_real_width($i), $this->image_real_height($i),
                             $this->config->thumb_width_preview,
                             $this->config->thumb_height_preview,
                             $this->config->thumb_force_size_preview).'" ';
      $ret .= 'height="'.$this->thumbnail_height(
                             $this->image_real_width($i), $this->image_real_height($i),
                             $this->config->thumb_width_preview,
                             $this->config->thumb_height_preview,
                             $this->config->thumb_force_size_preview).'" ';
      $ret .= 'alt="'.$this->image_name($i).'" ';
      $ret .= 'title="'.$this->image_name($i).'" ';
      if($i==$this->image->index) $ret .= 'class="PreviewThumbCurrent" ';
      else $ret .= 'class="PreviewThumb" ';
      $ret .= "/></a>\n";
    }

    return $ret;
  }

  /**
   * @return string html link to the previous image if one exists
   */
  function image_prev_link($text="Previous",$extrahtml = null,$separator = ' | ')
  {
	if($extrahtml)
		$extrahtml = $extrahtml.' ';
    if($this->image_has_prev())
      return "<a ".$extrahtml."href=\"".$this->image_prev_url()."\" title=\"".$this->image_name($this->image->index-1)."\">".$text."</a>".$separator;
  }

  /**
   * @return string html link to the first image if not already first
   */
  function image_first_link($text="First",$extrahtml = null)
  {
	if($extrahtml)
		$extrahtml = $extrahtml.' ';
    if($this->image_has_prev())
      return "<a href=\"".$this->image_first_url()."\" title=\"".$this->image_name(0)."\">".$text."</a>";
  }

  /**
   * @return string
   */
  function image_parent_link($text = "Up",$extrahtml = '')
  {
	if($extrahtml)
		$extrahtml = $extrahtml.' ';
    return "<a ".$extrahtml."href=\"".$this->image_parent_url()."\" title=\"".$this->gallery_name()."\">".$text."</a>";
  }

  /**
   * @return string html link to the next image if one exists
   */
  function image_next_link($text="Next",$extrahtml = '',$separator = ' | ')
  {
	if($extrahtml)
		$extrahtml = $extrahtml.' ';
    if($this->image_has_next())
      return $separator."<a ".$extrahtml."href=\"".$this->image_next_url()."\" title=\"".$this->image_name($this->image->index+1)."\">".$text."</a>";
  }

  /**
   * @return string html link to the last image if not already last
   */
  function image_last_link($text="Last",$extrahtml = '')
  {
	if($extrahtml)
		$extrahtml = $extrahtml.' ';
    if($this->image_has_next())
      return "<a ".$extrahtml."href=\"".$this->image_last_url()."\" title=\"".$this->image_name($this->image_count()-1)."\">".$text."</a>";
  }

  function image_first_url()
  {
    return $this->format_url($this->gallery->id_encoded, $this->gallery->images[0]->filename);
  }

  function image_prev_url()
  {
    return $this->format_url($this->gallery->id_encoded, $this->gallery->images[$this->image->index-1]->filename);
  }

  function image_parent_url()
  {
    return $this->format_url($this->gallery->id_encoded, null, (floor($this->image->index/$this->config->thumb_number_album)*$this->config->thumb_number_album));
  }

  function image_next_url()
  {
    return $this->format_url($this->gallery->id_encoded, $this->gallery->images[$this->image->index+1]->filename);
  }

  function image_last_url()
  {
    return $this->format_url($this->gallery->id_encoded, $this->gallery->images[$this->image_count()-1]->filename);
  }

function image_page_url() {
	if($this->index === null) $img = $this->image;
    else $img = $this->gallery->images[$this->index];

	return $this->format_url($this->gallery->id_encoded, $img->filename);
}
  /**
   * @return boolean
   */
  function image_has_prev()
  {
    return isset($this->gallery->images[$this->image->index-1]);
  }

  /**
   * @return boolean
   */
  function image_has_next()
  {
    return isset($this->gallery->images[$this->image->index+1]);
  }

    /**
   * Image thumbnail that links to the appropriate image page
   * @return string
   */
  /**
   * @return int the number of images in the specified gallery
   */
  function image_count()
  {
    if(!$this->gallery->galleries[$this->index])
      return count($this->gallery->images);
    else
      return count($this->gallery->galleries[$this->index]->images);
  }

  /**
   * @return string the number of images in the specified gallery
   */
  function image_count_text()
  {
    return sprintf("%s&nbsp;images", $this->image_count());
  }


  function image_thumbnail_linked($title = '', $extrahtml = '')
  {
	if(!empty($extrahtml)) { $extrahtml = ' '.$extrahtml; }

    if($this->index === null) $img = $this->image;
    else $img = $this->gallery->images[$this->index];
    $ret  = "<a href=\"".$this->format_url($this->gallery->id_encoded, $img->filename).'"'.$extrahtml.'>';
    $ret .= $this->image_thumbnail_image($title);
    $ret .= "</a>";
    return $ret;
  }



  /**
   * Creates a correctly formatted &lt;img&gt; tag to display the album
   * thumbnail of the specified image
   * @param int index of image (optional)
   * @return string html
   */

function image_thumbnail() {
		echo $this->image_thumbnail_image();
	}
  function image_thumbnail_image($title = '')
  {
    if($this->index === null):
    $img = $this->image;
    else:
    $img = $this->gallery->images[$this->index];
    endif;

    $ret  = '<img src="'.$this->thumb_url(
                           $this->gallery->id_encoded, $img->filename,
                           $this->config->thumb_width_album,
                           $this->config->thumb_height_album,
                           $this->config->thumb_force_size_album).'" ';
    $ret .= 'width="'.$this->thumbnail_width(
                           $this->image_real_width(), $this->image_real_height(),
                           $this->config->thumb_width_album,
                           $this->config->thumb_height_album,
                           $this->config->thumb_force_size_album).'" ';
 $ret .= 'height="'.$this->thumbnail_height(
                           $this->image_real_width(), $this->image_real_height(),
                           $this->config->thumb_width_album,
                           $this->config->thumb_height_album,
                           $this->config->thumb_force_size_album).'" ';
    $ret .= 'alt="'.$this->image_name().'" ';
	($title != '') ? $title = $title : $title = $this->image_name();
    $ret .= 'title="'.$title.'" />';
    return $ret;
  }



  /**
   * Calculates thumbnail width given:
   * @param int original image width
   * @param int original image height
   * @param int required image width
   * @param int required image height
   * @param bool force size of thumbnail
   * @return int width of thumbnail in pixels
   */
  function thumbnail_width($image_width, $imageHeight, $maxWidth, $maxHeight, $forceSize)
  {
    //if aspect ratio is to be constrained set crop size
    if($forceSize) {
      $newAspect = $maxWidth/$maxHeight;
      @$oldAspect = $image_width/$imageHeight;
      if($newAspect > $oldAspect) {
        $cropWidth = $image_width;
        $cropHeight = round($imageHeight*($oldAspect/$newAspect));
      } else {
        $cropWidth = round($image_width*($newAspect/$oldAspect));
        $cropHeight = $imageHeight;
      }
    //else crop size is image size
    } else {
      $cropWidth = $image_width;
      $cropHeight = $imageHeight;
    }
    
    if($cropHeight > $maxHeight && ($cropWidth < $maxWidth || ($cropWidth > $maxWidth && round($cropHeight/$cropWidth * $maxWidth) > $maxHeight))) {
      return round($cropWidth/$cropHeight * $maxHeight);
    } elseif($cropWidth > $maxWidth) {
      return $maxWidth;
    } else {
      return $image_width;
	}
  }
function is_movie() {
	$ext = strtolower(pathinfo($this->image_filename(), PATHINFO_EXTENSION));
	$movie_extensions = explode("|", strtolower($this->config->movie_extensions));
	if(in_array($ext, $movie_extensions))  return true;
	return false;
}

function thumbnail_height($image_width, $image_height, $max_width, $max_height, $force) {
    //if aspect ratio is to be constrained set crop size
    if($force && !$this->is_movie()) {
      $newAspect = $max_width/$max_height;
      $oldAspect = $image_width/$image_height;
      if($newAspect > $oldAspect) {
        $cropWidth = $image_width;
        $cropHeight = round($oldAspect/$newAspect * $image_height);
      } else {
        $cropWidth = round($newAspect/$oldAspect * $image_width);
        $cropHeight = $image_height;
      }
    //else crop size is image size
    } else {
      $cropWidth = $image_width;
      $cropHeight = $image_height;
    }

    if(!$this->is_movie() && $cropWidth > $max_width && ($cropHeight < $max_height || ($cropHeight > $max_height && round($cropWidth/$cropHeight * $max_height) > $max_width)))
      return round($cropHeight/$cropWidth * $max_width);
    elseif($cropHeight > $max_height)
      return $max_height;
    else
      return $image_height;
  }

  /**
   * Calculates image width by supplying appropriate values to {@link thumbnail_width()}
   * @param int index of image (optional)
   * @return int width of image in pixels
   */
  function image_width()
  {
    if($this->config->full_image_resize) {
		if($this->image_real_width() == null) 
			return $this->config->thumb_width_image;
      	
		return $this->thumbnail_width(
               $this->image_real_width(), $this->image_real_height(),
               $this->config->thumb_width_image, $this->config->thumb_height_image,
               $this->config->thumb_force_size_image);
    } elseif($this->image_real_width() == null) {
		return $this->config->thumb_width_image;
	} else {
      return $this->image_real_width();
	}
  }

  /**
   * Calculates image height by supplying appropriate values to {@link thumbnail_height()}
   * @param int index of image (optional)
   * @return int height of image in pixels
   */
  function image_height()
  {
    if($this->config->full_image_resize) {
		if($this->image_real_height() == null) 
			return $this->config->thumb_height_image;
      		return $this->thumbnail_height(
               $this->image_real_width(), $this->image_real_height(),
               $this->config->thumb_width_image, $this->config->thumb_height_image,
               $this->config->thumb_force_size_image);
	} elseif($this->image_real_height() == null) {
		return $this->config->thumb_height_image;
    } else {
      return $this->image_real_height();
	}
  }


  /**
   * Returns the size of the original image
   * @param int index of image (optional)
   * @return int width of image in pixels
   */
  function image_real_width()
  {
    if($this->index === null)
      return $this->image->width;
    else
      return $this->gallery->images[$this->index]->width;
  }

  /**
   * Returns the size of the original image
   * @param int index of image (optional)
   * @return int height of image in pixels
   */
  function image_real_height()
  {
    if($this->index === null)
      return $this->image->height;
    else
      return $this->gallery->images[$this->index]->height;
  }


}


?>
