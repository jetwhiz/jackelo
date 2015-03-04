<?
	
	// Pull in toolkits for all instances 
	require "../headers.php";
	require "../db.php";
	require "../toolkit.php";
	require "../error.php";
	require "../user.php";
	require "../auth.php";
	
	
	
	// Prepare databases
	try {
		$DBs = new Database();
	} catch (Error $e) {
		$e->kill();
	}
	////
	
	
	
	// Ensure user is logged in 
	$Auth = new Authenticate($DBs);
	$User = $Auth->getUser();
	echo "<h1>Welcome, " . $User->getUsername() . "!</h1>";
	
	
?>
