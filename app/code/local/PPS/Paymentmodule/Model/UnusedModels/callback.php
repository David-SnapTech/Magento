<?php
require 'oauth.php';
 
	$oauth_token=$_GET['oauth_token'];
	$oauth_verifier=$_GET['oauth_verifier'];
	$user_id=$_GET['user_id'];
	 
	$creds=getCreds();
	parse_str(getAccessToken($consumerSecret, $oauth_token,$creds['oauth_token_secret'],$links['access'][0],$links['access'][1],$params,$oauth_verifier),$data);
	echo $data['oauth_token']."<br />";
	echo $data['oauth_token_secret']."<br />";
 
 
?>