<h2>user management</h2>

<?php if(!$ps->is_admin()) echo '<p>You must be an administrator to access this area.</p>'; ?>

<table class="editusers">
	<tr>
		<td width="125"><strong>UserName</strong></td>
		<td width="135"><strong>Email</strong></td>
		<td width="125"><strong>Full Name</strong></td>
		<td></td>		
	</tr>
<?php 
  $users = $ps->io->get_users();
  for($i=0; $i<count($users); $i++) {
    echo "<tr>\n  ";
    echo "<td>";
    if($users[$i]->permissions & USER_SUSPENDED) echo "<strike>"; 
    if($users[$i]->permissions & USER_ADMIN) echo "<u>"; 
    echo $users[$i]->username;
    if($users[$i]->permissions & USER_ADMIN) echo "</u>"; 
    if($users[$i]->permissions & USER_SUSPENDED) echo "</strike>";
    echo "</td>\n  "; 
    echo "<td>".$users[$i]->email."</td>\n  ";
    echo "<td>".$users[$i]->fullname."</td>\n  ";
    echo '<td><a href="'.$ps->format_admin_url("edituser", null, null, null, "&amp;username=".$users[$i]->username).'">edit</a></td>';
    echo '<td><a href="#"'."onclick=\"if(confirm('Are you sure you want to delete the user ".$users[$i]->username."')) window.location='".$ps->format_admin_url("deleteuser", null, null, null, "&amp;username=".$users[$i]->username)."'; return false;\">delete</a></td>";
    echo '<td><a href="'.$ps->format_admin_url("suspenduser", null, null, null, "&amp;username=".$users[$i]->username).'">'.($users[$i]->permissions & USER_SUSPENDED ? "unsuspend" : "suspend")."</a></td>\n";
    echo "</tr>\n";
  }

?>
</table>

<h2>create new user</h2>


<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="action" value="newuser" />
  <p>Username: <input type="input" name="username" /></p>
<?php if($ps->is_admin()) { ?>
	
<p>Type
    <label for="TypeAdmin">
	<input type="radio" id="TypeAdmin" name="user_type" value="admin" checked="true" />Administrator</label>
	
   <label for="TypeUser"><input type="radio" id="TypeUser" name="user_type" value="user" checked="true" />User</label></p>
  
	<?php if($ps->is_admin()) { ?>
 <p>Email: <input type="input" name="email" /></p>
 <p>Full name: <input type="input" name="fullname" /></p>
 <p>Description: <input type="input" name="description" /></p>

  		<?php if($ps->is_admin()) { ?>
    		<p>Password: <input type="input" name="password" value="" /></p>
		<?php } ?>
	<?php } ?>
<?php } ?>

<p><input type="submit" class="button" value="Save Changes" /></p>

</form>