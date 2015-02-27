<?
	class EventID {
		private $REST_vars;
		private $DBs;
		
		function __construct( $REST_vars, $dbs ) {
			if ( is_null($dbs) ) {
				echo "ERR";
			}
			
			$this->REST_vars = $REST_vars;
			$this->DBs = $dbs;
			
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
		
		
		private function get() {
			
			// Get main event data // 
			$select = "
					SELECT `name`, `datetimeStart`, `datetimeEnd`, `description`, `ownerID`, `eventTypeID` 
					FROM `Events` 
					WHERE `id` = ?
			";
			$binds = ["i", $this->REST_vars["eventID"]];
			$res = $this->DBs->select($select, $binds);
			
			
			$JSON = [];
			while ($row = $res->fetch_assoc()) {
				$obj = [];
				$obj["name"] = $row['name'];
				$obj["datetimeStart"] = $row['datetimeStart'];
				$obj["datetimeEnd"] = $row['datetimeEnd'];
				$obj["description"] = $row['description'];
				$obj["ownerID"] = $row['ownerID'];
				$obj["eventTypeID"] = $row['eventTypeID'];
				
				
				// Get event destinations //
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
				
				$obj["destinations"] = Toolkit::build_json(
											$this->DBs->select($select, $binds)
										);
				// * // 
				
				
				// Get event categories //
				$select = "
						SELECT `categoryID`
						FROM `EventCategories` 
						WHERE `eventID` = ?
				";
				$binds = ["i", $this->REST_vars["eventID"]];
				
				$obj["categories"] = Toolkit::build_json(
											$this->DBs->select($select, $binds)
										);
				// * // 
				
				$JSON[] = $obj;
			}
			// * // 
			
			
			echo json_encode($JSON, JSON_PRETTY_PRINT) . "\n\n";
		}
		
	}
?>