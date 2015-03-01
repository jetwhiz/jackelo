<?
	class CountryCity {
		private $REST_vars;
		private $DBs;
		private $User;
		
		// CONSTRUCTOR //
		function __construct( $REST_vars, &$dbs, &$user ) {
			if ( is_null($dbs) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("CountryCity Error: Database not supplied\n");
				}
				
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "CountryCity Error: Database not supplied.");
			}
			
			$this->REST_vars = $REST_vars;
			$this->DBs = &$dbs;
			$this->User = &$user;
			
			switch ( $REST_vars["method"] ) {
				case "get": 
					$this->get();
					break;
				case "delete":
				case "put":
				case "post":
				default: 
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], "CountryCity Error: Request method not supported.");
			}
		}
		// * // 
		
		
		
		// GET //
		private function get() {
			
			// Get city with given cityID
			if ( $this->REST_vars["cityID"] && is_numeric($this->REST_vars["cityID"]) ) {
				$select = "
					SELECT `id`, `countryID`, `name`, `latitude`, `longitude`
					FROM `Cities` 
					WHERE `id` = ?
				";
				$binds = ["i", $this->REST_vars["cityID"]];
			}
			
			// Get cities with given countryID
			else {
				$select = "
					SELECT `id`, `countryID`, `name`, `latitude`, `longitude`
					FROM `Cities` 
					WHERE `countryID` = ?
				";
				$binds = ["i", $this->REST_vars["countryID"]];
			}
			
			
			$res = $this->DBs->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "CountryCity Error: Failed to retrieve request.");
			}
			
			$JSON = [];
			while ($row = $res->fetch_assoc()) {
				$obj = [];
				
				$obj["id"] = $row['id'];
				$obj["countryID"] = $row['countryID'];
				$obj["name"] = $row['name'];
				$obj["latitude"] = $row['latitude'];
				$obj["longitude"] = $row['longitude'];
				
				$JSON[] = $obj;
			}
			//// 
			
			
			echo json_encode($JSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
		}
		// * // 
		
	}
?>