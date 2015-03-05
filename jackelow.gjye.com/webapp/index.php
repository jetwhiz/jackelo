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
		<link type="text/css" rel="stylesheet" href="index.css" />
		
		<script type="text/javascript" src="jquery.js"></script>
		<script type="text/javascript" src="index.js"></script>
	</head>
	<body>
		<div id="header"><h1>Jackelo</h1></div>
		<div id="content-body">
			<div id="wrapper">
				<? echo "<h1>Welcome, " . $User->getUsername() . "!</h1>"; ?>
				
				<div id="injection-point"></div>
				<div id="event-template">
					<div class="event-block">
						<div class="tr">
							<div class="thumb-cell"></div>
							<div class="info-cell">
								<div class="title-cell"></div>
								<div class="username-cell"></div>
								<div class="dates-cell"></div>
								<div class="tags-cell"></div>
							</div>
						</div>
						<div class="tr">
							<div class="td">
								&nbsp;
							</div>
							<div class="description-cell"></div>
						</div>
					</div>
					<br />
				</div>
			</div>
		</div>
	</body>
</html>
