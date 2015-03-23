<?
	
	// Pull in toolkits for all instances 
	require "../headers.php";
	require "../toolkit.php";
	require "../error.php";
	require "../user.php";
	require "../auth.php";
	
	
	// Detect if user is logged in already
	$Auth = new Authenticate(true);
	$User = $Auth->getUser();
	
	// Already logged in - redirect to mobile app 
	header("Location: jackelo://?sessionID=" . $_COOKIE["sessionID"]);
	die;
	
?>