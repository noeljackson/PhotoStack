<?php

class configuration
{
	function configuration($configFilePath = "config.php") {
		if(!$this->load_config($configFilePath)) {
		echo '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
		<head>
			<meta http-equiv="content-type" content="text/html; charset=utf-8" />
			<style>
			body {
				font-family: Verdana, Helvetica, Arial, sans-serif;
				background: white;
				font-size: 12px;
			}
			li {
				line-height: 1.75em;
			}
			#wrap {
				width: 600px;
				margin: 0px auto;
			}
			</style>
		</head>
		<body>
		<div id="wrap">
		<h3>Welcome to PhotoStack!</h3>
		<p>These are the steps you need to follow to use PhotoStack.</p>
		<ul>
		<li>Create a config file by copying <strong>config.sample.php</strong> to <strong>config.php</strong></li>
		<li>Login to your <a href="organize.php">Organization Page</a>. The username is <strong>admin</strong> and the password is <strong>admin</strong>. (You\'ll want to change this once you login.)</li>
		<li>Make sure your permissions are acceptable, and begin uploading photos!</li>
		<li>Have fun!</li>
		</ul>
		</div>
		</body>
		</html>
		';	
		}
	}
	
	function load_config($configFilePath) {

		if(!file_exists($configFilePath)) {
			return false;
		} else {
			
			$ini_values = parse_ini_file($configFilePath);
			
			// fix chmod_value
			strlen($ini_values['chmod_value']);
			
			// set if blank chmod value
			if($ini_values['chmod_value'] == null && strstr('config.php', $configFilePath)) {
				$ini_values['chmod_value'] = "0755";
			}		

			foreach($ini_values as $key => $value) {
				
				// AUTO BASEURL
				if($key == 'base_url' && $value == '') {
					$self = dirname($_SERVER['PHP_SELF']);
		    		($self == '/')  ? $base = $self : $base = $self.'/';
					$value = 'http://' . $_SERVER['HTTP_HOST'] . $base;
				}
			
				if($key == "chmod_value") {
					
				if(!preg_match('/0[0-9]*/', $value)) $value = "0755";
					#$pid = posix_getpwuid(posix_geteuid());
					#$pid = $pid['uid'];
					#fileowner($this->pathto_galleries);
				}
				$this->$key = $value;
			} 
			
			return true;
		
		}
		
	}
}

?>
