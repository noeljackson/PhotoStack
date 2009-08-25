<h2>user management</h2>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="saveuser" />
<input type="hidden" name="username" value="<?php echo $_REQUEST["username"]; ?>" />
<?php
	//get users and set to var
  $users = $ps->io->get_users();
	//loop through to find user and...
  for($i=0; $i<count($users); $i++)
    if($users[$i]->username == $_REQUEST["username"]) {
		//set user to $user
      $user = $users[$i];
      break;
    } ?>

<p>  Username: <strong><?php echo $user->username; ?></strong></p>
<?php if($ps->is_admin()) { ?>
	
<p>Type
    <label for="TypeAdmin">
	<input type="radio" id="TypeAdmin" name="user_type" value="admin"<?php echo ($user->permissions && USER_ADMIN) ? ' checked="true"' : ''; ?> />Administrator</label>
	
   <label for="TypeUser"><input type="radio" id="TypeUser" name="user_type" value="user" <?php echo ($user->permissions && USER_ADMIN) ? '' : ' checked="true"'; ?>/>User</label></p>
  
	<?php if($ps->is_admin()) { ?>
<p>Email<input type="input" name="email" value="<?php echo $user->email; ?>" /></p>
<p>Full name<input type="input" name="fullname" value="<?php echo $user->fullname; ?>" /></p>
 <p>Description<input type="input" name="description" value="<?php echo $user->description; ?>" /></p>

  		<?php if($ps->is_admin()) { ?>
    		<p>Change Password<input type="input" name="password" value="" /></p>
<!-- <?php $var = explode("\r\n",chunk_split(md5(mt_rand()),8)); echo $var[1]; ?> -->
		<?php } ?>
	<?php } ?>
<?php } ?>
<p><input type="submit" class="button" value="Save Changes" /></p>

</form>
