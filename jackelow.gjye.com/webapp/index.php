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
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Jackelo - GTL Events Manager</title>
		
		<meta charset="utf-8" />
		
		<link type="text/css" rel="stylesheet" href="base.css" />
		
		<script type="text/javascript" src="jquery.js"></script>
	</head>
	<body>
		<div id="header"><h1>Jackelo</h1></div>
		<div id="content-body"><? echo "<h1>Welcome, " . $User->getUsername() . "!</h1>"; ?></div>
	</body>
</html>
