<?
	class User {
		private $db;
		private $id;
		private $sessionID;
		
		// Set up data structure //
		function __construct(&$db, $sessionID) {
			
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
			$this->handle = &$db;
		}
		// * //
		
		
		// Validate user //
		private function sessionToUserID($sessionID) {
			if ( !$sessionID ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], "User Error: Invalid user ID.");
			}
			
			// TODO: OBTAIN USERID FROM GIVEN SESSIONID 
			return 1;
		}
		// * //
		
		
		// Return current user ID //
		public function getID() {
			return $this->sessionToUserID($this->sessionID);
		}
		// * //
		
	}
?>