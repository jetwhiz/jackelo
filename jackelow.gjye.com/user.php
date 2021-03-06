<?
	
	// Pull in toolkits for all instances 
	require_once "headers.php";
	require_once "error.php";
	
	class User {
		private $db;
		private $id;
		private $username;
		private $networkAbbr;
		private $networkName;
		private $sessionID;
		private $GPS;
		
		
		// Set up data structure //
		function __construct(&$db, $sessionID) {
			$this->db = &$db;
			$this->GPS = [];
			
			
			// A database object is required 
			if ( is_null($db) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("User Error: No database object passed\n");
				}
				
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "User Error: No database object passed.");
			}
			
			// Ensure we're given a sessionID 
			if ( is_null($sessionID) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("User Error: Invalid session ID\n");
				}
				
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "User Error: Invalid session ID.");
			}
			
			
			// Get userID from sessionID 
			$usrid = $this->sessionToUserID($sessionID);
			
			
			// If this sessionID not validly mapped to userID, throw exception 
			if ( is_null($usrid) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("User Error: Invalid user ID\n");
				}
				
				throw new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], "User Error: Invalid user ID.");
			}
			
			$this->id = $usrid;
			$this->sessionID = $sessionID;
		}
		// * //
		
		
		
		// Get location //
		public function getLocation() {
			return $this->GPS;
		}
		// * //
		
		
		
		// Validate user //
		private function sessionToUserID($sessionID) {
			if ( !$sessionID ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], "User Error: Invalid user ID.");
			}
			
			$select = "
				SELECT `Sessions`.`userID`, `Sessions`.`datetime`, `Users`.`username`, 
					`Networks`.`name_short` AS `networkAbbr`, `Networks`.`name` AS `network`
				FROM `Sessions` 
				INNER JOIN `Users` AS `Users`
					ON `Sessions`.`userID` = `Users`.`id`
				INNER JOIN `Networks` AS `Networks`
					ON `Networks`.`id` = `Users`.`networkID`
				WHERE `Sessions`.`id` = ?
				LIMIT 1
			";
			$binds = [];
			$binds[0] = "s";
			$binds[] = $sessionID;
			
			$res = $this->db->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "User Error: Database error.");
			}
			
			$row = $res->fetch_assoc();
			if ( !$row ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], "User Error: Cannot find session.");
			}
			
			// Ensure the userID is good 
			if ( !is_int( $row["userID"] ) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], "User Error: Invalid user ID from database.");
			}
			
			
			// TODO: Verify the session isn't too old? 
			
			
			// Set static fields
			$this->username = $row["username"];
			$this->networkAbbr = $row["networkAbbr"];
			$this->networkName = $row["network"];
			
			return $row["userID"];
		}
		// * //
		
		
		
		// Check if user is "premium" type //
		public function isPremium() {
			
			$select = "
				SELECT `premium`
				FROM `Users` 
				WHERE `id` = ?
				LIMIT 1
			";
			$binds = [];
			$binds[0] = "i";
			$binds[] = $this->getID();
			
			$res = $this->db->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "User Error: Database error.");
			}
			
			$row = $res->fetch_assoc();
			if ( !$row ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], "User Error: Cannot find user.");
			}
			
			// Ensure the value is good 
			if ( !is_int( $row["premium"] ) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], "User Error: Invalid user ID from database.");
			}
			
			return ( $row["premium"] == 1 );
		}
		// * //
		
		
		
		// Return network-username to display //
		public function getDisplayName() {
			$usrname = $this->getUsername();
			return $this->networkAbbr . "-" . $usrname;
		}
		// * //
		
		
		
		// Return current username //
		public function getUsername() {
			if ( $this->username == "" ) {
				$this->sessionToUserID($this->sessionID);
			}
			
			return $this->username;
		}
		// * // 
		
		
		
		// Return current user ID //
		public function getID() {
			return $this->sessionToUserID($this->sessionID);
		}
		// * //
		
	}
?>