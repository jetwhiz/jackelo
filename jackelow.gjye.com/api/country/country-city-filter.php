<?
	class CountryCityFilter extends Handler {
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
			
			// Get category matching given string 
			$select = "
				SELECT `id`, `name`
				FROM `Cities` 
				WHERE `name` LIKE CONCAT(?, '%')
				AND `countryID` = ?
			";
			$binds = ["si", $this->REST_vars["terms"], $this->REST_vars["countryID"]];
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nBINDS\n");
				print_r($select."\n");
				print_r($binds);
			}
			
			
			$res = $this->DBs->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
			}
			
			$JSON = [];
			while ($row = $res->fetch_assoc()) {
				$obj = [];
				
				$obj["id"] = $row['id'];
				$obj["name"] = $row['name'];
				
				$JSON[] = $obj;
			}
			//// 
			
			
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["OK"] );
		}
		// * // 
		
	}
?>