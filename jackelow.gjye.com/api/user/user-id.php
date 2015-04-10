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
					SELECT `Users`.`id`, `Users`.`username`, `Users`.`networkID`, `Networks`.`name_short` AS `networkAbbr`, 
						`Networks`.`name` AS `network`, `Users`.`email`, `Users`.`name`, `Users`.`surname`, 
						`Users`.`premium`, `Users`.`created`
					FROM `Users` 
					INNER JOIN `Networks` AS `Networks`
						ON `Networks`.`id` = `Users`.`networkID`
					WHERE `Users`.`id` = ?
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
				$obj["username"] = $row['username'];
				$obj["name"] = $row['name'];
				$obj["surname"] = $row['surname'];
				$obj["created"] = $row['created'];
				$obj["networkID"] = $row['networkID'];
				$obj["networkAbbr"] = $row['networkAbbr'];
				$obj["network"] = $row['network'];
				
				// Private info (only for requests about oneself) 
				if ($this->User->getID() == $this->REST_vars["userID"]) {
					$obj["email"] = $row['email'];
					$obj["premium"] = $row['premium'];
				}
				
				$JSON[] = $obj;
			}
			//// 
			
			
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["OK"] );
		}
		// * // 
		
	}
?>