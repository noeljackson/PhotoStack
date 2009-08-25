<?php 

//include the base IO class
require_once dirname(__FILE__)."/io.class.php";

class IO_csv extends IO
{
  //constructor provided by parent class

  /**
   * Fetches gallery info for the specified gallery and immediate children.
   */
  function get_gallery($gallery_name, $getChildGalleries = 1) 
  {
    $gal = new gallery($gallery_name);

    //if fail then try to open generic metadata
    $fp = @fopen($this->config->base_path.$this->config->pathto_galleries.$gallery_name."/metadata.csv","r");

	if($fp) { 
	while($temp[] = fgetcsv($fp,2048)); 
	fclose($fp);
      
      list(
        $gal->filename,
        $gal->name,
        $gal->desc,
        $gal->long_desc,
        $gal->date
      ) = $temp[1];
      
      
      for($i=0;$i<count($temp)-3;$i++) {
        $gal->images[$i] = new image();
        list(
        $gal->images[$i]->filename,
        $gal->images[$i]->name,
        $gal->images[$i]->desc,
		$gal->images[$i]->long_desc,
        $gal->images[$i]->date,
		$gal->images[$i]->sort
        ) = $temp[$i+2];

		$gal->images[$i]->full_path = $gal->name."/".$gal->images[$i]->filename;
        //get image size and type
        list(
        $gal->images[$i]->width, 
        $gal->images[$i]->height, 
        $gal->images[$i]->type
        ) = substr($gal->images[$i]->filename, 0, 7)=="http://"
            ? @GetImageSize($gal->images[$i]->filename)
            : @GetImageSize($this->config->base_path.$this->config->pathto_galleries.$gal->id."/".$gal->images[$i]->filename);
      }
      
      //discover child galleries
          $dir = photostack::get_listing($this->config->base_path.$this->config->pathto_galleries.$gallery_name."/", "dirs");
          if($getChildGalleries)
            //but only fetch their info if required too
          foreach($dir->dirs as $gallery) 
              $gal->galleries[] = $this->get_gallery($gallery_name."/".$gallery, $getChildGalleries);
          else
            //otherwise just copy their names in so they can be counted
            $gal->galleries = $dir->dirs;
          return $gal;

    } else
      return parent::get_gallery($gallery_name, $getChildGalleries);
  }
  
function put_gallery($gallery) {
	$csvpath = $this->config->base_path.$this->config->pathto_galleries.$gallery->id."/metadata.csv";
	
	if(!is_writeable($csvpath) && file_exists($csvpath)) {
		// this is the fix for a fubared metadata file
		$perms = substr(sprintf('%o', fileperms($csvpath)), -4);
		if($perms == '0000') @chmod($csvpath, octdec($this->config->chmod_value));
	}
	
   	$fp = fopen($csvpath,"w");
	
	// throw an error if necessary
	if(!$fp) { 
		if(file_exists($csvpath)) { 
			echo 'Please fix permissions on '.realpath($this->config->base_path.$this->config->pathto_galleries.$gallery->id);
		} else {
			echo "PhotoStack can't open your metadata file to write to it.";
		} 
		return false;
	} else {
	
	// write csv header
    $success  = (bool) fwrite($fp,"filename/thumbnail,image_name,description,long_description,date,sort");
	// write csv data
    $success &= (bool) fwrite($fp,"\n\"".$gallery->filename."\",\"".$gallery->name.'","'.$gallery->desc.'","'.$gallery->long_desc.'","'.$gallery->date.'"');

    for($i=0;$i<count($gallery->images);$i++)
      $success &= (bool) fwrite($fp,"\n\"".
        $gallery->images[$i]->filename."\",\"".
        str_replace('"','""',$gallery->images[$i]->name).'","'.
        str_replace('"','""',$gallery->images[$i]->desc).'","'.
		str_replace('"','""',$gallery->images[$i]->long_desc).'",'.
        str_replace('"','""',$gallery->images[$i]->date).',"'.
 		str_replace('"','""',$gallery->images[$i]->sort).'"'
      );
    $success &= (bool) fclose($fp);
    @chmod($csvpath, octdec($this->config->chmod_value));
	return $success;
	}
}
  
function get_users() {
	$fp = fopen($this->config->base_path."config.users.php","r");
	
	//strip off description line
	fgetcsv($fp,1024);
	
	for($i=0;$entry = fgetcsv($fp,1000,",");$i++) {
	  $users[$i] = new user(null,null);
	  list(
	    $users[$i]->username,
	    $users[$i]->userpass,
	    $users[$i]->permissions,
	    $users[$i]->email,
	    $users[$i]->fullname,
	    $users[$i]->description,
	  ) = $entry;
	}
	
	fclose($fp);
	return $users;
}

function get_user($username) {
	$users = $this->get_users();
	for($i=0;$i < count($users);$i++) {
  		if($users[$i]->username == $username) {
  			return $users[$i];
  			break;
  		} else {
  			return false;
  		}
  	}
}
  

function put_users($users) {
	// open users file
	$fp = fopen($this->config->base_path."config.users.php","w");
    if(!$fp) return false;
   
    $success = (bool) fwrite($fp,"<?php die(\"The contents of this file are hidden\"); ?>username,md5(pass),permissions,email,name,description\n");
    for($i=0;$i<count($users);$i++) 
    $success &= (bool) fwrite($fp, $users[$i]->username.",".$users[$i]->userpass.','.$users[$i]->permissions.',"'.$users[$i]->email.'","'.$users[$i]->fullname.'","'.$users[$i]->description.'"'."\n");
    
    fclose($fp);
    return $success;
	}
}

?>