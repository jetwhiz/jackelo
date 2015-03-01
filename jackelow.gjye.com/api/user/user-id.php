<?
	class UserID {
		private $REST_vars;
		private $DBs;
		private $User;
		
		// CONSTRUCTOR //
		function __construct( $REST_vars, &$dbs, &$user ) {
			if ( is_null($dbs) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("UserID Error: Database not supplied\n");
				}
				
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "UserID Error: Database not supplied.");
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
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], "UserID Error: Request method not supported.");
			}
		}
		// * // 
		
		
		
		// GET //
		private function get() {
			
			// Get main event data 
			$select = "
					SELECT `id`, `email`, `name`, `surname`
					FROM `Users` 
					WHERE `id` = ?
			";
			$binds = ["i", $this->REST_vars["userID"]];
			
			$res = $this->DBs->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "UserID Error: Failed to retrieve request.");
			}
			
			$JSON = [];
			while ($row = $res->fetch_assoc()) {
				$obj = [];
				
				// Public info 
				$obj["id"] = $row['id'];
				$obj["name"] = $row['name'];
				$obj["surname"] = $row['surname'];
				
				// Private info (only for requests about oneself) 
				if ($this->User->getID() == $this->REST_vars["userID"]) {
					$obj["email"] = $row['email'];
				}
				
				$JSON[] = $obj;
			}
			//// 
			
			
			echo json_encode($JSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
		}
		// * // 
		
	}
?>