<?php

/**
 * Creates and caches a thumbnail of the specified size for the specified image.
 */

//require config class
require_once "includes/config.class.php";

if(get_magic_quotes_gpc()) 
  new Thumb(stripslashes($_REQUEST['gallery']),stripslashes($_REQUEST["image"]), $_REQUEST["width"], $_REQUEST["height"], isset($_REQUEST["force"]));
else
  new Thumb($_REQUEST['gallery'],$_REQUEST["image"], $_REQUEST["width"], $_REQUEST["height"], isset($_REQUEST["force"]));


class Thumb {

function Thumb($gallery, $image, $max_width, $max_height, $force_size) {
  //create config object
  $this->config = new configuration();
  
  //security check: make sure requested file is in galleries directory
  $gal_path = realpath($this->config->pathto_galleries);
  $img_path = realpath($this->config->pathto_galleries.$gallery."/".$image);
	$ext = strtolower(pathinfo($img_path, PATHINFO_EXTENSION));
  if(substr($img_path,0,strlen($gal_path)) != $gal_path) header("HTTP/1.0 404 Not Found");
	$extensions = explode("|", strtolower($this->config->recognised_extensions));
	$movie_extensions = explode("|", strtolower($this->config->movie_extensions));
	
if( !in_array($ext, $extensions) ){
  die("Specified file is not a recognised image file");
}  
if(in_array($ext, $movie_extensions)) $movie = true;

  	$image_path = realpath($this->config->pathto_galleries."$gallery/$image");
  	$thumb_path = $this->config->pathto_cache.$max_width."x".$max_height.($force_size?"f":"").strtr("-$gallery-$image",":/?\\","----");
	$movie_path = $this->config->pathto_cache.md5(strtr("-$gallery-$image",":/?\\","----")).'.flv';

	if(in_array($ext, $movie_extensions)) { $thumb_path = $thumb_path.'.jpg'; }
	
  	$imageModified = @filemtime($image_path);
  	$thumbModified = @filemtime($thumb_path);	
  	if($_GET['movie']) {
		$imageModified = @filemtime($movie_path);
		$thumb_path = $movie_path;
		header("Content-type: video/x-flv");
	} else {
		//send appropriate headers
  		switch($ext) {
    		case 'gif' : header("Content-type: image/gif"); break;
    		case 'png' : header("Content-type: image/png"); break;
    		default: header("Content-type: image/jpeg"); break;
		}
	}
	#header('Content-Disposition: inline; filename='.$_GET['image']);

  	//if thumbnail is newer than image then output cached thumbnail and exit
 	if($imageModified<$thumbModified) { 
    #	header("Last-Modified: ".gmdate("D, d M Y H:i:s",$thumbModified)." GMT");
    	readfile($thumb_path);
    	exit;
  	} else {
		$this->make_thumb($gallery, $image, $image_path, $thumb_path, $max_width, $max_height, $force_size, $movie, $movie_path);
	}

}




function make_thumb($gallery, $image, $image_path, $thumb_path, $max_width, $max_height, $force_size, $movie = null, $movie_path = null) {
	$this->config = new configuration();
	if($movie) {
		 include_once('includes/getid3/getid3.php');
		$getID3 = new getID3;
		$fileinfo = $getID3->analyze($image_path);
		getid3_lib::CopyTagsToComments($fileinfo);
		$image_width = $fileinfo['video']['resolution_x'];
		$image_height = $fileinfo['video']['resolution_y'];
	} else {
		$thumbQuality = $this->config->thumbnail_quality;
		list($image_width, $image_height, $image_type) = GetImageSize($image_path);
	}
	  //if aspect ratio is to be constrained set crop size
	  if($force_size) {

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

	  //set cropping offset
	  $cropX = floor(($image_width-$cropWidth)/2);
	  $cropY = floor(($image_height-$cropHeight)/2);

	  //compute width and height of thumbnail to create
	  if($cropWidth >= $max_width && ($cropHeight < $max_height || ($cropHeight > $max_height && round($cropWidth/$cropHeight * $max_height) > $max_width))) {
	    $thumbWidth = $max_width;
	    $thumbHeight = round($cropHeight/$cropWidth * $max_width);
	  } elseif($cropHeight >= $max_height) {
	    $thumbWidth = round($cropWidth/$cropHeight * $max_height);
	    $thumbHeight = $max_height;
	  } else {
	    //image is smaller than required dimensions so output it and exit
	    readfile($image_path);
	    exit;
	  }
	
	
	if($movie) {
		$thumbHeight = floor($thumbHeight/2) *2;
		$thumbWidth = floor($thumbWidth/2) *2;
		if($_GET['movie']) {
			exec($this->config->pathto_ffmpeg.' -i '.escapeshellarg($image_path).' -f flv '.escapeshellarg($movie_path));
			exec('/bin/cat '.escapeshellarg($movie_path).' | '.$this->config->pathto_flvtool.' -U stdin '.escapeshellarg($movie_path));
			readfile($movie_path);
			exit;
		}
		exec($this->config->pathto_ffmpeg.' -i '.escapeshellarg($image_path).' -s '.$thumbWidth.'x'.$thumbHeight.' -f mjpeg -t 1 -ss 3 '.escapeshellarg($thumb_path));
		readfile($thumb_path);
		exit;
	}
	  switch($this->config->thumbnail_software) {
	  case "im" : //use ImageMagick
	  	// hack for square thumbs;
	  	if(($thumbWidth == $thumbHeight) or $force_size) {
	  		$thumbsize = $thumbWidth;
			if($image_height > $image_width) {
				$cropY = -($thumbsize / 2);
				$cropX = 0;
				$thumbcommand = "{$thumbsize}x";
			} else {
				$cropY = -($thumbsize / 2);
				$cropX = 0;
				$thumbcommand = "x{$thumbsize}";
			}
	    } else {
	    	$thumbcommand = $thumbWidth.'x'.$thumbHeight;
	    }
	    $cmd  = '"'.$this->config->pathto_convert.'"';
	    if($force_size) $cmd .= " -gravity center -crop {$thumbWidth}x{$thumbHeight}!+0+0";
	    $cmd .= " -resize {$thumbcommand}";
	    if($image_type == 2) $cmd .= " -quality $thumbQuality";
	    if($this->config->progressive_thumbs) $cmd .= " -interlace Plane";
	    if($this->config->remove_jpeg_profile) $cmd .= ' +profile "*"';
	    $cmd .= ' '.escapeshellarg($image_path).' '.escapeshellarg($thumb_path);
	   exec($cmd);  
	   readfile($thumb_path);
	exit;
	    break;

	  case "gd2" :
	  default : //use GD by default
	    //read in image as appropriate type
	    switch($image_type) {
	      case 1 : $image = ImageCreateFromGIF($image_path); break;
	      case 3 : $image = ImageCreateFromPNG($image_path); break;
	      case 2 : 
	      default: $image = ImageCreateFromJPEG($image_path); break;
	    }

		 //create blank truecolor image
	    $thumb = ImageCreateTrueColor($thumbWidth,$thumbHeight);
	    //resize image with resampling
	    ImageCopyResampled(
	      $thumb,                    $image,
	      0,           0,            $cropX,     $cropY,
	      $thumbWidth, $thumbHeight, $cropWidth, $cropHeight);

	    //set image interlacing
	    ImageInterlace($thumb, $this->config->progressive_thumbs);

	    //output image of appropriate type
	    switch($image_type) {
	      case 1 :
	        //GIF images are output as PNG
	      case 3 :
	        ImagePNG($thumb); 
	        ImagePNG($thumb,$thumb_path); 
	        break;
	      case 2 :
	      default: 
	        ImageJPEG($thumb,"",$thumbQuality);
	        ImageJPEG($thumb,$thumb_path,$thumbQuality);
	        break;
	    }

	    ImageDestroy($image);
	    ImageDestroy($thumb);
	  }	
	}

}
?>
