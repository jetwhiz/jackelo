<?
	
	// Pull in toolkits for all instances 
	require "auth.php";
	require "../headers.php";
	require "../toolkit.php";
	require "../error.php";
	require "../user.php";
	
	
	
	// Prepare databases
	require "../db.php";
	try {
		$DBs = new Database();
	} catch (Error $e) {
		$e->kill();
	}
	////
	
	
	
	// Ensure user is logged in 
	$User = Authenticate::assert_login();
	echo "<h1>Welcome, " . $User->getUsername() . "!</h1>";
	
	
?>
