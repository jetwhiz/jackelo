<?
	class EventAll {
		private $REST_vars;
		private $DBs;
		private $User;
		
		
		// CONSTRUCTOR //
		function __construct( $REST_vars, &$dbs, &$user ) {
			if ( is_null($dbs) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("ERR");
				}
				
				throw new Exception('EventAll Error: Database not supplied.');
			}
			
			$this->REST_vars = $REST_vars;
			$this->DBs = &$dbs;
			$this->User = &$user;
			
			switch ( $REST_vars["method"] ) {
				case "get": 
					$this->get();
					break;
				case "put":
					break;
				case "post":
					$this->post();
					break;
				case "delete":
					break;
				default: 
					break;
			}
		}
		// * // 
		
		
		// POST NEW EVENT //
		private function post() {
			/* EXAMPLE: 
			URL encoded: 
			name=cool+stuff+is+coming!&description=jfklsdjfkj+jsdfksdjf
			&datetimeStart=2015-04-23+11%3A00%3A11&datetimeEnd=2015-04-28+17%3A00%3A00
			&eventTypeID=3&categoryID=1%2C2&destination%5B0%5D%5Baddress%5D=ALOES
			&destination%5B0%5D%5BdatetimeStart%5D=2015-04-23+11%3A00%3A11
			&destination%5B0%5D%5BdatetimeEnd%5D=2015-04-24+11%3A00%3A11
			&destination%5B0%5D%5BcityID%5D=1&destination%5B0%5D%5BcountryID%5D=60
			&destination%5B1%5D%5Baddress%5D=Layafette
			&destination%5B1%5D%5BdatetimeStart%5D=2015-04-24+11%3A00%3A11
			&destination%5B1%5D%5BdatetimeEnd%5D=2015-04-25+11%3A00%3A11
			&destination%5B1%5D%5BcityID%5D=1&destination%5B1%5D%5BcountryID%5D=60
			&destination%5B2%5D%5Baddress%5D=Hostel
			&destination%5B2%5D%5BdatetimeStart%5D=2015-04-25+11%3A00%3A11
			&destination%5B2%5D%5BdatetimeEnd%5D=2015-04-28+11%3A00%3A11
			&destination%5B2%5D%5BcityID%5D=2&destination%5B2%5D%5BcountryID%5D=64&
			*/
			
			print_r("\nPOST-EventAll\n");
			print_r($_POST);
			
			
			
			// start transaction 
			$this->DBs->startTransaction();
			
			
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
			
			
			print_r("\nBINDS\n");
			print_r($insert."\n");
			print_r($binds);
			
			if ( ! ($results = $this->DBs->insert($insert, $binds)) ) {
				// Roll back transaction 
				$this->DBs->abortTransaction();
				throw new Exception("EventAll: Insert failed!");
			}
			
			$eventID = $this->DBs->insertID();
			echo "INSERTID: " . $eventID . "\n";
			
			
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
				
				print_r("\nBINDS\n");
				print_r($insert."\n");
				print_r($binds);
				
				if ( ! ($results = $this->DBs->insert($insert, $binds)) ) {
					// Roll back transaction 
					$this->DBs->abortTransaction();
					throw new Exception("EventAll: Insert category failed!");
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
				
				print_r("\nBINDS\n");
				print_r($insert."\n");
				print_r($binds);
				
				if ( ! ($results = $this->DBs->insert($insert, $binds)) ) {
					// Roll back transaction 
					$this->DBs->abortTransaction();
					throw new Exception("EventAll: Insert destination failed!");
				}
			}
			
			
			// Commit transaction
			$this->DBs->endTransaction();
			
		}
		// * //
		
		
		// GET EVENTS //
		private function get() {
			
			// Get main event data 
			
			$select = "";
			$join = "";
			$where = "";
			$groupBy = "";
			$orderBy = "";
			$binds = null;
			
			
			
			// group by (country) 
			if ( $this->REST_vars["group"] ) {
				$select .= "
					SELECT DISTINCT COUNT(*) AS `Count`, `EventDestinations`.`countryID`
					FROM `Events` 

				";
				$join .= "
					INNER JOIN `EventDestinations` AS `EventDestinations`
						ON `Events`.`id` = `EventDestinations`.`eventID`
				";
			}
			else {
				$select .= "
					SELECT `Events`.`id`
					FROM `Events` 
				";
			}
			
			
			
			
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
			
			$JSON = Toolkit::build_json(
											$this->DBs->select($prepared, $binds)
										);
			//// 
			
			
			echo json_encode($JSON, JSON_PRETTY_PRINT) . "\n\n";
		}
		// * // 
		
	}
?>