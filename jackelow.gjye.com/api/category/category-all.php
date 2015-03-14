<?
	class CategoryAll extends Handler {
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
		
		
		
		// POST NEW CATEGORY //
		protected function post() {
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nPOST-CategoryAll\n");
				print_r($_POST);
			}
			
			
			// Perform INSERT for Comments table 
			$insert = "
				INSERT IGNORE INTO `Categories` (`name`)
				VALUES (?)
			";
			
			$FLAGS = ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE;
			
			// Sanitize input string 
			$term = preg_replace("/[^\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w ]/u", "", $_POST["name"]);
			if ( strlen($term) > $GLOBALS["MAX_LENGTHS"]["category_name"] ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Category length too long!");
			}
			
			$binds = [];
			$binds[0] = "s";
			$binds[] = htmlspecialchars($term, $FLAGS, "UTF-8");
			
			if ($GLOBALS["DEBUG"]) {
				print_r("\nBINDS\n");
				print_r($insert."\n");
				print_r($binds);
			}
			
			// Perform insertion (and ensure row was inserted) 
			$affected = $this->DBs->insert($insert, $binds);
			if ( !$affected ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert Category failed!");
			}
			
			// Retrieve categoryID for future reference 
			$categoryID = $this->DBs->insertID();
			if ($GLOBALS["DEBUG"]) {
				print_r("INSERTID: " . $categoryID . "\n");
			}
			if ( !$categoryID ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . ": Insert Category failed!");
			}
			
			
			// Return inserted categoryID 
			$JSON = [
				"categoryID" => $categoryID
			];
			$this->send( $JSON, $GLOBALS["HTTP_STATUS"]["Created"] );
		}
		// * //
		
		
		
		// GET //
		protected function get() {
			
			// Get all categories 
			$select = "
				SELECT `id`, `name`
				FROM `Categories` 
			";
			$binds = null;
			
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