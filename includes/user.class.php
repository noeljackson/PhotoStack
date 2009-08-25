<?php 
class user
{
  /**
   * Filename of the image if the image is local or the full URL of the image
   * if the image is remotely hosted.
   * @var string
   */
  var $username = "";

  /**
   * MD5 hash of password
   * @var string
   */
  var $userpass = "5f4dcc3b5aa765d61d8327deb882cf99";
  
  /**
   * Bit-field of permissions
   * @var int
   */
  var $permissions = 0;
  
  /**
   * Email address of user
   * @var string
   */
  var $email = "";
  
  /**
   * The name or title of the image
   * @var string
   */
  var $fullname = "";
  
  /**
   * Constructor forces username and userpass to have values
   */
  function user($username, $userpass, $permissions = '0')
  {
    $this->username = $username;
    $this->userpass = $userpass;
	$this->permissions = $permissions;
  }
}

?>
