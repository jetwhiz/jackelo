<?
	class EventAll extends Handler {
		protected $REST_vars;
		protected $DBs;
		protected $User;
		
		
		// RUN //
		public function run() {
			switch ( $this->REST_vars["method"] ) {
				case "get": 
					$this->get();
					break;
				case "post":
					$this->post();
					break;
				case "put":
				case "delete":
				default: 
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], get_class($this) . " Error: Request method not supported.");
			}
		}
		// * // 
		
		
		
		// POST NEW EVENT //
		protected function post() {
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nPOST-EventAll\n");
				print_r($_POST);
			}
			
			
			// Verify given event date range is valid  
			if ( !Toolkit::daterange_valid($_POST["datetimeStart"], $_POST["datetimeEnd"]) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert Event failed (bad date range)!");
			}
			
			
			// Verify certain field lengths 
			if ( strlen($_POST["name"]) > $GLOBALS["MAX_LENGTHS"]["event_name"] ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Event name length too long!");
			}
			if ( strlen($_POST["description"]) > $GLOBALS["MAX_LENGTHS"]["event_description"] ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Event description length too long!");
			}
			
			
			// Verify premium features
			if ( !$this->User->isPremium() && intval($_POST["eventTypeID"], 10) == $GLOBALS["EventTypes"]["Sponsored"] ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Premium features unavailable!");
			}
			
			
			// start transaction 
			if ( !$this->DBs->startTransaction() ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to begin transaction.");
			}
			
			
			// Perform INSERT for Events table 
			$insert = "
				INSERT INTO `Events` (`name`, `datetimeStart`, `datetimeEnd`, `description`, `ownerID`, `eventTypeID`)
				VALUES (?, ?, ?, ?, ?, ?)
			";
			
			$FLAGS = ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE;
			
			$binds = [];
			$binds[0] = "ssssii";
			$binds[] = htmlspecialchars($_POST["name"], $FLAGS, "UTF-8");
			$binds[] = $_POST["datetimeStart"];
			$binds[] = $_POST["datetimeEnd"];
			$binds[] = htmlspecialchars($_POST["description"], $FLAGS, "UTF-8");
			$binds[] = $this->User->getID();
			$binds[] = intval($_POST["eventTypeID"], 10);
			
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
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert Event failed!");
			}
			
			// Retrieve eventID for future reference 
			$eventID = $this->DBs->insertID();
			if ($GLOBALS["DEBUG"]) {
				print_r("INSERTID: " . $eventID . "\n");
			}
			if ( !$eventID ) {
				// Roll back transaction 
				$this->DBs->abortTransaction();
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert Event failed!");
			}
			
			
			// Perform INSERT for EventCategories table
			$categories = Toolkit::array_clean(array_unique(explode(",", $_POST["categoryID"])));
			foreach ($categories as $categoryID) {
				$insert = "
					INSERT INTO `EventCategories` (`eventID`, `categoryID`)
					VALUES (?, ?)
				";
				
				$binds = [];
				$binds[0] = "ii";
				$binds[] = $eventID;
				$binds[] = intval($categoryID, 10);
				
				if ($GLOBALS["DEBUG"]) {
					print_r("\nBINDS\n");
					print_r($insert."\n");
					print_r($binds);
				}
				
				$affected = $this->DBs->insert($insert, $binds);
				if ( !$affected ) {
					// Roll back transaction 
					$this->DBs->abortTransaction();
					throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert category failed!");
				}
			}
			
			
			// Perform INSERT for EventDestinations table 
			foreach ($_POST["destination"] as $destination) {
				
				
				// Verify date ranges are valid and within event start-end dates 
				if ( !Toolkit::daterange_bounded( $_POST["datetimeStart"], $_POST["datetimeEnd"], 
												$destination["datetimeStart"], $destination["datetimeEnd"] ) ) {
					// Roll back transaction 
					$this->DBs->abortTransaction();
					throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert destination failed (bad date range)!");
				}
				
				
				$insert = "
					INSERT INTO `EventDestinations` (`eventID`, `address`, `datetimeStart`, `datetimeEnd`, `cityID`)
					VALUES (?, ?, ?, ?, ?)
				";
				
				// Crop silently (don't tell user, shh!) 
				$dest_address = substr($destination["address"], 0, $GLOBALS["MAX_LENGTHS"]["destination_address"]);
				
				$binds = [];
				$binds[0] = "isssi";
				$binds[] = $eventID;
				$binds[] = htmlspecialchars($dest_address, $FLAGS, "UTF-8");
				$binds[] = $destination["datetimeStart"];
				$binds[] = $destination["datetimeEnd"];
				$binds[] = intval($destination["cityID"], 10);
				
				
				if ($GLOBALS["DEBUG"]) {
					print_r("\nBINDS\n");
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
			
			
			// Auto-attend your own event
			$insert = "
				INSERT INTO `Attendants` (`eventID`, `userID`)
				VALUES (?, ?)
			";
			
			$binds = [];
			$binds[0] = "ii";
			$binds[] = $eventID;
			$binds[] = $this->User->getID();
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nBINDS\n");
				print_r($insert."\n");
				print_r($binds);
			}
			
			$affected = $this->DBs->insert($insert, $binds);
			if ( !$affected ) {
				// Roll back transaction 
				$this->DBs->abortTransaction();
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert attendance failed!");
			}
			
			
			
			// Commit transaction
			if ( !$this->DBs->endTransaction() ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to commit transaction.");
			}
			
			
			// Return inserted eventID 
			$JSON = [
				"eventID" => $eventID
			];
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["Created"] );
		}
		// * //
		
		
		
		// GET EVENTS //
		protected function get() {
			
			// Get main event data 
			
			$select = "";
			$join = "";
			$where = "";
			$groupBy = "";
			$orderBy = "";
			$limit = "";
			$binds = null;
			
			
			
			// group by (country) -- cannot be combined with others 
			if ( $this->REST_vars["group"] ) {
				$select = "
					SELECT COUNT(*) AS `count`, `Cities`.`countryID`
					FROM `EventDestinations` 
					INNER JOIN `Events` AS `Events`
						ON `EventDestinations`.`eventID` = `Events`.`id`
					INNER JOIN `Cities` AS `Cities`
						ON `Cities`.`id` = `EventDestinations`.`cityID`
					WHERE `Events`.`eventTypeID` != ?
					GROUP BY `Cities`.`countryID`
					ORDER BY `count` DESC
				";
				
				$binds[0] .= "i";
				$binds[] = $GLOBALS["EventTypes"]["Info"];
				
				if ($GLOBALS["DEBUG"]) {
					print_r($select . "\n");
				}
				
				$result = $this->DBs->select($select, $binds);
				if ( is_null($result) ) {
					throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
				}
				$JSON = Toolkit::build_json($result);
				//// 
				
				
				$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["OK"] );
				
				return;
			}
			
			
			
			$select .= "
				SELECT `Events`.`id`
				FROM `Events` 
			";
			
			
			// filter by category 
			if ( $this->REST_vars["category"] ) {
				$join .= "
					INNER JOIN `EventCategories` AS `EventCategories`
						ON `Events`.`id` = `EventCategories`.`eventID`
						AND `EventCategories`.`categoryID` = ?
				";
				
				$binds[0] .= "i";
				$binds[] = $this->REST_vars["category"];
			}
			
			// filter by country id 
			if ( $this->REST_vars["country"] ) {
				$join .= "
					INNER JOIN `EventDestinations` AS `EventDestinations`
						ON `Events`.`id` = `EventDestinations`.`eventID`
					INNER JOIN `Cities` AS `Cities`
						ON `Cities`.`id` = `EventDestinations`.`cityID`
						AND `Cities`.`countryID` = ?
				";
				$groupBy .= "
					GROUP BY `Events`.`id`
				";
				
				$binds[0] .= "i";
				$binds[] = $this->REST_vars["country"];
			}
			
			// filter by type 
			if ( $this->REST_vars["type"] ) {
				$where .= "
					WHERE `Events`.`eventTypeID` = ?
				";
				
				$binds[0] .= "i";
				$binds[] = $this->REST_vars["type"];
			}
			else {
				$where .= "
					WHERE `Events`.`eventTypeID` != ?
				";
				
				$binds[0] .= "i";
				$binds[] = $GLOBALS["EventTypes"]["Info"];
			}
			
			
			// Sorting 
			$join .= "
				INNER JOIN (
					SELECT COUNT(*) AS `ATT-CNT`
					FROM `Attendants` 
					WHERE `Attendants`.`eventID` = `id`
				) AS `Attendants`
			";
			$orderBy .= "
				ORDER BY `Events`.`datetimeStart`, `Attendants`.`ATT-CNT`, `Events`.`name`
			";
			// NOTE: Sort by location performed AFTER results pulled from database 
			
			
			// limit number of results  
			$numResults = $GLOBALS["NUM_RESULTS"];
			if ( $this->REST_vars["limit"] > 0 ) {
				$numResults = $this->REST_vars["limit"];
			}
			// limit results (with offset if given) 
			$offsetResults = 0;
			if ( $this->REST_vars["start"] > 0 ) {
				$offsetResults = $this->REST_vars["start"];
			}
			$limit .= "
				LIMIT " . $offsetResults . ", " . $numResults . "
			";
			
			
			$prepared = "$select$join$where$groupBy$orderBy$limit";
			
			if ($GLOBALS["DEBUG"]) {
				print_r($prepared . "\n");
			}
			
			$result = $this->DBs->select($prepared, $binds);
			if ( is_null($result) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
			}
			$JSON = Toolkit::build_json($result);
			//// 
			
			
			// TODO: Sort by location
			
			
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["OK"] );
		}
		// * // 
		
	}
?>