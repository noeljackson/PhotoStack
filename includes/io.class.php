<?php 

class IO
{
  
  var $config;

  function IO(&$config)
  {
    $this->config =& $config;
  }

  /**
   * Fetches gallery info for the specified gallery (and immediate children).
   * @param string  gallery id
   * @param string  language code spec for this request (optional, ignored)
   * @param int     number of levels of child galleries to fetch (optional)
   */
  function get_gallery($gallery_name, $getChildGalleries = 1) 
  {
    $gal = new gallery($gallery_name);
    
    if(file_exists($this->config->base_path.$this->config->pathto_galleries.$gallery_name)) { 
    
      $bits = explode("/",$gal->id);
      $temp = strtr($bits[count($bits)-1], "_", " ");
      if($temp == ".")
        $gal->name = $this->config->gallery_name;
      elseif(strpos($temp, " - ")) 
        list($gal->artist,$gal->name) = explode(" - ", $temp);
      else
        $gal->name = $temp;
      
      $dir = photostack::get_listing($this->config->base_path.$this->config->pathto_galleries.$gal->id."/", "images");
      
      //set gallery thumbnail to first image in gallery (if any)
      if(isset($dir->files[0])) $gal->filename = $dir->files[0];
      
      for($i=0;$i<count($dir->files);$i++) {
        $gal->images[$i] = new image();
        $gal->images[$i]->filename = $dir->files[$i];
		$gal->images[$i]->full_path = $gal->name."/".$dir->files[$i];
		
        //trim off file extension and replace underscores with spaces
        $temp = strtr(substr($gal->images[$i]->filename, 0, strrpos($gal->images[$i]->filename,".")-strlen($gal->images[$i]->filename)), "_", " ");
        //split string in two on " - " delimiter
        $gal->images[$i]->name = $temp;
      
        //get image size and type
        list(
          $gal->images[$i]->width, 
          $gal->images[$i]->height, 
          $gal->images[$i]->type
        ) = @GetImageSize($this->config->base_path.$this->config->pathto_galleries.$gal->id."/".$gal->images[$i]->filename);
      }

    } else {
      //selected gallery does not exist

      return null;
    }
    
    //discover child galleries
    $dir = photostack::get_listing($this->config->base_path.$this->config->pathto_galleries.$gallery_name."/", "dirs");
    if($getChildGalleries)
      //but only fetch their info if required too
      foreach($dir->dirs as $gallery) 
        $gal->galleries[] = $this->get_gallery($gallery_name."/".$gallery, $getChildGalleries-1);
    else
      //otherwise just copy their names in so they can be counted
      $gal->galleries = $dir->dirs;
    
    return $gal;
  }
  
  /**
   * Pseudo-abstract method to be over-ridden in subclasses.
   */
  function put_gallery($gallery) {
    return false;
  }
  
  /**
   * Pseudo-abstract method to be over-ridden in subclasses.
   */
  function get_users() {
    return array();
  }
  
  /**
   * Pseudo-abstract method to be over-ridden in subclasses.
   */
  function put_users($users) {
    return false;
  }
}

?>
