<?
	class EventComments {
		private $REST_vars;
		private $DBs;
		private $user;
		
		
		// CONSTRUCTOR //
		function __construct( $REST_vars, &$dbs, &$user ) {
			if ( is_null($dbs) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("EventComments Error: Database not supplied\n");
				}
				
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "EventComments Error: Database not supplied.");
			}
			
			$this->REST_vars = $REST_vars;
			$this->DBs = &$dbs;
			$this->user = &$user;
			
			switch ( $REST_vars["method"] ) {
				case "get": 
					$this->get();
					break;
				case "put":
				case "post":
				case "delete":
				default: 
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], "EventComments Error: Request method not supported.");
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
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "EventComments Error: Failed to retrieve request.");
			}
			
			$JSON = Toolkit::build_json($res);
			
			echo json_encode($JSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
		}
		// * // 
		
	}
?>