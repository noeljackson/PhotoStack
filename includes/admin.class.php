<?php 
define("USER_ADMIN",     1024);
define("USER_SUSPENDED", 2048);

class admin extends photostack {
var $lastError = "";
var $index;
  /**
   * Admin constructor. Doesn't call {@link Singapore} constructor.
   * @param string the path to the base singapore directory
   */
function admin($basePath = "") {
    //import class definitions
    //io handler class included once config is loaded
    require_once $basePath."includes/gallery.class.php";
    require_once $basePath."includes/config.class.php";
    require_once $basePath."includes/image.class.php";
    require_once $basePath."includes/user.class.php";
    require_once $basePath."includes/pclzip.lib.php";
	require_once $basePath."includes/exif.php";
    
    //remove slashes
    if(get_magic_quotes_gpc()) $_REQUEST = array_map(array("photostack","arraystripslashes"), $_REQUEST);

    //load config from default ini file (config.php)
    $this->config = new configuration("config.php");

	if(!file_exists(realpath($this->config->base_path.$this->config->pathto_galleries.$_REQUEST[$this->config->url_gallery])))
		$_REQUEST[$this->config->url_gallery] = ".";

	$gallery_name = isset($_REQUEST[$this->config->url_gallery]) ? $_REQUEST[$this->config->url_gallery] : ".";

    $this->config->pathto_admin_template = $this->config->pathto_templates.$this->config->admin_template_name."/";
	$this->config->pathto_current_template = $this->config->pathto_templates.$this->config->template.'/';

    //load config from admin template ini file (admin.ini) if present
    $this->config->load_config($this->config->pathto_admin_template."admin.ini");

    //include IO handler class and create instance
    require_once $basePath."includes/io_".$this->config->io_handler.".class.php";
    $ioClassName = "IO_".$this->config->io_handler;
    $this->io = new $ioClassName($this->config);

    //set action to perform
    (empty($_REQUEST["action"])) ? $this->action = "view" : $this->action = $_REQUEST["action"];
    
	//set cachefile path
	@$this->cachefile = $this->config->base_path.'cache'.'/php-cache-'.
	md5($_SERVER['SCRIPT_NAME'].$_SERVER['REQUEST_URI'].
	$_REQUEST[$this->config->url_startat].$_REQUEST[$this->config->url_gallery]
	.$_REQUEST[$this->config->url_image].$_REQUEST[$this->config->url_action]);

    //set page title
    $this->page_title = $this->config->gallery_name;
}

	function last_error() { return $this->last_error; }

	function format_admin_url($action, $gallery = null, $image = null, $startat = null, $extra = null) {
    $ret  = $this->config->base_url."organize.php?";
    $ret .= "action=".$action;
    if($gallery != null)
      $ret .= "&amp;gallery=".$gallery;
    if($image != null)
      $ret .= "&amp;image=".$image;
    if($startat != null)
      $ret .= "&amp;startat=".$startat;
    if($extra != null)
      $ret .= $extra;
    return $ret;
	}
	
	function is_subpath($parent, $child) {
	$parentPath = realpath($parent);
    return substr(realpath($child),0,strlen($parentPath)) == $parentPath;
  	}
	
	function save_pass() {
    $users = $this->io->get_users();
    $found = false;

    for($i=0;$i < count($users);$i++)
      if($_POST["username"] == $users[$i]->username) {
        $found = true;
        if(md5($_POST["OldPass"]) == $users[$i]->userpass)
          if($_POST["NewPass1"]==$_POST["NewPass2"])
            if(strlen($_POST["NewPass1"]) >= 6 && strlen($_POST["NewPass1"]) <= 16) { 
              $users[$i]->userpass = md5($_POST["NewPass1"]);
			  $_SESSION['photostack_user']->userpass = md5($_POST['NewPass1']);
              if($this->io->put_users($users)) return true;
              else $this->last_error = "Could not save user info";
            }
            else 
              $this->last_error = "New password must be between 6 and 16 characters long.";
          else 
            $this->last_error = "The new passwords you entered do not match.";
        else 
          $this->last_error = "The current password you entered does not match the one in the database.";
      }
    
    if(!$found) $this->last_error = "The username specified was not found in the database.";
    
    //some sort of error occurred so:
    return false;
  }
  
  /**
   * @return boolean true if user is logged in; false otherwise
   */
  function is_logged_in()
  {
    if($_SESSION["photostack_user"]->check == md5($_SERVER["REMOTE_ADDR"]) && (time() - $_SESSION["photostack_user"]->login_time < 1800)) {
      $_SESSION["photostack_user"]->login_time = time();
      return true;
    }
	
    return false;
  }

  /**
   * Attempts to log a registered user into admin.
   * 
   * @return boolean true on success; false otherwise
   */
  function login() 
  {
	
    if(isset($_POST["username"]) && isset($_POST["password"])) {
      $users = $this->io->get_users();
      for($i=0;$i < count($users);$i++)
        if($_POST["username"] == $users[$i]->username && md5($_POST["password"]) == $users[$i]->userpass){
          if($users[$i]->permissions & USER_SUSPENDED) {
            $this->last_error = "Your account has been suspended";
            return false;
          } else { 
            $_SESSION["photostack_user"] = $users[$i];
            $_SESSION["photostack_user"]->check = md5($_SERVER["REMOTE_ADDR"]);
            $_SESSION["photostack_user"]->ip = $_SERVER["REMOTE_ADDR"];
            $_SESSION["photostack_user"]->login_time = time();
            return true;
          }
        }
      $this->logout();
      $this->last_error = "Username and/or password incorrect";
      return false;
    }
    $this->last_error = "You must enter a username and password";
    return false;

  }
  
  function logout()
  {
    $_SESSION["photostack_user"] = null;
    return true;
  }

  function is_admin($usr = null)
  {
    if($usr == null)
      $usr = $_SESSION["photostack_user"];
    return $usr->permissions & USER_ADMIN;
  }

  function add_user()
  {
    $users = $this->io->get_users();
    for($i=0; $i<count($users); $i++)
      if($users[$i]->username == $_REQUEST["username"]) {
        $this->last_error = "Username already exists";
        return false;
      }
    
    if(!preg_match("/[a-zA-Z0-9_]{3,}/",$_REQUEST["username"])) {
      $this->last_error = "Username must be at least 3 characters long and contain only alphanumeric characters";
      return false;
    }
    mail($_REQUEST['email'], 'Your New PhotoStack Login', "Your new PhotoStack login has been successfully created.\n\nYou can log in with the following information.\n\nLogin URL: ".$this->config->base_url.'organize.php'."\nUsername: ".$_REQUEST['username']."\nPassword: ".$_REQUEST['password'], 'From: New PhotoStack Login <info@photostack.org>');
    
    $users[$i] = new user($_REQUEST["username"], md5($_REQUEST["password"]), ($_REQUEST["user_type"] == "admin") ? 1024 : 0);
    
    if($this->io->put_users($users))
      return true;
    
    $this->last_error = "Could not save user info";
    return false;
  }
  
  function delete_user($user = null)
  {
    if($user == null)
      $user = $_REQUEST["username"];
      
    if($user == "admin" || $user == "guest") {
      $this->last_error = "Cannot delete built in accounts";
      return false;
    }
      
    $users = $this->io->get_users();
    for($i=0; $i<count($users); $i++)
      if($users[$i]->username == $_REQUEST["username"]) {
        
        //delete user at offset $i from $users
        array_splice($users,$i,1);
        
        if($this->io->put_users($users))
          return true;
    
        $this->last_error = "Could not save user info";
        return false;
      }
    
    $this->last_error = "Username not recognised";
    return false;
  }
  
  function save_user() {
    if($_REQUEST['action'] == 'newuser') {
        $this->add_user();
    }
    $users = $this->io->get_users();
    
    for($i=0; $i<count($users); $i++)
      if($users[$i]->username == $_REQUEST["username"]) {
        $users[$i]->email = $this->prepare_text($_REQUEST["email"]);
        $users[$i]->fullname = $this->prepare_text($_REQUEST["fullname"]);
        $users[$i]->description = $this->prepare_text($_REQUEST["description"]);
        if($this->is_admin() && $_REQUEST["action"] == "saveuser") {
          
          $users[$i]->permissions = ($_REQUEST["user_type"] == "admin") ? $users[$i]->permissions | USER_ADMIN : $users[$i]->permissions & ~USER_ADMIN;
          if(isset($_REQUEST["password"]) && $_REQUEST["password"] != "**********")
            $users[$i]->userpass = md5($_REQUEST["password"]);
        }
        if($this->io->put_users($users))
          return true;
        $this->last_error = "Could not save user info";
        return false;
      }
    $this->last_error = "Username not recognised";
    return false;
  }
  
  /**
   * Suspend or unsuspend a user's account.
   * 
   * @return bool true on success; false otherwise
   */
  function suspend_user() {
    $users = $this->io->get_users();
    for($i=0; $i<count($users); $i++)
      if($users[$i]->username == $_REQUEST["username"]) {
      
        $users[$i]->permissions = ($users[$i]->permissions & USER_SUSPENDED) ? $users[$i]->permissions & ~USER_SUSPENDED : $users[$i]->permissions | USER_SUSPENDED;
        if($this->io->put_users($users))
          return true;
        $this->last_error = "Could not save user info";
        return false;
      }
    $this->last_error = "Username not recognised";
    return false;
  }


  function generate_thumbs($object, $gallery_name)
  {
    $dir = $object->get_listing($object->config->base_path.$object->config->pathto_galleries.$gallery_name.'/',"images");
	
    if($object->config->load_config($object->config->base_path.$object->config->pathto_galleries.$gallery_name."/gallery.ini"));
    if($object->config->load_config($object->config->base_path.$object->config->pathto_templates.$object->config->template."/template.ini"));

    //if contains subgalleries, recurse
    if ($dir->dirs) {
      foreach ($dir->dirs as $subgal) {
        $this->generate_thumbs($object, $gallery_name.'/'.$subgal);
    	}
    //otherwise display thumbnails
    } else {

      foreach($dir->files as $image) {
        fopen (str_replace('&amp;','&',$object->thumb_url($gallery_name, $image,
                                            $object->config->thumb_width_image,
                                            $object->config->thumb_height_image,
                                            $object->config->thumb_force_size_image)),'r');

        fopen (str_replace('&amp;','&',$object->thumb_url($gallery_name, $image,
                                            $object->config->thumb_width_album,
                                            $object->config->thumb_height_album,
                                            $object->config->thumb_force_size_album)),'r');
        fopen (str_replace('&amp;','&',$object->thumb_url($gallery_name, $image,
                                            $object->config->thumb_width_preview,
                                            $object->config->thumb_height_preview,
                                            $object->config->thumb_force_size_preview)),'r');
        usleep(5000);
        flush();
       }
    }
      
		//reload admin.ini for admin thumbs
	$object->config->load_config($object->config->pathto_admin_template."admin.ini");
	return true;
	
  }
  
  /**
   * Creates a directory.
   *
   * @return boolean true on success; false otherwise
   */
  function new_gallery()
  {
    $newGalleryId = $this->gallery->id."/".$_REQUEST["newgallery"];
    $path = $this->config->pathto_galleries.$newGalleryId;
    
    if(file_exists($path)) {
      $this->last_error = "Gallery already exists";
      return false;
    }
    
    // FOR FTP
   /* if($this->config->use_ftp = "on") {	
		if(!$this->ftp_newdir($this->config->ftp_server,$this->config->ftp_user,$this->config->ftp_pass,
			$this->config->ftp_pathto_galleries,$newGalleryId, $this->config->directory_mode)) {
			$this->lastError = "Could not create directory.";
      	return false;
    	}
	} else { */
    
        if(!mkdir($path)) {
          $this->last_error = "Could not create directory, perhaps your permissions are set incorrectly.";
          return false;
        }
    
    
    $gal = new gallery($newGalleryId);
    $gal->name = $_REQUEST["newgallery"];
    
    if($this->io->put_gallery($gal))
      return true;
      
    $this->last_error = "Could not save gallery info";
    return false;
  }
  
  function prepare_text($text, $multiline = false)
  {
    if(get_magic_quotes_gpc())
      $text = stripslashes($text);
    
    if($multiline) {
      $text = strip_tags($text, $this->config->allowed_tags);
      $text = str_replace(array("\n","\r"), array("<br />",""), $text);
    } else
      $text = strip_tags($text);
      
    return $text;
  }

  
  /**
   * Saves gallery info to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function save_gallery()
  {
	$_REQUEST[$_REQUEST['field']] = $_REQUEST['value'];
	
	switch($_REQUEST['field']) {
		case "name" :
		if($_REQUEST["name"] == '') {
	  		$this->gallery->name = explode("/",$this->gallery->id); $this->gallery->name = $this->gallery->name[1];
		} else {
	        $this->gallery->name = $this->prepare_text($_REQUEST["name"]);
		}
		echo $this->gallery->name;
		break;
		case "folder" :
		if($_REQUEST['folder'] != '') {
			$startpath = rtrim($this->gallery->id,'/');
			$dir = substr(strrchr($startpath, "/"), 1);
			
			$sub = ltrim(str_replace($dir,'',$this->gallery->id),'.');
			
			$subForRename = ltrim($sub,'/');

			rename($this->config->pathto_galleries.$startpath, $this->config->pathto_galleries.$subForRename.$_REQUEST['folder']);
			header('Location: '.str_replace('&amp;','&',$this->format_admin_url("view",'.'.$sub.$_REQUEST['folder'], null, null)));
			exit;
		}
		break;
		case "desc" :
		$this->gallery->desc = $this->prepare_text($_REQUEST["desc"],true);
	    
	    if($this->config->enable_clickable_urls) {
	      //recognise URLs and htmlise them
	      $this->gallery->desc = preg_replace('{(?<!href="|href=)\b('.FORMAT_REGEXP_PROTOCOLURL.')\b(?!</a>)}', '<a href="$1">$1</a>', $this->gallery->desc);  //general protocol match
	      $this->gallery->desc = preg_replace('{(?<!://)\b('.FORMAT_REGEXP_WWWURL.')\b(?!</a>)}', '<a href="http://$1">$1</a>', $this->gallery->desc);  //web addresses starting www. without path info
	      $this->gallery->desc = preg_replace('{(?<!mailto:|\.)\b('.FORMAT_REGEXP_EMAILURL.')\b(?!</a>)}', '<a href="mailto:$1">$1</a>', $this->gallery->desc);  //email addresses *@*.*
	    }
	    echo $this->gallery->desc;
		break;
		case "date" :
		if(strtotime($_REQUEST['date']) != -1) {
        $return = strtotime($_REQUEST["date"]);
        $this->gallery->date = $return;
        echo date('F j, Y', $return);
    	} else {
		$return = '';
		$this->gallery->date = $return;
		echo 'no date';
		}
		break;
		case "thumbnail" :
		    if($_REQUEST['thumbnail'] == '__none__' or $_REQUEST['thumbnail'] == ''/*or $_REQUEST['value'] == '__random__'*/) {
    		    echo $_REQUEST['thumbnail'];
    	    } else {
    		    echo str_replace('&amp;', '&', rawurldecode($this->thumb_url(
    	                           rawurldecode($this->gallery->id_encoded), rawurlencode($_REQUEST['thumbnail']),
    	                           $this->config->thumb_width_preview,
    	                           $this->config->thumb_height_preview,
    	                           $this->config->thumb_force_size_preview)));
    	    }
		    
		    $this->gallery->filename = $_REQUEST['thumbnail'];
		break;
		case "long_desc" :
      $this->gallery->long_desc = $this->prepare_text($_REQUEST["long_desc"],true);
      echo $this->prepare_text($_REQUEST["long_desc"],true);
		break;
	}

    if($this->io->put_gallery($this->gallery))
      return true;
      
    $this->last_error = "Could not save gallery info, probably because your permissions are incorrect on this directory.";
    return false;
  }
  
  /**
   * Deletes a gallery and everything contained within it.
   *
   * @return boolean true on success; false otherwise
   */
  function delete_gallery($gallery_name = null)
  {
    if($gallery_name === null)
      $gallery_name = $_REQUEST[$this->config->url_gallery];
  
    //security check: make sure requested file is in galleries directory
    if(!$this->is_subpath($this->config->pathto_galleries,$this->config->pathto_galleries.$gallery_name)) {
      $this->last_error = "Object not found";
      return false;
    }
  
    //check that the gallery to delete is not the top level directory
    if(realpath($this->config->pathto_galleries.$gallery_name) == realpath($this->config->pathto_galleries)) {
      $this->last_error = "Cannot delete the top level directory";
      return false;
    }
    
    //remove the offending directory and all contained therein
    return $this->rmdir_all($this->config->pathto_galleries.$gallery_name);
  }

function reorder_gallery($gallery_name = null) {
	if($gallery_name === null) $gallery_name = $_REQUEST[$this->config->url_gallery];

	foreach($_REQUEST['album_list'] as $key => $value) {
		$this->gallery->images[$value]->sort = $key;
	}

	if($this->io->put_gallery($this->gallery)) {
			$this->reindex();
			 return true;
	}
}

function upload_bulk() {

	if(!$_FILES) {
		$this->last_error = "Please check your <strong><a href=\"http://uk2.php.net/manual/en/ini.core.php#ini.upload-max-filesize\">php.ini upload_max_filesize</a></strong> settings.";
		return false;
	}
	$filedata = $_FILES;

	foreach($filedata as $key) {

		if($key['name'] != '') {
		    if($key['error'] == 1) {
        		$this->last_error = "Could not upload file, <em>".$key['name']."</em>, <a href=\"http://de3.php.net/manual/en/features.file-upload.errors.php\">it is too large</a>!";
        		return false;
        	}
        	
			if(!strstr($key["name"], '.zip')) {
				$ret = $this->upload_image($key);
			} else {
				$ret = $this->upload_zip($key);
			}
			if($ret == false) {
				$error =  $this->last_error;
			}
		}
		}
		if(@$error) {
			return false;
		}
		return true;
}

function upload_image($imagefile) {
	//set the name
	$image = basename($imagefile['name']);

	//make sure file has a recognised extension
	if(!preg_match("/\.(".$this->config->recognised_extensions.")$/i",$image)) $image .= ".jpeg";
	
	//set the imag upload path
	$path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
	$srcImage = $image;
   
	// if the image already exists, figure out what to do.
	if(file_exists($path)) {
    switch($this->config->upload_overwrite) {
	case 1 : //overwrite
		$this->delete_image($image);
		break;
	case 2 : //generate unique
		for($i=0;file_exists($path);$i++) {
			$pivot = strrpos($srcImage,".");
			$image = substr($srcImage, 0, $pivot).'-'.$i.substr($srcImage, $pivot,strlen($srcImage)-$pivot);
			$path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
		}
		break;
	case 0 : //raise error
	default :
		$this->last_error = "File already exists (".$imagefile['name'].")";
		return false;
	}
	}
    
	// raise an error if image can not be moved
	if(!move_uploaded_file($imagefile["tmp_name"],$path)) {
		$this->last_error = "Could not upload file"; 
		return false;
	}
	
	// set the permissions of the uploaded image
	chmod($path, octdec($this->config->chmod_value));
	
	//create a new image object and set attributes
    $img = new image();
    $img->filename = $image;
    $img->name = strtr(substr($image, strrpos($image,"/"), strrpos($image,".")-strlen($image)), "_", " ");
    list($img->width, $img->height, $img->type) = GetImageSize($path);
    
	//add the image to the gallery images array
    $this->gallery->images[count($this->gallery->images)] = $img;
    
	//put the image into the gallery db
    if($this->io->put_gallery($this->gallery)) {
      return true;
    }
    
    $this->last_error = "Could not add image to gallery";
	//if the file could not be uploaded then unlink the file.
    @unlink($path);
	return false;
}


function upload_zip($imagefile) {

	if(!mkdir($tmpdir = $this->config->pathto_cache.'/'.uniqid("ps"))) {
 		$this->last_error = "Your cache directory is not writeable, please check the permissions.";
 		return false;
	}
    
	$archive = $imagefile["tmp_name"];
  
    if(!is_uploaded_file($archive)) {
      $this->last_error = "Could not upload file"; 
      return false;
    }
    
    switch($this->config->unzip_method) {
    	case 'unzip':
        //decompress archive to temp
        $cmd  = escapeshellcmd($this->config->pathto_unzip);
        $cmd .= ' -d "'.escapeshellcmd(realpath($tmpdir));
        $cmd .= '" "'.escapeshellcmd(realpath($archive)).'"';
    
        if(!exec($cmd)) {
          $this->last_error = "Could not decompress archive"; 
          return false;
        }
      	break;
      case 'pclzip':
      	/* 
				This uses the PclZip Library to extract the files of the zip-archive
      	*/
      	$zip = new PclZip(realpath($archive));

        $archiveContents = $zip->listContent();
        foreach ($archiveContents as $file){
          if(preg_match("/\.(".$this->config->recognised_extensions.")$/i",$file['filename'])){
            //if(stristr($file['filename'],'/') === FALSE){
              $imageIndex .= $file['index'] . ",";
            //}
          }
        }
        $imageIndex = substr($imageIndex,0,strlen($imageIndex)-1);
        $zip->extractByIndex($imageIndex,$tmpdir);
      break;
    }
    
    //start processing archive contents
    $wd = $tmpdir;
    $contents = $this->get_listing($wd,"images");
    
    //cope with archives contained within a directory
    if(empty($contents->files) && count($contents->dirs) == 1)
      $contents = $this->get_listing($wd .= '/'.$contents->dirs[0],"images");

    $success = true;

    //add any images to current gallery
    foreach($contents->files as $image) {
    
      //make sure file has a recognised extension
      if(!preg_match("/\.(".$this->config->recognised_extensions.")$/i",$image)) $image .= ".jpeg";
      
      $path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
      $srcImage = $image;
      
      if(file_exists($path))
        switch($this->config->upload_overwrite) {
          case 1 : //overwrite
            $this->delete_image($image);
            break;
          case 2 : //generate unique
            for($i=0;file_exists($path);$i++) {
              $pivot = strrpos($srcImage,".");
              $image = substr($srcImage, 0, $pivot).'-'.$i.substr($srcImage, $pivot,strlen($srcImage)-$pivot);
              $path = $this->config->pathto_galleries.$this->gallery->id."/".$image;
            }
            break;
          case 0 : //raise error
          default :
            $this->last_error = "File already exists";
            $success = false;
            continue;
        }
      
      copy($wd.'/'.$srcImage,$path);
	@chmod($path, octdec($this->config->chmod_value));
      $img = new image();
      
      $img->filename = $image;
      $img->name = strtr(substr($image, strrpos($image,"/"), strrpos($image,".")-strlen($image)), "_", " ");
      list($img->width, $img->height, $img->type) = GetImageSize($path);
      
      $this->gallery->images[count($this->gallery->images)] = $img;
    }
    
    //add any directories as subgalleries, if allowed
      foreach($contents->dirs as $gallery) {
			if($gallery != '__MACOSX') {
        $path = $this->config->pathto_galleries.$this->gallery->id."/".$gallery;
  
        if(file_exists($path))
          switch($this->config->upload_overwrite) {
            case 1 : //overwrite
              $this->delete_gallery($this->gallery->id.'/'.$gallery);
              break;
            case 2 : //generate unique
              for($i=0;file_exists($path);$i++)
                $path = $this->config->pathto_galleries.$this->gallery->id."/".$gallery.'-'.$i;
              break;
            case 0 : //raise error
            default :
              $success = false;
              continue;
          }
  
        rename($wd.'/'.$gallery,$path);
	chmod($path, octdec($this->config->chmod_value));
		}
      }
	
    
    //if images were added save metadata
    if(!empty($contents->files))
      $success &= $this->io->put_gallery($this->gallery);
    
    //if subgalleries were added reload gallery data
    if(!empty($contents->dirs))
      $this->select_gallery();
    
    //remove temporary directory
    $this->rmdir_all($tmpdir);
    
    if(!$success)
      $this->last_error = "Some archive contents could not be added";
      
    return $success;
  }
  
  /**
   * Saves image info to the database.
   *
   * @return boolean true on success; false otherwise
   */
  function save_image()
  {
	
  	if(trim($_REQUEST["image_name"]) == '') {
  	  	$this->image->name = explode('.',$this->image->filename); $this->image->name = $this->image->name[0]; 
	} else {
  	   $this->image->name = $this->prepare_text($_REQUEST["image_name"]);
  	}
  	if($_REQUEST['image_filename'] == '') { $_REQUEST['image_filename'] == $_REQUEST['image']; }
    $this->image->filename   = $this->prepare_text($_REQUEST['image_filename']);
	$this->image->sort = $this->prepare_text($_REQUEST['sort']);
    $this->image->desc = $this->prepare_text($_REQUEST["desc"],true);
    $this->image->long_desc = $this->prepare_text($_REQUEST["long_desc"],true);
	$this->image->date = '';

	if(strtotime($_REQUEST['date']) == -1) {
		$this->image->date = '';
	} else {
		$this->image->date = $this->prepare_text(strtotime($_REQUEST["date"]));
	}
	# IT SEEMS THAT THE NEW EXIF DATES GET MESSED UP WITH THIS 
	# LINES AND THE $_REQUEST['reindex']; commented out for now.
	/*	if($this->image->date == '') {$_REQUEST['reindex'] = 'yes'; $this->reindex(); 
	}
	*/

    if($_REQUEST['image_filename'] != $_REQUEST['image']) {
        $startpath = rtrim($this->gallery->id,'/');
        $dir = substr(strrchr($startpath, "/"), 1);
        $dir = $dir.'/';
        
        $images = $this->get_listing($this->config->base_path.$this->config->pathto_galleries.$startpath,"images");
        foreach ($images as $key => $value) {
        foreach($value as $key => $image) {
        	if($image == $_REQUEST['image_filename']) $found = true;
        }}

        if(!$found) {
            if(rename($this->config->pathto_galleries.$dir.$_REQUEST['image'], $this->config->pathto_galleries.$dir.$_REQUEST['image_filename'])) {
            	$moved = true;
				$this->purge_cache('images', $_REQUEST['image']);
			}
        } else {
         $this->last_error = 'There is already an image with that filename.';
         return false;
        }
	}

	if($this->io->put_gallery($this->gallery)) {
			$this->reindex();
			 return true;
	}
    
    if($moved == true) {
        header('Location: '.str_replace('&amp;','&',$this->format_admin_url("view",$this->gallery->id, $_REQUEST['image_filename'], null))); exit;
    }
    
    $this->last_error = "Could not save image information";
    return false;    
  }
  
  /**
   * Deletes an image from the current gallery.
   *
   * @param string the filename of the image to delete (optional)
   * @return boolean true on success; false otherwise
   */
	function delete_bulk($images) {
		foreach($images as $image => $value) {
			$ret = $this->delete_image($value);
			if($ret == false) {
				$error =  $this->last_error;
			}
		}
		if($error) {
			return false;
		}
		return true;
	}
	
	function rotate_bulk($images, $degree) {
		foreach($images as $image => $value) {
			$ret = $this->rotate_image($value, $degree);
			if($ret == false) {
				$error =  $this->last_error;
			}
		}
		if($error) {
			return false;
		}
		$this->reindex();
		return true;
	}
	
function rotate_image($image = null, $degrees) {
	if($image === null) $image = $this->image->filename;
	
	for($i=0;$i<count($this->gallery->images);$i++)
        if($this->gallery->images[$i]->filename == $image)
            array_splice($this->gallery->images,$i,1);
            
    if(file_exists($this->config->pathto_galleries.$this->gallery->id."/".$image)
    //security check: make sure requested file is in galleries directory
	&& $this->is_subpath($this->config->pathto_galleries,$this->config->pathto_galleries.$this->gallery->id."/".$image)) {
    // set filename for image
	$filename = $this->config->pathto_galleries.$this->gallery->id."/".$image;
	// find file extension/type
	$type = strrchr($filename,'.');
	if($this->config->thumbnail_software == "gd2") {
		// execute GD rotation
		switch($type) {
        	case '.gif' : $source = imagecreatefromgif($filename); break;
			case '.png' : $source = imagecreatefrompng($filename); break;
			default: $source = imagecreatefromjpeg($filename); break;
        }
		
		$rotate_img = imagerotate($source, $degrees, 0);
		
		switch($type) {
			case '.gif' : $rotateit = imagegif($rotate_img, $filename); break;
			case '.png' : $rotateit = imagepng($rotate_img, $filename); break;
			default: $rotate = imagejpeg($rotate_img, $filename); break;
		}  
	} elseif($this->config->thumbnail_software=="im") {
		// execute ImageMagick with correct rotation
    	$degrees = $degrees - 180;
    	$cmd = $this->config->pathto_convert.' -rotate '.$degrees.' '.realpath($filename).' '.realpath($filename);
    	exec($cmd);
    	$rotate = true;
	}

	if(!$rotate) {
    	$this->last_error = "Either the image you were trying to rotate doesn't exist, or your permissions are set incorrectly.";
    	return false;
	} else {
		return true;
	}
	
	}
}
	
function delete_image($image = null) {
    if($image === null) $image = $this->image->filename;

    for($i=0;$i<count($this->gallery->images);$i++)
    	if($this->gallery->images[$i]->filename == $image)
        array_splice($this->gallery->images,$i,1);
    
    if(file_exists($this->config->pathto_galleries.$this->gallery->id."/".$image)
    //security check: make sure requested file is in galleries directory
    && $this->is_subpath($this->config->pathto_galleries,$this->config->pathto_galleries.$this->gallery->id."/".$image)) {
		if(!unlink($this->config->pathto_galleries.$this->gallery->id."/".$image)) {
			$this->last_error = "Either the image you were trying to delete doesn't exist, or your permissions are set incorrectly.";
	      	return false;
		}
	}
    
    if($this->io->put_gallery($this->gallery)) {
      $this->image = null;
      return true;
    } else {
      $this->last_error = "Could not delete image";
      return false;
    }
}

function purge_cache($type = "all", $match = null) {
	$dir = $this->get_listing($this->config->pathto_cache,$type);   
    $success = true;
    for($i=0;$i<count($dir->files);$i++)
		if(preg_match('/'.preg_quote($match).'/', $dir->files[$i]) && $match != null)
      	$success &= unlink($dir->path.$dir->files[$i]);    
    return $success;
}

function save_config() {
	if(is_writeable('config.php')) {
	$data = '; <?php die("The contents of this file are hidden"); ?>'."\n"; 

	foreach($_REQUEST['config'] as $key => $value) {
		if($value == 'on' or $value == 'off' or is_numeric($value)) {
		// do nothing
		} else { $value = '"'.$value.'"' ; }
        
		$data .= $key.' = '.$value."\n";

		if ($key == 'use_mod_rewrite' && $value == 'on')
			$edithtaccess = true;
	}

      $data .= "url_gallery = \"gallery\"
url_image = \"image\"
url_startat = \"startat\"
url_action = \"action\"
url_template = \"template\"";

	if($edithtaccess) {
		//open file to check if rules are present
		$filecheck = fopen(realpath($this->config->base_path.'.htaccess'), 'r');
		if($filecheck) {
			for($i=0;$entry = fgets($filecheck,1000);$i++) {
				$fullfile .= $entry;
				if(preg_match('/.*\#start\ photostack.*/', $entry)) $dontwrite = true;
			}
			
			fclose($filecheck);

		//check to see if rules are present
		if(!$dontwrite) {
			$htdata = $fullfile."\n".'
#start photostack
RewriteEngine On

# rewrite galleries
RewriteCond %{REQUEST_URI} !/galleries/ 
RewriteCond %{REQUEST_URI} !/templates/
RewriteCond %{REQUEST_URI} !/rss/
RewriteRule ^([^,]+)(,([0-9]+))?/$ index.php?gallery=$1&startat=$3 [QSA] 

# rewrite images 
RewriteCond %{REQUEST_URI} !/templates/
RewriteCond %{REQUEST_URI} !/galleries/
RewriteCond %{REQUEST_URI} !/rss/
RewriteRule ^((.*)/)?([^/]+\.(jpeg|JPG|JPEG|jpg|jpe|png|PNG|GIF|gif|bmp|tif|tiff))$ index.php?gallery=$2&image=$3 [QSA]

# rewrite rss
RewriteRule ^((.*)/)?rss/?$ index.php?gallery=$2&template=rss [QSA]
#end photostack
			';
			$ht = fopen(realpath($this->config->base_path.'.htaccess'), 'w');
			fwrite($ht, $htdata);
					fclose($ht);
		}
	    }

	}
	
	
	// write config.php
	if($fp = fopen('config.php', 'w')) {
		fwrite($fp, $data);
		fclose($fp);
		return true;
	}
    
	} else {
      $this->last_error = "Your config file is not writeable, please fix its permissions.";
      return false;
    }
}

  
function rmdir_all($wd)
  {
    if(!$dp = opendir($wd)) return false;
    $success = true;
    while(false !== ($entry = readdir($dp))) {
      if($entry == "." || $entry == "..") continue;
      if(is_dir("$wd/$entry")) $success &= $this->rmdir_all("$wd/$entry");
      else $success &= unlink("$wd/$entry");
    }
    closedir($dp);
    $success &= rmdir($wd);
    return $success;
}
  
  // FOR FTP
   /* function ftp_newdir($server,$user,$pass,$path,$newDir,$dirmode) {
		
	if ($this->config->ftp_secure = "on") {
		$connection = ftp_ssl_connect($server); // connection
	}else{
     	$connection = ftp_connect($server); // connection
	}

           // login to ftp server
           $result = ftp_login($connection, $user, $pass);
     
       // check if connection was made
         if ((!$connection) || (!$result)) {
           return false;
           exit();
           } else {
             ftp_chdir($connection, $path); // go to destination dir
           if(ftp_mkdir($connection,$newDir)) { // create directory
               ftp_site($connection, "CHMOD $dirmode $newDir")
					or die("FTP SITE CMD failed.");
					//ftp_chmod($connection, $dirmode, $newDir); 
               return $newDir;
           } else {
               return false;       
           }
       ftp_close($connection); // close connection
       }
     
    }*/

}
?>