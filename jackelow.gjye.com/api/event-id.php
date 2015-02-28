<?
	class EventID {
		private $REST_vars;
		private $DBs;
		private $User;
		
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
			$this->User = &$user;
			
			switch ( $REST_vars["method"] ) {
				case "get": 
					$this->get();
					break;
				case "delete":
					$this->delete();
					break;
				case "put":
				case "post":
				default: 
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], "EventID Error: Request method not supported.");
			}
		}
		// * // 
		
		
		// DELETE //
		private function delete() {
			if ($GLOBALS["DEBUG"]) {
				print_r("DELETE-EventID\n");
			}
			
			// Perform DELETE for Events table 
			$delete = "
				DELETE FROM `Events` WHERE `id` = ? AND `ownerID` = ?
				LIMIT 1
			";
			
			$binds = [];
			$binds[0] = "ii";
			$binds[] = $this->REST_vars["eventID"];
			$binds[] = $this->User->getID();
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nBINDS\n");
				print_r($delete."\n");
				print_r($binds);
			}
			
			// Perform removal (and ensure row was removed) 
			$affected = $this->DBs->delete($delete, $binds);
			if ( !$affected ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], "EventID: Event removal failed!");
			}
			
			
			// Removal should cascade to all relevant tables 
			// Namely, EventDestinations, EventCategories, Comments, Attendants
			
			
			// Return? 
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
							ORDER BY `datetimeStart`
					";
				}
				else {
					$select = "
							SELECT `address`, `datetimeStart`, `datetimeEnd`, `cityID`, `countryID`
							FROM `EventDestinations` 
							WHERE `eventID` = ?
							ORDER BY `datetimeStart`
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