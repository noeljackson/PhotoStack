<?php 

//include the base IO class
require_once dirname(__FILE__)."/io.class.php";

class IO_sql extends IO
{
  
  /**
   * Overridden in subclasses
   */
  function query($query) { }
  function escape_string($query) { }
  function fetch_array($res) { }
  function num_rows($res) { }
  function error()
  {
    return "unknown error";
  }
  
  function getGallery($galleryId, $getChildGalleries = 1) 
  {
    $gal =& new gallery($galleryId, $parent);
    
    //try to open language specific gallery info
    $res = $this->query("SELECT * FROM ".$this->config->sql_prefix."galleries ".
           "WHERE galleryid='".$this->escape_string($galleryId)."' ".
           "AND lang='".$this->escape_string($language)."'");
    
    //if fail then try to open generic gallery info
    if(!$res || !$this->num_rows($res))
      $res = $this->query("SELECT * FROM ".$this->config->sql_prefix."galleries ".
             "WHERE galleryid='".$this->escape_string($galleryId)."' and lang=''");
    //if that succeeds then get galleries from db
    if($res && $this->num_rows($res)) {
      $galinfo = $this->fetch_array($res);
      $gal->filename = $galinfo['filename'];    
      $gal->name = $galinfo['name'];
      $gal->desc = $galinfo['description'];
      $gal->date = $galinfo['date'];
      
      //try to open language specific image info
      $res = $this->query("SELECT * FROM ".$this->config->sql_prefix."images ".
             "WHERE galleryid='".$this->escape_string($galleryId)."' ");
      
      //if fail then try to open generic image info
      if(!$res || !$this->num_rows($res))
        $res = $this->query("SELECT * FROM ".$this->config->sql_prefix."images ".
               "WHERE galleryid='".$this->escape_string($galleryId)."'");
      for($i=0;$i<$this->num_rows($res);$i++) {
        $imginfo = $this->fetch_array($res);
        $gal->images[$i] =& new image($imginfo['filename'], $gal);
        $gal->images[$i]->thumbnail = $imginfo['thumbnail'];
        $gal->images[$i]->name = $imginfo['name'];
        $gal->images[$i]->desc = $imginfo['description'];
        $gal->images[$i]->date = $imginfo['date'];
      }
        
    } else
      //no record found so use iifn method implemented in parent class
      return parent::get_gallery($galleryId, $parent, $getChildGalleries, $language);
    
    //discover child galleries
    $dir = photostack::get_listing($this->config->base_path.$this->config->pathto_galleries.$galleryId."/");
    if($getChildGalleries)
      //but only fetch their info if required too
      foreach($dir->dirs as $gallery) 
        $gal->galleries[] =& $this->get_gallery($galleryId."/".$gallery, $gal, $getChildGalleries-1, $language);
    else
      //otherwise just copy their names in so they can be counted
      $gal->galleries = $dir->dirs;
    
    return $gal;
  }
  
function put_gallery($gallery) {
   # //insert gallery info
    $success = $this->query(
		"REPLACE INTO ".$this->config->sql_prefix."galleries ".
		"VALUES ('".
		$this->escape_string($gallery->id).
		"','".
		$this->escape_string($gallery->filename).
		"','".
		$this->escape_string($gallery->name).
		"','".
		$this->escape_string($gallery->desc).
		"','".
		$this->escape_string($gallery->long_desc).
		"','".
		$this->escape_string($gallery->date).
		"')");
		
    //delete all image info
    $success &= (bool) $this->query("DELETE FROM ".$this->config->sql_prefix."images ".
          "WHERE galleryid='".$this->escape_string($gallery->id)."'");

    for($i=0;$i<count($gallery->images);$i++) {
      $success &= (bool) $this->query(
		"INSERT INTO ".$this->config->sql_prefix."images ".
		"VALUES ('".
		$this->escape_string($gallery->id).
		"','".
		$this->escape_string($gallery->images[$i]->filename).
		"','".
		$this->escape_string($gallery->images[$i]->name).
		"','".
		$this->escape_string($gallery->images[$i]->desc).
		"','".
		$this->escape_string($gallery->images[$i]->long_desc).
		"','".
		$this->escape_string($gallery->images[$i]->date).
		"','".
		$this->escape_string($gallery->images[$i]->sort).
		"')"
		);

	}
	return $success;
}
  
 
  /**
   * Fetches all registered users.
   */
  function get_users() {
    $res = $this->query("SELECT * FROM ".$this->config->sql_prefix."users");
    $usrinfo = $this->fetch_array($res);
    for($i=0;$i<$this->num_rows($res);$i++) {
      $users[$i] = new user($usrinfo['username'],$usrinfo['userpass']);
      $users[$i]->permissions = $usrinfo['permissions'];
      $users[$i]->email = $usrinfo['email'];
      $users[$i]->name = $usrinfo['name'];
      $users[$i]->description = $usrinfo['description'];
    }
    
    return $users;
  }

function get_user($username) {
	$users = $this->get_users();
	print_r($users);
	for($i=0;$i < count($users);$i++) {
  		if($users[$i]->username == $username) {
  			return $users[$i];
  			break;
  		} else {
  			return false;
  		}
  	}
}
  /**
   * Stores all registered users.
   * @param array  an array of sgUser objects representing the users to store
   */
  function put_users($users) {
    //empty table
    $success = (bool) $this->query("DELETE FROM ".$this->config->sql_prefix."users");
    for($i=0;$i<count($users);$i++)
      $success &= (bool) $this->query("INSERT INTO ".$this->config->sql_prefix."users ".
           "(username,userpass,permissions,groups,email,fullname,description,stats) VALUES ('".
           $this->escape_string($users[$i]->username)."','".
           $users[$i]->userpass."',".$users[$i]->permissions.",'".
           $this->escape_string($users[$i]->email)."','".
           $this->escape_string($users[$i]->name)."','".
           $this->escape_string($users[$i]->description)."')");
           
    return $success;
  }
}

?>
