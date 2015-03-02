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
			$categories = Toolkit::array_clean(explode(",", $_POST["categoryID"]));
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
				$insert = "
					INSERT INTO `EventDestinations` (`eventID`, `address`, `datetimeStart`, `datetimeEnd`, `cityID`, `countryID`)
					VALUES (?, ?, ?, ?, ?, ?)
				";
				
				$binds = [];
				$binds[0] = "isssii";
				$binds[] = $eventID;
				$binds[] = htmlspecialchars($destination["address"], $FLAGS, "UTF-8");
				$binds[] = $destination["datetimeStart"];
				$binds[] = $destination["datetimeEnd"];
				$binds[] = intval($destination["cityID"], 10);
				$binds[] = intval($destination["countryID"], 10);
				
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
			$binds = null;
			
			
			
			// group by (country) -- cannot be combined with others 
			if ( $this->REST_vars["group"] ) {
				$select = "
					SELECT COUNT(*) AS `count`, `countryID`
					FROM `EventDestinations` 
					GROUP BY `countryID`
				";
				
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
			
			// filter by country id (cannot be used with group) 
			if ( $this->REST_vars["country"] && !$this->REST_vars["group"] ) {
				$join .= "
					INNER JOIN `EventDestinations` AS `EventDestinations`
						ON `Events`.`id` = `EventDestinations`.`eventID`
						AND `EventDestinations`.`countryID` = ?
				";
				$groupBy .= "
					GROUP BY `Events`.`id`
				";
				
				$binds[0] .= "i";
				$binds[] = $this->REST_vars["country"];
			}
			
			// filter by type 
			if ( $this->REST_vars["show"] ) {
				$where .= "
					WHERE `Events`.`eventTypeID` = ?
				";
				
				$binds[0] .= "i";
				$binds[] = $this->REST_vars["show"];
			}
			else {
				$where .= "
					WHERE `Events`.`eventTypeID` != ?
				";
				
				$binds[0] .= "i";
				$binds[] = $GLOBALS["EventTypes"]["Info"];
			}
			
			
			// TODO: sort by location  
			if ( $this->REST_vars["sort"] == $GLOBALS["SortTypes"]["Location"] ) {
				$orderBy .= "
					ORDER BY `Events`.`datetimeStart`
				";
			}
			
			// sort by popularity 
			elseif ( $this->REST_vars["sort"] == $GLOBALS["SortTypes"]["Popularity"] ) {
				$join .= "
					INNER JOIN (
						SELECT COUNT(*) AS `ATT-CNT`
						FROM `Attendants` 
						WHERE `Attendants`.`eventID` = `id`
					) AS `Attendants`
				";
				$orderBy .= "
					ORDER BY `Attendants`.`ATT-CNT`
				";
			}
			
			// date sort (default) 
			else {
				$orderBy .= "
					ORDER BY `Events`.`datetimeStart`
				";
			}
			
			$prepared = "$select$join$where$groupBy$orderBy";
			
			if ($GLOBALS["DEBUG"]) {
				print_r($prepared . "\n");
			}
			
			$result = $this->DBs->select($prepared, $binds);
			if ( is_null($result) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
			}
			$JSON = Toolkit::build_json($result);
			//// 
			
			
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["OK"] );
		}
		// * // 
		
	}
?>