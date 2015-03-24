<?
	class UserID extends Handler {
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
			
			// Get main event data 
			$select = "
					SELECT `id`, `email`, `name`, `surname`, `premium`, `guest`
					FROM `Users` 
					WHERE `id` = ?
			";
			$binds = ["i", $this->REST_vars["userID"]];
			
			$res = $this->DBs->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
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
					$obj["premium"] = $row['premium'];
					$obj["guest"] = $row['guest'];
				}
				
				$JSON[] = $obj;
			}
			//// 
			
			
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["OK"] );
		}
		// * // 
		
	}
?>