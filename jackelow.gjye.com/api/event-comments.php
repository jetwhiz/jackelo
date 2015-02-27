<?
	class EventComments {
		private $REST_vars;
		private $DBs;
		private $user;
		
		
		// CONSTRUCTOR //
		function __construct( $REST_vars, &$dbs, &$user ) {
			if ( is_null($dbs) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("ERR");
				}
				
				throw new Exception('EventComments Error: Database not supplied.');
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
			
			// Get comments
			
			// Specific commentID given (give full details of comment) 
			if ( $this->REST_vars["commentID"] ) {
				$select = "
						SELECT `eventID`, `ownerID`, `datetime`, `message`
						FROM `Comments` 
						WHERE `id` = ?
				";
				$binds = ["i", $this->REST_vars["commentID"]];
			}
			
			// No commentID given (return all comments for this eventID) 
			else {
				$select = "
						SELECT `id`
						FROM `Comments` 
						WHERE `eventID` = ?
				";
				$binds = ["i", $this->REST_vars["eventID"]];
			}
			
			$res = $this->DBs->select($select, $binds);
			$JSON = Toolkit::build_json(
											$this->DBs->select($select, $binds)
										);
			
			echo json_encode($JSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
		}
		// * // 
		
	}
?>