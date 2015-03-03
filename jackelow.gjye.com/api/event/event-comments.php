<?
	class EventComments extends Handler {
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
				print_r("DELETE-EventComments\n");
			}
			
			// Make sure commentID given 
			if ( ! $this->REST_vars["commentID"] ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], get_class($this) . ": Comment ID not supplied!");
			}
			
			// Perform DELETE for Comments table 
			$delete = "
				DELETE `Comments` FROM `Comments`
				INNER JOIN `Events` AS `Events` 
				WHERE 
					`Events`.`id` = `Comments`.`eventID` AND
					`Comments`.`id` = ? AND (
						`Comments`.`ownerID` = ? OR 
						`Events`.`ownerID` = ?
					)
			";
			
			$binds = [];
			$binds[0] = "iii";
			$binds[] = $this->REST_vars["commentID"];
			$binds[] = $this->User->getID();
			$binds[] = $this->User->getID();
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nBINDS\n");
				print_r($delete."\n");
				print_r($binds);
			}
			
			// Perform removal (and ensure row was removed) 
			$affected = $this->DBs->delete($delete, $binds);
			if ( !$affected ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], get_class($this) . ": Comment removal failed!");
			}
			
			
			$JSON = [];
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["OK"] );
		}
		// * //
		
		
		
		// POST NEW EVENT //
		protected function post() {
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nPOST-EventComments\n");
				print_r($_POST);
			}
			
			// Get datetime of comment (NOW) 
			$now = new DateTime();
			$dateTime = $now->format('Y-m-d H:i:s');
			
			
			// No comment body supplied 
			if ( $_POST["message"] == "" ) {
				$e = new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], get_class($this) . ": You did not supply a comment body.");
				$e->kill();
			}
			
			
			// Perform INSERT for Comments table 
			$insert = "
				INSERT INTO `Comments` (`eventID`, `ownerID`, `datetime`, `message`)
				VALUES (?, ?, ?, ?)
			";
			
			$FLAGS = ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE;
			
			$binds = [];
			$binds[0] = "iiss";
			$binds[] = $this->REST_vars["eventID"];
			$binds[] = $this->User->getID();
			$binds[] = $dateTime;
			$binds[] = htmlspecialchars($_POST["message"], $FLAGS, "UTF-8");
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nBINDS\n");
				print_r($insert."\n");
				print_r($binds);
			}
			
			// Perform insertion (and ensure row was inserted) 
			$affected = $this->DBs->insert($insert, $binds);
			if ( !$affected ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert Comment failed!");
			}
			
			// Retrieve commentID for future reference 
			$commentID = $this->DBs->insertID();
			if ($GLOBALS["DEBUG"]) {
				print_r("INSERTID: " . $commentID . "\n");
			}
			if ( !$commentID ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert Comment failed!");
			}
			
			
			// Return inserted commentID 
			$JSON = [
				"commentID" => $commentID
			];
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["Created"] );
		}
		// * //
		
		
		
		// GET //
		protected function get() {
			
			// Get comments
			
			// Specific commentID given (give full details of comment) 
			if ( $this->REST_vars["commentID"] ) {
				$select = "
						SELECT `eventID`, `ownerID`, `datetime`, `message`
						FROM `Comments` 
						WHERE `id` = ?
				";
				$binds = ["i", $this->REST_vars["commentID"]];
			}
			
			// No commentID given (return all comments for this eventID) 
			else {
				$select = "
						SELECT `id`
						FROM `Comments` 
						WHERE `eventID` = ?
						ORDER BY `datetime`
				";
				$binds = ["i", $this->REST_vars["eventID"]];
			}
			
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