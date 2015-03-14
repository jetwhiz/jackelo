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
					$this->put();
					break;
				case "post":
				default: 
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], get_class($this) . " Error: Request method not supported.");
			}
		}
		// * // 
		
		
		
		// EDIT EXISTING EVENT //
		protected function put() {
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nPUT-EventAll\n");
				print_r($GLOBALS["_PUT"]);
			}
			
			
			// Verify certain field lengths 
			if ( strlen($GLOBALS["_PUT"]["name"]) > $GLOBALS["MAX_LENGTHS"]["event_name"] ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Event name length too long!");
			}
			if ( strlen($GLOBALS["_PUT"]["description"]) > $GLOBALS["MAX_LENGTHS"]["event_description"] ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Event description length too long!");
			}
			
			
			// start transaction 
			if ( !$this->DBs->startTransaction() ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to begin transaction.");
			}
			
			
			
			// Get ownership info data //
			$select = "
					SELECT `Events`.`ownerID`
					FROM `Events` 
					WHERE `Events`.`id` = ?
					LIMIT 1
			";
			$binds = ["i", $this->REST_vars["eventID"]];
			
			$res = $this->DBs->select($select, $binds, true); // unsafe select (for transaction) 
			if ( is_null($res) ) {
				// Roll back transaction 
				$this->DBs->abortTransaction();
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
			}
			
			// Verify event ID exists 
			$row = $res->fetch_assoc();
			if ( !$row ) {
				// Roll back transaction 
				$this->DBs->abortTransaction();
				throw new Error($GLOBALS["HTTP_STATUS"]["Not Found"], get_class($this) . " Error: Failed to find event.");
			}
			
			// Verify current user is owner 
			if ( $this->User->getID() != $row['ownerID'] ) {
				// Roll back transaction 
				$this->DBs->abortTransaction();
				throw new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], get_class($this) . " Error: You are not the owner of this event.");
			}
			////
			
			// Verify given event date range is valid  
			if ( !Toolkit::daterange_valid($GLOBALS["_PUT"]["datetimeStart"], $GLOBALS["_PUT"]["datetimeEnd"]) ) {
				// Roll back transaction 
				$this->DBs->abortTransaction();
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Edit event failed (bad date range)!");
			}
			////
			
			
			
			
			// Perform UPDATE for Events table 
			$update = "
				UPDATE `Events` 
				SET `name` = ?, `datetimeStart` = ?, `datetimeEnd` = ?, `description` = ?, `eventTypeID` = ?
				WHERE `ownerID` = ? AND `id` = ?
			";
			
			$FLAGS = ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE;
			
			$binds = [];
			$binds[0] = "ssssiii";
			$binds[] = htmlspecialchars($GLOBALS["_PUT"]["name"], $FLAGS, "UTF-8");
			$binds[] = $GLOBALS["_PUT"]["datetimeStart"];
			$binds[] = $GLOBALS["_PUT"]["datetimeEnd"];
			$binds[] = htmlspecialchars($GLOBALS["_PUT"]["description"], $FLAGS, "UTF-8");
			$binds[] = intval($GLOBALS["_PUT"]["eventTypeID"], 10);
			$binds[] = $this->User->getID();
			$binds[] = $this->REST_vars["eventID"];
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nBINDS\n");
				print_r($update."\n");
				print_r($binds);
			}
			
			// Perform update (cannot do rows affected because there may be no changes) 
			$this->DBs->update($update, $binds);
			////
			
			
			
			// Get existing categories 
			$select = "
					SELECT `id`, `categoryID`
					FROM `EventCategories` 
					WHERE `eventID` = ?
			";
			$binds = ["i", $this->REST_vars["eventID"]];
			
			$res = $this->DBs->select($select, $binds, true); // unsafe select (for transaction) 
			if ( is_null($res) ) {
				// Roll back transaction 
				$this->DBs->abortTransaction();
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
			}
			
			// Remember which categories this event used to have (and their EventCategories ID) 
			$oldCategories = [];
			while ($row = $res->fetch_assoc()) {
				$oldCategories[] = [ "id" => $row['id'], "categoryID" => $row['categoryID'] ];
			}
			
			
			// Categories newly specified by user (may have overlap with old) 
			$newCategories = Toolkit::array_clean(array_unique(explode(",", $GLOBALS["_PUT"]["categoryID"])));
			
			
			// Scan list of old categories and find which ones are no longer chosen 
			$catToRemove = [];
			foreach ($oldCategories as $catE) {
				$in = false;
				
				foreach ($newCategories as $catN) {
					if ( $catN == $catE["categoryID"] ) {
						$in = true;
					}
				}
				
				if ( $in == false ) {
					// Perform DELETE for EventCategories table 
					$delete = "
						DELETE FROM `EventCategories` WHERE `id` = ? AND `eventID` = ?
						LIMIT 1
					";
					
					$binds = [];
					$binds[0] = "ii";
					$binds[] = $catE["id"];
					$binds[] = $this->REST_vars["eventID"];
					
					if ($GLOBALS["DEBUG"]) {
						print_r("\nBINDS\n");
						print_r($delete."\n");
						print_r($binds);
					}
					
					// Perform removal (and ensure row was removed) 
					$affected = $this->DBs->delete($delete, $binds);
					if ( !$affected ) {
						// Roll back transaction 
						$this->DBs->abortTransaction();
						throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Category removal failed!");
					}
					
					$catToRemove[] = $catE["categoryID"];
				}
			}
			
			
			// Scan list of new categories and find which ones are new 
			$catToAdd = [];
			foreach ($newCategories as $catN) {
				$in = false;
				
				foreach ($oldCategories as $catE) {
					if ( $catN == $catE["categoryID"] ) {
						$in = true;
					}
				}
				
				if ( $in == false ) {
					// Perform INSERT for EventCategories table 
					$insert = "
						INSERT INTO `EventCategories` (`eventID`, `categoryID`)
						VALUES (?, ?)
					";
					
					$binds = [];
					$binds[0] = "ii";
					$binds[] = $this->REST_vars["eventID"];
					$binds[] = $catN;
					
					if ($GLOBALS["DEBUG"]) {
						print_r("\nBINDS\n");
						print_r($insert."\n");
						print_r($binds);
					}
					
					// Perform insertion (and ensure row was inserted) 
					$affected = $this->DBs->insert($insert, $binds);
					if ( !$affected ) {
						// Roll back transaction 
						$this->DBs->abortTransaction();
						throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Add category failed!");
					}
					
					$catToAdd[] = $catN;
				}
			}
			
			if ($GLOBALS["DEBUG"]) {
				print_r("REMOVE: ");
				print_r($catToRemove);
				
				print_r("ADD: ");
				print_r($catToAdd);
			}
			////
			
			
			
			// Get existing destinations (order by id to ensure idempotence) 
			$select = "
					SELECT `id`
					FROM `EventDestinations` 
					WHERE `eventID` = ?
					ORDER BY `id`
			";
			$binds = ["i", $this->REST_vars["eventID"]];
			
			$res = $this->DBs->select($select, $binds, true); // unsafe select (for transaction) 
			if ( is_null($res) ) {
				// Roll back transaction 
				$this->DBs->abortTransaction();
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve destinations.");
			}
			
			// Remember which destinations this event used to have 
			$oldDestinationIDs = [];
			while ($row = $res->fetch_assoc()) {
				$oldDestinationIDs[] = $row['id'];
			}
			if ($GLOBALS["DEBUG"]) {
				print_r($oldDestinationIDs);
			}
			
			
			// Perform UPDATE/INSERT for EventDestinations table (replace old ones) 
			$oldID = 0;
			foreach ($GLOBALS["_PUT"]["destination"] as $destination) {
				
				// Verify date ranges are valid and within event start-end dates 
				if ( !Toolkit::daterange_bounded( $GLOBALS["_PUT"]["datetimeStart"], $GLOBALS["_PUT"]["datetimeEnd"], 
												$destination["datetimeStart"], $destination["datetimeEnd"] ) ) {
					// Roll back transaction 
					$this->DBs->abortTransaction();
					throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Edit destination failed (bad date range)!");
				}
				
				
				// Update existing 
				if ( $oldID < count($oldDestinationIDs) ) {
					$update = "
						UPDATE `EventDestinations` 
						SET `address` = ?, `datetimeStart` = ?, `datetimeEnd` = ?, `cityID` = ?, `countryID` = ?
						WHERE `id` = ? AND `eventID` = ? 
					";
					
					// Crop silently (don't tell user, shh!) 
					$dest_address = substr($destination["address"], 0, $GLOBALS["MAX_LENGTHS"]["destination_address"]);
					
					$binds = [];
					$binds[0] = "sssiiii";
					$binds[] = htmlspecialchars($dest_address, $FLAGS, "UTF-8");
					$binds[] = $destination["datetimeStart"];
					$binds[] = $destination["datetimeEnd"];
					$binds[] = intval($destination["cityID"], 10);
					$binds[] = intval($destination["countryID"], 10);
					$binds[] = $oldDestinationIDs[$oldID];
					$binds[] = $this->REST_vars["eventID"];
					
					if ($GLOBALS["DEBUG"]) {
						print_r("\nU-BINDS\n");
						print_r($update."\n");
						print_r($binds);
					}
					
					// Perform update (cannot do rows affected because there may be no changes) 
					$this->DBs->update($update, $binds);
				}
				
				// Otherwise there are more destinations than before (insert new ones) 
				else {
					$insert = "
						INSERT INTO `EventDestinations` (`eventID`, `address`, `datetimeStart`, `datetimeEnd`, `cityID`, `countryID`)
						VALUES (?, ?, ?, ?, ?, ?)
					";
					
					$binds = [];
					$binds[0] = "isssii";
					$binds[] = $this->REST_vars["eventID"];
					$binds[] = htmlspecialchars($destination["address"], $FLAGS, "UTF-8");
					$binds[] = $destination["datetimeStart"];
					$binds[] = $destination["datetimeEnd"];
					$binds[] = intval($destination["cityID"], 10);
					$binds[] = intval($destination["countryID"], 10);
					
					if ($GLOBALS["DEBUG"]) {
						print_r("\nI-BINDS\n");
						print_r($insert."\n");
						print_r($binds);
					}
					
					$affected = $this->DBs->insert($insert, $binds);
					if ( !$affected ) {
						// Roll back transaction 
						$this->DBs->abortTransaction();
						throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert destination failed!");
					}
				}
				
				
				// Proceed to next oldID 
				++$oldID;
			}
			
			// EventDestinations were removed (we have leftovers) -- DELETE 
			for ( ; $oldID < count($oldDestinationIDs); ++$oldID ) {
				// Perform DELETE for EventDestinations table 
				$delete = "
					DELETE FROM `EventDestinations` WHERE `id` = ? AND `eventID` = ?
					LIMIT 1
				";
				
				$binds = [];
				$binds[0] = "ii";
				$binds[] = $oldDestinationIDs[$oldID];
				$binds[] = $this->REST_vars["eventID"];
				
				if ($GLOBALS["DEBUG"]) {
					print_r("\nBINDS\n");
					print_r($delete."\n");
					print_r($binds);
				}
				
				// Perform removal (and ensure row was removed) 
				$affected = $this->DBs->delete($delete, $binds);
				if ( !$affected ) {
					// Roll back transaction 
					$this->DBs->abortTransaction();
					throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Destination removal failed!");
				}
				
			}
			////
			
			
			
			// Commit transaction
			if ( !$this->DBs->endTransaction() ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to commit transaction.");
			}
			////
			
			
			// Return updated eventID 
			$JSON = [
				"eventID" => $this->REST_vars["eventID"]
			];
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["OK"] );
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
							ORDER BY `EventDestinations`.`id`
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
							ORDER BY `EventDestinations`.`id`
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