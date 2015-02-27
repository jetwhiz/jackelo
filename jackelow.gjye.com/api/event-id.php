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
			
			// No results found for this eventID
			if ( $res->num_rows <= 0 ) {
				echo "[]\n";
				exit;
			}
			
			echo "[\n";
			echo "\t{\n"; 
			$separator = '';
			while ($row = $res->fetch_assoc()) {
				echo
					"$separator\t\t\"name\" : \"" . $row['name'] . "\",\n" . 
					"\t\t\"datetimeStart\" : \"" . $row['datetimeStart'] . "\",\n" . 
					"\t\t\"datetimeEnd\" : \"" . $row['datetimeEnd'] . "\",\n" . 
					"\t\t\"description\" : \"" . $row['description'] . "\",\n" . 
					"\t\t\"ownerID\" : " . $row['ownerID'] . ",\n" . 
					"\t\t\"eventTypeID\" : " . $row['eventTypeID'] . ",\n";
				$separator = ",\n";
			}
			// * // 
			
			
			// Get event destinations //
			$select = "
					SELECT `address`, `datetimeStart`, `datetimeEnd`, `cityID`, `countryID`
					FROM `EventDestinations` 
					WHERE `eventID` = ?
			";
			$binds = ["i", $this->REST_vars["eventID"]];
			$res = $this->DBs->select($select, $binds);
			
			echo "\t\t\"destinations\" : [\n";
			$separator = '';
			$destinations = [];
			while ($row = $res->fetch_assoc()) {
				if ( $this->REST_vars["simple"] == 1 ) {
					if ( in_array($row['countryID'], $destinations) ) {
						continue;
					}
					echo  "$separator\t\t\t" . $row['countryID'] . "";
					$separator = ",\n";
					$destinations[] = $row['countryID'];
				}
				else {
					echo 
						"$separator\t\t\t{\n" . 
						"\t\t\t\t\"address\" : \"" . $row['address'] . "\",\n" . 
						"\t\t\t\t\"datetimeStart\" : \"" . $row['datetimeStart'] . "\",\n" . 
						"\t\t\t\t\"datetimeEnd\" : \"" . $row['datetimeEnd'] . "\",\n" . 
						"\t\t\t\t\"cityID\" : " . $row['cityID'] . ",\n" . 
						"\t\t\t\t\"countryID\" : " . $row['countryID'] . "\n" . 
						"\t\t\t}";
					$separator = ",\n";
				}
			}
			echo "\n\t\t],\n";
			// * // 
			
			
			// Get event categories //
			$select = "
					SELECT `categoryID`
					FROM `EventCategories` 
					WHERE `eventID` = ?
			";
			$binds = ["i", $this->REST_vars["eventID"]];
			$res = $this->DBs->select($select, $binds);
			
			echo "\t\t\"categories\" : [\n";
			$separator = '';
			while ($row = $res->fetch_assoc()) {
				echo "$separator\t\t\t" . $row['categoryID'] . "";
				$separator = ",\n";
			}
			echo "\n\t\t]\n";
			// * // 
			
			
			
			echo "\t}\n";
			echo "]\n";
		}
		
	}
?>