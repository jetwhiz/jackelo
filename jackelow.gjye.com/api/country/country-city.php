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
				case "delete":
				case "put":
				case "post":
				default: 
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], get_class($this) . " Error: Request method not supported.");
			}
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