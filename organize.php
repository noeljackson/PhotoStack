<?php
/**
 * Admin interface file.
 * Checks the selected 'action', checks user permissions, calls the appropriate 
 * methods and sets the required include file. Finally it includes the admin 
 * template's index file.
 */

//include main class
require_once "includes/photostack.class.php";
require_once "includes/admin.class.php";

//create the admin object
$ps = new admin();

if($_REQUEST['session_id']) session_id($_REQUEST['session_id']);
session_start();

//check if user is logged in
if($ps->is_logged_in() or $ps->action == "login") 
  //choose which file to include and/or perform admin actions
  switch($ps->action) {
    case "newgallery" :
      $ps->select_gallery();
      if($ps->new_gallery()) {
        $ps->select_gallery($ps->gallery->id."/".$_REQUEST["newgallery"]);
        $adminMessage = 'Gallery added, Would you like to <strong><a href="'.$ps->format_admin_url("upload",$ps->gallery->id_encoded).'">upload</a></strong> some images?"';
        $includeFile = "view";
      } else {
        $adminMessage = "An error occurred:"." ".$ps->last_error();
		  $includeFile = "view";
      }
      break;
	case "upload" :
		$ps->select_gallery();
		$includeFile = "upload";
	break;
	
	case "uploadsend" :
		$ps->select_gallery();
		if($ps->upload_bulk($_FILES)) {
		  $adminMessage = "Image added. ";
		} else {
		  $adminMessage = "An error occurred:"." ".$ps->last_error(); 
		}
		$includeFile = "view";
	break;
		
	case "updategallery" :

		$ps->select_gallery();
		if($ps->reorder_gallery()) {
		
		}
	break;
        
    case "deletegallery" :
$ps->select_gallery();
      if(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"] == true || (count($ps->gallery->images)==0 && count($ps->gallery->galleries)==0)) {
        if($ps->delete_gallery()) {
          $ps->select_gallery(rawurldecode($ps->gallery->parent));
          $adminMessage = "Gallery deleted.";
        } else {
			
          $adminMessage = "An error occurred: We could not delete this gallery, perhaps it doesn't exist, or the permissions aren't correct?";
        }
        $includeFile = "view";
      }
      break;
    case "deleteimage" :
   	$ps->select_gallery();
      if(isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"]==true) {	
        if($ps->delete_image())
          $adminMessage = "Image deleted";
        else
          $adminMessage = "An error occurred:"." ".$ps->last_error();
      } 
        $includeFile = "view";		
      break;
      case "rotateimage" :
      $ps->select_gallery();
      if(isset($_REQUEST["degree"]) && $_REQUEST["degree"]==true) {	
        if($ps->rotate_image(null, $_REQUEST['degree']))
          $adminMessage = "Image rotated";
        else
          $adminMessage = "An error occurred:"." ".$ps->last_error();
        
        $ps->select_gallery();
      } 
      $includeFile = "view";
      break;
      
	case "bulk" :
	    $ps->select_gallery();
	    switch ($_REQUEST['function']) {
	        case 'delete':
		    if(!$_REQUEST['images']) {
                $adminMessage = "An error occurred: "."You didn't select any images for deletion.";
		    } else {
	            if($_REQUEST['images'] && $ps->delete_bulk($_REQUEST['images'])) {
		    		$count = count($_REQUEST['images']);
	              $adminMessage = $count." image(s) deleted";
	            } else {
	              $adminMessage = "An error occurred:"." ".$ps->last_error();
		    	}
	        }
	        	
	        break;
	        case 'rotateright':
	        if(!$_REQUEST['images']) {
	            $adminMessage = "An error occurred: "."You didn't select any images for rotation.";
	        } else {
	            if($_REQUEST['images'] && $ps->rotate_bulk($_REQUEST['images'], 270)) {
	                $count = count($_REQUEST['images']);
	                $adminMessage = $count." image(s) rotated";
	            } else {
	                $adminMessage = "An error occurred:"." ".$ps->last_error();
	            }
	        }
	        break;
	        case 'rotateleft':
	        if(!$_REQUEST['images']) {
	            $adminMessage = "An error occurred: "."You didn't select any images for rotation.";
	        } else {
	            if($_REQUEST['images'] && $ps->rotate_bulk($_REQUEST['images'], 90)) {
	                $count = count($_REQUEST['images']);
	                $adminMessage = $count." image(s) rotated";
	            } else {
	                $adminMessage = "An error occurred:"." ".$ps->last_error();
	            }
	        }
	        break;
        }
        $ps->select_gallery();
        $includeFile = "view";	
	    break;
    case "deleteuser" :
      if(!$ps->is_admin()) {
        $adminMessage = "You do not have permission to perform this operation.";
        $includeFile = "view";
      } else {
        if($ps->delete_user())
          $adminMessage = "User deleted";
        else
          $adminMessage = "An error occurred:"." ".$ps->last_error();
      }
      $includeFile = "manageusers";
      break;
    case "editprofile" :
        $includeFile = "editprofile";
      break;
    case "edituser" :
      if(!$ps->is_admin() && $_REQUEST["username"] != $_SESSION["photostack_user"]->username) {
        $adminMessage = "You do not have permission to perform this operation.";
		  $ps->select_gallery();
        $includeFile = "view";
      } else
        $includeFile = "edituser";
      break;
    case "login" :
      if($ps->login()) {
		if($_POST['forward_url']) header("Location: ".$_POST['forward_url']);
		$ps->select_gallery($_POST['gallery']);
        $adminMessage = "Welcome to Organize";
        $includeFile = "view";
      } else {
        $adminMessage = $ps->last_error();
        $includeFile = "login";
      }
      break;
    case "logout" :
      $ps->logout();
      $adminMessage = "Thank you and goodbye!";
      $includeFile = "login";
      break;
    case "manageusers" :
      if(!$ps->is_admin()) {
			$ps->select_gallery();
        $adminMessage = "You do not have permission to perform this operation.";
        $includeFile = "view";
      } else
        $includeFile = "manageusers";
      break;
    case "newuser" :
      if(!$ps->is_admin()) {
		$ps->select_gallery();
        $adminMessage = "You do not have permission to perform this operation.";
        $includeFile = "view";
      } elseif($ps->save_user()) {
        $adminMessage = "New user added successfully.";
        $includeFile = "manageusers";
      } else {
        $adminMessage = "An error occurred:"." ".$ps->last_error();
        $includeFile = "manageusers";
      }
      break;
    case "purgecache" :
      if(!$ps->is_admin()) {
        $adminMessage = "You do not have permission to perform this operation.";
		$ps->select_gallery();
        $includeFile = "view";
      } else {
        if($ps->purge_cache()) {
            $adminMessage = "Thumbnail cache purged";
			$ps->select_gallery();
			$includeFile = "view";
        } else {
            $adminMessage = "An error occurred:"." ".$ps->last_error();
            $includeFile = "view";
		}
      }
      break;
      case "generate" :
      	if(!$ps->is_admin()) {
              $adminMessage = "You do not have permission to perform this operation.";
              $includeFile = "view";
        } else {
            if($ps->generate_thumbs($ps,'.')) {
                $adminMessage = "All Thumbnails regenerated";
                $ps->select_gallery();
                $includeFile = "view";
            } else {
                $adminMessage = "An error occurred:"." ".$ps->last_error();
              	$includeFile = "view";
			}
        }
       break;
		case "savegallery" :
		 	$ps->select_gallery();
	      /*if($ps->save_gallery()) {
		
				echo str_replace('&amp;', '&',$msg);
			} else {
				$adminMessage = "An error occurred:"." ".$ps->last_error();
			}*/
			$ps->save_gallery();
			break;
    case "saveimage" :
      $ps->select_gallery();
      if($ps->save_image()) {
        $adminMessage = "Image info saved";
        $includeFile = "view";
      } else {
        $adminMessage = "An error occurred:"." ".$ps->last_error();
        $includeFile = "view";
      }
      break;
    case "savepass" :
      if($ps->save_pass()) {
        $adminMessage = "Password saved";
		$ps->select_gallery();
        $includeFile = "view";
      } else {
        $adminMessage = "An error occurred:"." ".$ps->last_error();
			$ps->select_gallery();
        $includeFile = "editpass";
      }
      break;
    case "savepermissions" :
      $ps->select_gallery();
      if(!$ps->is_admin()) {
        $adminMessage = "You do not have permission to perform this operation.";
        $includeFile = "view";
      } elseif($ps->savePermissions()) {
        $adminMessage = "Permissions saved";
        $includeFile = "view";
      } else {
        $adminMessage = "An error occurred:"." ".$ps->last_error();
        $includeFile = "editpermissions";
      }
      break;
    case "saveprofile" :
      if($_REQUEST["username"] != $_SESSION["photostack_user"]->username) {
        $adminMessage = "You do not have permission to perform this operation.";
$ps->select_gallery();
        $includeFile = "view";
      } elseif($ps->save_user()) {
	$ps->select_gallery();
        $adminMessage = "User info saved";
        $includeFile = "view";
      } else {
        $adminMessage = "An error occurred:"." ".$ps->last_error();
        $includeFile = "editprofile";
      }
      break;
    case "saveuser" :
      if(!$ps->is_admin()) {
        $adminMessage = "You do not have permission to perform this operation.";
        $includeFile = "view";
      } elseif($ps->save_user()) {
        $adminMessage = "User info saved";
        $includeFile = "manageusers";
      } else {
        $adminMessage = "An error occurred:"." ".$ps->last_error();
        $includeFile = "edituser";
      }
      break;
    case "suspenduser" :
      if(!$ps->is_admin()) {
        $adminMessage = "You do not have permission to perform this operation.";
        $includeFile = "view";
      } elseif($ps->suspend_user())
        $adminMessage = "User info saved";
      else
        $adminMessage = "An error occurred:"." ".$ps->last_error();
      $includeFile = "manageusers";
      break;
    case "settings" :
    	if(!$ps->is_admin()) {
    	    $adminMessage = "You do not have permission to perform this operation.";
			$ps->select_gallery();
    	    $includeFile = "view";
    	} else {
    	    $includeFile = "settings";
    	}
    break;
    
    case "config" :
        if(!$ps->is_admin()) {
            $adminMessage = "You do not have permission to perform this operation.";
            
			$includeFile = "view";
        } else {
            if($ps->save_config()) {
                $adminMessage = "Successful config save.";
            } else {
                $adminMessage = "An error occurred:"." ".$ps->last_error();
            }
			// load the new config
            $ps->config->load_config('config.php');
			
            $includeFile = "settings";          
        }
    break;
    case "view" :
      $ps->select_gallery();
      $includeFile = "view";
      break;
    default :
	$ps->select_gallery();
      $includeFile = "view";
  }
else //not logged in
  $includeFile = "login";
  
if(@$includeFile) {
//pass control over to template
include $ps->config->pathto_admin_template."index.php";
}
?>