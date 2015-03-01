<?
	class CountryAll {
		private $REST_vars;
		private $DBs;
		private $User;
		
		// CONSTRUCTOR //
		function __construct( $REST_vars, &$dbs, &$user ) {
			if ( is_null($dbs) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("CountryAll Error: Database not supplied\n");
				}
				
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "CountryAll Error: Database not supplied.");
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
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], "CountryAll Error: Request method not supported.");
			}
		}
		// * // 
		
		
		
		// GET //
		private function get() {
			
			// Get all countries 
			$select = "
				SELECT `id`, `name`, `latitude`, `longitude`
				FROM `Countries` 
			";
			$binds = null;
			
			$res = $this->DBs->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "CountryAll Error: Failed to retrieve request.");
			}
			
			$JSON = [];
			while ($row = $res->fetch_assoc()) {
				$obj = [];
				
				$obj["id"] = $row['id'];
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