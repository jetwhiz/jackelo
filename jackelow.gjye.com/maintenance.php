<?
	
	// Pull in toolkits for all instances 
	require_once "headers.php";
	require_once "db.php";
	
	class Maintenance {
		private $db;
		
		
		// Set up data structure //
		function __construct() {
			
			// Prepare databases
			try {
				$this->db = new Database();
			} catch (Error $e) {
				echo "ERROR: Cannot access database!";
				die;
			}
			////
	
		}
		// * //
		
		
		
		// Perform maintenance //
		public function run() {
			$ret = true;
			
			$ret = $ret && $this->clearDemoUsers();
			$ret = $ret && $this->clearOldEvents();
			
			return $ret;
		}
		// * //
		
		
		
		// Perform DELETE for Users table (demo users) //
		protected function clearDemoUsers() {
			$delete = "
				DELETE FROM `Users` 
				WHERE `demo` = 1 
					AND NOW() - `created` > ?
			";
			$binds = [];
			$binds[0] = "i";
			$binds[] = $GLOBALS["DEMOUSR_TO"];
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nBINDS\n");
				print_r($delete."\n");
				print_r($binds);
			}
			
			// Perform removal 
			$this->db->delete($delete, $binds);
			
			return true;
		}
		// * //
		
		
		
		// Perform DELETE for Events table (old events) //
		protected function clearOldEvents() {
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
			$this->db->delete($delete, $binds);
			
			return true;
		}
		// * //
		
	}
?>