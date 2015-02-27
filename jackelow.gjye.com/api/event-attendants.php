<?
	class EventAttendants {
		private $REST_vars;
		private $DBs;
		private $user;
		
		
		// CONSTRUCTOR //
		function __construct( $REST_vars, &$dbs, &$user ) {
			if ( is_null($dbs) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("ERR");
				}
				
				throw new Exception('EventAttendants Error: Database not supplied.');
			}
			
			$this->REST_vars = $REST_vars;
			$this->DBs = &$dbs;
			$this->user = &$user;
			
			switch ( $REST_vars["method"] ) {
				case "get": 
					$this->get();
					break;
				case "put":
					break;
				case "post":
					break;
				case "delete":
					break;
				default: 
					break;
			}
		}
		// * // 
		
		
		// GET //
		private function get() {
			
			// Get all attendants to event # eventID
			$select = "
					SELECT `userID`
					FROM `Attendants` 
					WHERE `eventID` = ?
			";
			$binds = ["i", $this->REST_vars["eventID"]];
			$res = $this->DBs->select($select, $binds);
			
			
			$JSON = Toolkit::build_json(
											$this->DBs->select($select, $binds)
										);
			
			echo json_encode($JSON, JSON_PRETTY_PRINT) . "\n\n";
		}
		// * // 
		
	}
?>