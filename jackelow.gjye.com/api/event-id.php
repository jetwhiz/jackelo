<?
	class EventID {
		private $REST_vars;
		private $DBs;
		private $user;
		
		// CONSTRUCTOR //
		function __construct( $REST_vars, &$dbs, &$user ) {
			if ( is_null($dbs) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("EventID Error: Database not supplied\n");
				}
				
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "EventID Error: Database not supplied.");
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
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], "EventID Error: Request method not supported.");
			}
		}
		// * // 
		
		
		// GET //
		private function get() {
			
			// Get main event data 
			$select = "
					SELECT `name`, `datetimeStart`, `datetimeEnd`, `description`, `ownerID`, `eventTypeID` 
					FROM `Events` 
					WHERE `id` = ?
			";
			$binds = ["i", $this->REST_vars["eventID"]];
			
			$res = $this->DBs->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "EventID Error: Failed to retrieve request.");
			}
			
			$JSON = [];
			while ($row = $res->fetch_assoc()) {
				$obj = [];
				$obj["name"] = $row['name'];
				$obj["datetimeStart"] = $row['datetimeStart'];
				$obj["datetimeEnd"] = $row['datetimeEnd'];
				$obj["description"] = $row['description'];
				$obj["ownerID"] = $row['ownerID'];
				$obj["eventTypeID"] = $row['eventTypeID'];
				
				
				// Get event destinations 
				if ( $this->REST_vars["simple"] == 1 ) {
					$select = "
							SELECT `countryID`
							FROM `EventDestinations` 
							WHERE `eventID` = ?
					";
				}
				else {
					$select = "
							SELECT `address`, `datetimeStart`, `datetimeEnd`, `cityID`, `countryID`
							FROM `EventDestinations` 
							WHERE `eventID` = ?
					";
				}
				
				$binds = ["i", $this->REST_vars["eventID"]];
				
				$result = $this->DBs->select($select, $binds);
				if ( is_null($result) ) {
					throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "EventID Error: Failed to retrieve request.");
				}
				
				$obj["destinations"] = Toolkit::build_json($result);
				//// 
				
				
				// Get event categories 
				$select = "
						SELECT `categoryID`
						FROM `EventCategories` 
						WHERE `eventID` = ?
				";
				$binds = ["i", $this->REST_vars["eventID"]];
				
				$result = $this->DBs->select($select, $binds);
				if ( is_null($result) ) {
					throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "EventID Error: Failed to retrieve request.");
				}
				
				$obj["categories"] = Toolkit::build_json($result);
				//// 
				
				$JSON[] = $obj;
			}
			//// 
			
			
			echo json_encode($JSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
		}
		// * // 
		
	}
?>