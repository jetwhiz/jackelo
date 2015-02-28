<?
	class Database {
		private $handle;
		
		
		// Set up data structure //
		function __construct() {
			$this->handle = [ "ro" => null, "rw" => null ];
		}
		// * //
		
		
		// Start Transaction (RW) //
		public function startTransaction() {
			$hnd = $this->connect("rw");
			if ( !$hnd ) { return false; }
			
			return $hnd->autocommit(false);
		}
		// * //
		
		
		// End Transaction (RW) //
		public function endTransaction() {
			$hnd = $this->connect("rw");
			if ( !$hnd ) { return false; }
			
			$hnd->commit();
			return $hnd->autocommit(true);
		}
		// * //
		
		
		// Abort Transaction (RW) //
		public function abortTransaction() {
			$hnd = $this->connect("rw");
			if ( !$hnd ) { return false; }
			
			$hnd->rollback();
			return $hnd->autocommit(true);
		}
		// * //
		
		
		// Are we connected (with given permission level) //
		private function isConnected($access) {
			return ( !is_null($this->handle[$access]) );
		}
		// * //
		
		
		// Connect with given permission level (or return handle if already connected) //
		private function connect($access) {
			
			// Check if connection already set up 
			if ( $this->isConnected($access) ) {
				return $this->handle[$access];
			}
			
			// Parse database configuration file 
			$ini_array = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../dbconf.ini", true);
			if (!$ini_array) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Database: Cannot access dbconf.ini\n");
				}
				
				return null;
			}
			
			// Pull appropriate username/password 
			if ($access == "ro") {
				$usr = $ini_array["ro"]["username"];
				$pass = $ini_array["ro"]["password"];
				$server = $ini_array["ro"]["server"];
				$database = $ini_array["ro"]["database"];
			}
			elseif($access == "rw") {
				$usr = $ini_array["rw"]["username"];
				$pass = $ini_array["rw"]["password"];
				$server = $ini_array["rw"]["server"];
				$database = $ini_array["rw"]["database"];
			}
			else {
				if ($GLOBALS["DEBUG"]) {
					print_r("Database: Invalid permission set requested\n");
				}
				
				return null;
			}
			
			// Connect with these permissions 
			$this->handle{$access} = new mysqli($server, $usr, $pass, $database);
			if (!$this->handle{$access}) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Failed to connect to MySQL\n");
				}
				
				return null;
			}
			if ($this->handle{$access}->connect_errno) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Failed to connect to MySQL: (" . $this->handle[$access]->connect_errno . ") " . $this->handle[$access]->connect_error . "\n");
				}
				
				return null;
			}
			
			// Set charset to uft8
			if (!$this->handle{$access}->set_charset("utf8")) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Failed to change MySQL charset: (" . $this->handle[$access]->error . ")\n");
				}
				
				return null;
			}
			
			// Return handle 
			return $this->handle[$access];
		}
		// * //
		
		
		// PHP's MySQLi sucks and requires us to pass binded params by reference //
		private function convertToRefs( $array ) {
			$a_params = [];
			
			for($i = 0; $i < count($array); $i++) {
				$a_params[] = & $array[$i];
			}
			
			return $a_params;
		}
		// * //
		
		
		// Wrapper for insert_id //
		public function insertID() {
			// RW permissions needed for insert_id 
			$hnd = $this->connect("rw");
			if ( !$hnd ) { return 0; }
			
			return $hnd->insert_id;
		}
		// * // 
		
		
		// Wrapper for INSERT statements (prepared-bound) //
		public function insert($INSERT_STR, $BINDS) {
			
			// RW permissions needed for INSERT statement 
			$hnd = $this->connect("rw");
			if ( !$hnd ) { return 0; }
			
			// Prepare statement 
			if (!($stmt = $hnd->prepare($INSERT_STR))) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Prepare failed: (" . $hnd->errno . ") " . $hnd->error . "\n");
				}
				
				return 0;
			}
			
			// Bind parameters to prepared statement (if available) 
			if ( count($BINDS) ) {
				if (!call_user_func_array(array($stmt, 'bind_param'), $this->convertToRefs($BINDS))) {
					if ($GLOBALS["DEBUG"]) {
						print_r("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error . "\n");
					}
					
					return 0;
				}
			}
			
			// Execute prepared statement 
			if (!$stmt->execute()) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Execute failed: (" . $stmt->errno . ") " . $stmt->error . "\n");
				}
				
				return 0;
			}
			
			// Returns number of inserted rows 
			$affected = $stmt->affected_rows;
			
			// Clean up after ourselves 
			$stmt->close();
			
			return $affected;
		}
		// * //
		
		
		// Wrapper for SELECT statements (prepared-bound) //
		public function select($SELECT_STR, $BINDS) {
			
			// Only read permissions necessary for SELECT statement 
			$hnd = $this->connect("ro");
			if ( !$hnd ) { return null; }
			
			// Prepare statement 
			if (!($stmt = $hnd->prepare($SELECT_STR))) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Prepare failed: (" . $hnd->errno . ") " . $hnd->error . "\n");
				}
				
				return null;
			}
			
			// Bind parameters to prepared statement (if available) 
			if ( count($BINDS) ) {
				if (!call_user_func_array(array($stmt, 'bind_param'), $this->convertToRefs($BINDS))) {
					if ($GLOBALS["DEBUG"]) {
						print_r("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error . "\n");
					}
					
					return null;
				}
			}
			
			// Execute prepared statement 
			if (!$stmt->execute()) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Execute failed: (" . $stmt->errno . ") " . $stmt->error . "\n");
				}
				
				return null;
			}
			
			// Get results 
			if (!($res = $stmt->get_result())) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Getting result set failed: (" . $stmt->errno . ") " . $stmt->error . "\n");
				}
				
				return null;
			}
			
			// return results 
			return $res;
		}
		// * //
		
	}
?>