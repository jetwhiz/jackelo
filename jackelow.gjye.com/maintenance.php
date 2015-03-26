<?
	
	// Pull in toolkits for all instances 
	require "headers.php";
	require "db.php";
	require "toolkit.php";
	require "error.php";
	require "user.php";
	require "auth.php";
	
	
	// Prepare databases
	try {
		$DBs = new Database();
	} catch (Error $e) {
		echo "ERROR: Cannot access database!";
		die;
	}
	////
	
	
	
	// Perform maintenance routines 
	$ret = true;
	$ret = $ret && clearDemoUsers($DBs);
	$ret = $ret && clearOldEvents($DBs);
	echo $ret;
	////
	
	
	
	// Perform DELETE for Users table (demo users) //
	function clearDemoUsers(&$DBs) {
		$delete = "
			DELETE FROM `Users` WHERE `demo` = 1
		";
		$binds = [];
		
		if ($GLOBALS["DEBUG"]) {
			print_r("\nBINDS\n");
			print_r($delete."\n");
			print_r($binds);
		}
		
		// Perform removal 
		$DBs->delete($delete, $binds);
		
		return true;
	}
	// * //
	
	
	
	// Perform DELETE for Events table (old events) //
	function clearOldEvents(&$DBs) {
		$delete = "
			DELETE FROM `Events` WHERE `datetimeEnd` < NOW()
		";
		$binds = [];
		
		if ($GLOBALS["DEBUG"]) {
			print_r("\nBINDS\n");
			print_r($delete."\n");
			print_r($binds);
		}
		
		// Perform removal 
		$DBs->delete($delete, $binds);
		
		return true;
	}
	// * //
	
?>