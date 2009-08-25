<h2><?php echo $confirmTitle ?></h2>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="previous" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
<?php 
  foreach($_REQUEST as $name => $value)
    echo "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
?>
<p><?php echo $confirmMessage ?></p>

<p>
<input type="submit" class="button" name="confirmed" value="OK">
<input type="submit" class="button" name="confirmed" value="Cancel">
</p>
</form>
