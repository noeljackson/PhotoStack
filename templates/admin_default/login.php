<div id="login_panel">
<h1>log in</h1>
<?php if($adminMessage) { echo '<div class="error"><p><em>'.$adminMessage.'</em></p></div>'; } ?>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<?php if(strstr($_SERVER['QUERY_STRING'],'action=') && $_SERVER['QUERY_STRING'] != 'action=logout') { ?>
	<input type="hidden" name="forward_url" value="<?php echo $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']; ?>" />
<?php } ?>
  <input type="hidden" name="action" value="login" />
  <p>
    <span class="large">Username:</span> <input type="text" name="username" />
  </p>
  <p>
    <span class="large">Password:</span> <input type="password" name="password" />
  <p>
    <input type="submit" class="button" value="Login" />
  </p>
</form>

</div>