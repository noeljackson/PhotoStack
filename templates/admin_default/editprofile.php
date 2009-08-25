<h2>my profile</h2>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<table class="formTable">
<input type="hidden" name="action" value="saveprofile" />
<input type="hidden" name="username" value="<?php echo $_SESSION["photostack_user"]->username; ?>" />
<?php 
  $users = $ps->io->get_users();
  for($i=0; $i<count($users); $i++)
    if($users[$i]->username == $_SESSION["photostack_user"]->username) {
      $user = $users[$i];
      break;
    }
?> 
<tr>
  <td>Username</td>
  <td><strong><?php echo $user->username; ?></strong></td>
</tr>
<tr>
  <td>Email</td>
  <td><input type="input" name="email" value="<?php echo $user->email; ?>" /></td>
</tr>
<tr>
  <td>Full name</td>
  <td><input type="input" name="fullname" value="<?php echo $user->fullname; ?>" /></td>
</tr>
  <tr><td>Description</td>
  <td><input type="input" name="description" value="<?php echo $user->description; ?>" /></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="Save Changes" /></td>
</tr>
</table>
</form>


<h2>change password</h2>
<p>Please choose a new password between 6 and 16 characters in length.</p>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="savepass" />
<input type="hidden" name="username" value="<?php echo $_SESSION['photostack_user']->username ?>" />
<table>
<tr>
  <td>Current password:</td>
  <td><input type="password" name="OldPass" size="23" /></td>
</tr>
<tr>
  <td>New password:</td>
  <td><input type="password" name="NewPass1" size="23" /></td>
</tr>
<tr>
  <td>Confirm password:</td>
  <td><input type="password" name="NewPass2" size="23" /></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="Save Changes" /></td>
</tr>
</table>
</form>