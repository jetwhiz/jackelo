<?
	class User {
		private $db;
		private $id;
		
		// Set up data structure //
		function __construct(&$db, $usrid) {
			if ( is_null($db) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("USR DB ERR");
				}
				
				throw new Exception('User Error: Could not establish connection.');
			}
			
			if ( is_null($usrid) || !is_numeric($usrid) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("USR ID ERR");
				}
				
				throw new Exception('User Error: Invalid user ID.');
			}
			
			
			// TODO: VERIFY USERID MATCHES W/ CURRENT SESSIONID 
			
			
			$this->id = $usrid;
			$this->handle = &$db;
		}
		// * //
		
		
		// Return current user ID //
		public function getID() {
			return $this->id;
		}
		// * //
		
	}
?>