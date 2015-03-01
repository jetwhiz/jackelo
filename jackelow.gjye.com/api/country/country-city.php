<?
	class CountryCity extends Handler {
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
				case "delete":
				case "put":
				default: 
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], get_class($this) . " Error: Request method not supported.");
			}
		}
		// * // 
		
		
		
		// POST //
		protected function post() {
			
			
			// Get country name from countryID 
			$select = "
				SELECT `name`
				FROM `Countries` 
				WHERE `id` = ?
				LIMIT 1
			";
			$binds = ["i", $this->REST_vars["countryID"]];
			
			$res = $this->DBs->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
			}
			
			$row = $res->fetch_assoc();
			$countryName = $row['name'];
			////
			
			
			// Clean up city name 
			$cityName = preg_replace("/[^\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w -]/u", "", $_POST["name"]);
			
			
			// Make request to Geolocation server to get GPS coords 
			$result = file_get_contents($GLOBALS["GEOLOC"] . urlencode("$cityName, $countryName"));
			$json = json_decode($result, true);
			
			// Ensure we have exactly 1 result 
			if ( count($json["results"]) != 1 || count($json["results"][0]["geometry"]["location"]) != 2 ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to obtain GPS coordinates.");
			}
			////
			
			
			// Get lat/lng
			$latitude = floatval($json["results"][0]["geometry"]["location"]["lat"]);
			$longitude = floatval($json["results"][0]["geometry"]["location"]["lng"]);
			
			
			// Perform INSERT for Cities table 
			$insert = "
				INSERT INTO `Cities` (`countryID`, `name`, `latitude`, `longitude`)
				VALUES (?, ?, ?, ?)
			";
			
			$FLAGS = ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE;
			
			$binds = [];
			$binds[0] = "isdd";
			$binds[] = $this->REST_vars["countryID"];
			$binds[] = htmlspecialchars($cityName, $FLAGS, "UTF-8");
			$binds[] = $latitude;
			$binds[] = $longitude;
			
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nBINDS\n");
				print_r($insert."\n");
				print_r($binds);
			}
			
			
			// Perform insertion (and ensure row was inserted) 
			$affected = $this->DBs->insert($insert, $binds);
			if ( !$affected ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert City failed!");
			}
			
			// Retrieve cityID for future reference 
			$cityID = $this->DBs->insertID();
			if ($GLOBALS["DEBUG"]) {
				print_r("INSERTID: " . $cityID . "\n");
			}
			if ( !$cityID ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert City failed!");
			}
			////
			
			
			
			// "Created" HTTP Status code 
			http_response_code($GLOBALS["HTTP_STATUS"]["Created"]);
			
			
			// Return inserted cityID 
			$JSON = [
				$cityID
			];
			echo json_encode($JSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
		}
		// * //
		
		
		
		// GET //
		protected function get() {
			
			// Get city with given cityID
			if ( $this->REST_vars["cityID"] && is_numeric($this->REST_vars["cityID"]) ) {
				$select = "
					SELECT `id`, `countryID`, `name`, `latitude`, `longitude`, `thumb`
					FROM `Cities` 
					WHERE `id` = ?
				";
				$binds = ["i", $this->REST_vars["cityID"]];
			}
			
			// Get cities with given countryID
			else {
				$select = "
					SELECT `id`, `countryID`, `name`, `latitude`, `longitude`, `thumb`
					FROM `Cities` 
					WHERE `countryID` = ?
				";
				$binds = ["i", $this->REST_vars["countryID"]];
			}
			
			
			$res = $this->DBs->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
			}
			
			$JSON = [];
			while ($row = $res->fetch_assoc()) {
				$obj = [];
				
				$obj["id"] = $row['id'];
				$obj["countryID"] = $row['countryID'];
				$obj["name"] = $row['name'];
				$obj["latitude"] = $row['latitude'];
				$obj["longitude"] = $row['longitude'];
				$obj["thumb"] = $GLOBALS["IMG_DIR"] . $row['thumb'];
				
				$JSON[] = $obj;
			}
			//// 
			
			
			echo json_encode($JSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
		}
		// * // 
		
	}
?>