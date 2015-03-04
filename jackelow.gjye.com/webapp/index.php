<?
	
	// Pull in toolkits for all instances 
	require "../headers.php";
	require "../toolkit.php";
	require "../error.php";
	require "../user.php";
	require "../auth.php";
	
	
	
	// Prepare databases
	require "../db.php";
	try {
		$DBs = new Database();
	} catch (Error $e) {
		$e->kill();
	}
	////
	
	
	
	// Ensure user is logged in 
	$User = Authenticate::getUser();
	echo "<h1>Welcome, " . $User->getUsername() . "!</h1>";
	
	
?>
