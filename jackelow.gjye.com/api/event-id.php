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
			$res[1] = $this->DBs->select($select, $binds);
			
			
			$JSON = [];
			while ($row[1] = $res[1]->fetch_assoc()) {
				$obj[1] = [];
				$obj[1]["name"] = $row[1]['name'];
				$obj[1]["datetimeStart"] = $row[1]['datetimeStart'];
				$obj[1]["datetimeEnd"] = $row[1]['datetimeEnd'];
				$obj[1]["description"] = $row[1]['description'];
				$obj[1]["ownerID"] = $row[1]['ownerID'];
				$obj[1]["eventTypeID"] = $row[1]['eventTypeID'];
				
				
				// Get event destinations //
				$select = "
						SELECT `address`, `datetimeStart`, `datetimeEnd`, `cityID`, `countryID`
						FROM `EventDestinations` 
						WHERE `eventID` = ?
				";
				$binds = ["i", $this->REST_vars["eventID"]];
				$res[2] = $this->DBs->select($select, $binds);
				
				$obj[2] = [];
				while ($row[2] = $res[2]->fetch_assoc()) {
					
					if ( $this->REST_vars["simple"] == 1 ) {
						if ( in_array($row[2]['countryID'], $obj[2]) ) {
							continue;
						}
						
						$obj[2][] = $row[2]['countryID'];
					}
					else {
						$obj[3] = [];
						$obj[3]["address"] = $row[2]['address'];
						$obj[3]["datetimeStart"] = $row[2]['datetimeStart'];
						$obj[3]["datetimeEnd"] = $row[2]['datetimeEnd'];
						$obj[3]["cityID"] = $row[2]['cityID'];
						$obj[3]["countryID"] = $row[2]['countryID'];
						
						$obj[2][] = $obj[3];
					}
				}
				$obj[1]["destinations"] = $obj[2];
				// * // 
				
				
				// Get event categories //
				$select = "
						SELECT `categoryID`
						FROM `EventCategories` 
						WHERE `eventID` = ?
				";
				$binds = ["i", $this->REST_vars["eventID"]];
				$res[3] = $this->DBs->select($select, $binds);
				
				$obj[4] = [];
				while ($row[3] = $res[3]->fetch_assoc()) {
					$obj[4][] = $row[3]['categoryID'];
				}
				$obj[1]["categories"] = $obj[4];
				// * // 
				
				$JSON[] = $obj[1];
			}
			// * // 
			
			
			echo json_encode($JSON, JSON_PRETTY_PRINT) . "\n\n";
		}
		
	}
?>