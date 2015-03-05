<?
	class EventID extends Handler {
		protected $REST_vars;
		protected $DBs;
		protected $User;
		
		
		// RUN //
		public function run() {
			switch ( $this->REST_vars["method"] ) {
				case "get": 
					$this->get();
					break;
				case "delete":
					$this->delete();
					break;
				case "put":
				case "post":
				default: 
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], get_class($this) . " Error: Request method not supported.");
			}
		}
		// * // 
		
		
		
		// DELETE //
		protected function delete() {
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
				throw new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], get_class($this) . ": Event removal failed!");
			}
			
			
			// Removal should cascade to all relevant tables 
			// Namely: EventDestinations, EventCategories, Comments, Attendants
			
			
			$JSON = [];
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["OK"] );
		}
		// * //
		
		
		
		// GET //
		protected function get() {
			
			// Get main event data 
			$select = "
					SELECT `Events`.`name`, `Events`.`datetimeStart`, `Events`.`datetimeEnd`, 
						`Events`.`description`, `Events`.`ownerID`, `Users`.`username`,
						`Events`.`eventTypeID`, `EventTypes`.`name` AS `eventType`
					FROM `Events` 
					INNER JOIN `EventTypes` AS `EventTypes`
						ON `EventTypes`.`id` = `Events`.`eventTypeID`
					INNER JOIN `Users` AS `Users`
						ON `Users`.`id` = `Events`.`ownerID`
					WHERE `Events`.`id` = ?
			";
			$binds = ["i", $this->REST_vars["eventID"]];
			
			$res = $this->DBs->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
			}
			
			$JSON = [];
			while ($row = $res->fetch_assoc()) {
				$obj = [];
				$obj["name"] = $row['name'];
				$obj["datetimeStart"] = $row['datetimeStart'];
				$obj["datetimeEnd"] = $row['datetimeEnd'];
				$obj["ownerID"] = $row['ownerID'];
				$obj["username"] = $row['username'];
				
				// Hide non-simple elements 
				if ( $this->REST_vars["simple"] != 1 ) {
					$obj["description"] = $row['description'];
					$obj["eventTypeID"] = $row['eventTypeID'];
					$obj["eventType"] = $row['eventType'];
				}
				
				// Get event destinations 
				if ( $this->REST_vars["simple"] == 1 ) {
					$select = "
							SELECT `EventDestinations`.`cityID`, `Cities`.`name` AS `cityName`, 
								`EventDestinations`.`countryID`, `Countries`.`name` AS `countryName`,
								CONCAT(?, `Cities`.`thumb`, '.thumb.jpg') AS `thumb`,
								CONCAT(?, `Cities`.`thumb`) AS `img`
							FROM `EventDestinations` 
							INNER JOIN `Cities` AS `Cities` ON `Cities`.`id` = `EventDestinations`.`cityID`
							INNER JOIN `Countries` AS `Countries` ON `Countries`.`id` = `EventDestinations`.`countryID`
							WHERE `EventDestinations`.`eventID` = ?
							ORDER BY `EventDestinations`.`datetimeStart`
					";
				}
				else {
					$select = "
							SELECT `EventDestinations`.`address`, `EventDestinations`.`datetimeStart`, 
								`EventDestinations`.`datetimeEnd`, 
								`EventDestinations`.`cityID`, `Cities`.`name` AS `cityName`, 
								`EventDestinations`.`countryID`, `Countries`.`name` AS `countryName`,
								CONCAT(?, `Cities`.`thumb`, '.thumb.jpg') AS `thumb`,
								CONCAT(?, `Cities`.`thumb`) AS `img`
							FROM `EventDestinations` 
							INNER JOIN `Cities` AS `Cities` ON `Cities`.`id` = `EventDestinations`.`cityID`
							INNER JOIN `Countries` AS `Countries` ON `Countries`.`id` = `EventDestinations`.`countryID`
							WHERE `EventDestinations`.`eventID` = ?
							ORDER BY `EventDestinations`.`datetimeStart`
					";
				}
				
				$binds = ["ssi", $GLOBALS["IMG_DIR"], $GLOBALS["IMG_DIR"], $this->REST_vars["eventID"]];
				
				$result = $this->DBs->select($select, $binds);
				if ( is_null($result) ) {
					throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
				}
				
				$obj["destinations"] = Toolkit::build_json($result);
				//// 
				
				
				// Get event categories (non-simple) 
				if ( $this->REST_vars["simple"] != 1 ) {
					$select = "
							SELECT `EventCategories`.`categoryID`, `Categories`.`name`
							FROM `EventCategories` 
							INNER JOIN `Categories` AS `Categories` 
								ON `Categories`.`id` = `EventCategories`.`categoryID`
							WHERE `EventCategories`.`eventID` = ?
					";
					$binds = ["i", $this->REST_vars["eventID"]];
					
					$result = $this->DBs->select($select, $binds);
					if ( is_null($result) ) {
						throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
					}
					
					$obj["categories"] = Toolkit::build_json($result);
				}
				//// 
				
				$JSON[] = $obj;
			}
			//// 
			
			
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["OK"] );
		}
		// * // 
		
	}
?>