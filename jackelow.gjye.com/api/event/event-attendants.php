<?
	class EventAttendants extends Handler {
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
					$this->delete();
					break;
				case "put":
				default: 
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], get_class($this) . " Error: Request method not supported.");
			}
		}
		// * // 
		
		
		
		// DELETE //
		protected function delete() {
			if ($GLOBALS["DEBUG"]) {
				print_r("DELETE-EventAttendants\n");
			}
			
			// Perform DELETE for Attendants table 
			$delete = "
				DELETE FROM `Attendants` WHERE `eventID` = ? AND `userID` = ?
				LIMIT 1
			";
			
			$binds = [];
			$binds[0] = "ii";
			$binds[] = $this->REST_vars["eventID"];
			$binds[] = $this->User->getID();
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nBINDS\n");
				print_r($delete."\n");
				print_r($binds);
			}
			
			// Perform removal (and ensure row was removed) 
			$affected = $this->DBs->delete($delete, $binds);
			if ( !$affected ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Attendance removal failed!");
			}
			
			
			$JSON = [];
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["OK"] );
		}
		// * //
		
		
		
		// POST //
		protected function post() {
			if ($GLOBALS["DEBUG"]) {
				print_r("\nPOST-EventAttendants\n");
				print_r($_POST);
			}
			
			// Perform INSERT for Attendants table 
			$insert = "
				INSERT INTO `Attendants` (`eventID`, `userID`)
				VALUES (?, ?)
			";
			
			
			$binds = [];
			$binds[0] = "ii";
			$binds[] = $this->REST_vars["eventID"];
			$binds[] = $this->User->getID();
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nBINDS\n");
				print_r($insert."\n");
				print_r($binds);
			}
			
			
			// Check to see if user is already attending event 
			{
				$select = "
					SELECT COUNT(*) AS `count` FROM `Attendants` 
					WHERE `eventID` = ? AND `userID` = ?
					LIMIT 1
				";
				
				$res = $this->DBs->select($select, $binds);
				if ( is_null($res) ) {
					throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
				}
				
				$row = $res->fetch_assoc();
				if ($row["count"] == 1) {
					// Already attending event -- no changes needed
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], get_class($this) . " Error: Already attending event.");
				}
			}
			
			
			// Perform insertion (and ensure row was inserted) 
			$affected = $this->DBs->insert($insert, $binds);
			if ( !$affected ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Attendance failed!");
			}
			
			
			$JSON = [];
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["Created"] );
		}
		// * //
		
		
		
		// GET //
		protected function get() {
			
			// Get all attendants to event # eventID
			$select = "
					SELECT `Attendants`.`userID` AS `id`, `Users`.`username`
					FROM `Attendants` 
					INNER JOIN `Users` AS `Users`
						ON `Users`.`id` = `Attendants`.`userID`
					WHERE `Attendants`.`eventID` = ?
			";
			$binds = ["i", $this->REST_vars["eventID"]];
			
			$res = $this->DBs->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Failed to retrieve request.");
			}
			
			$JSON = Toolkit::build_json($res);
			
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["OK"] );
		}
		// * // 
		
	}
?>