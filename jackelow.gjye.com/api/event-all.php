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
					break;
				case "delete":
					break;
				default: 
					break;
			}
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