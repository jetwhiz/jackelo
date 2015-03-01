<?
	class CategoryAll {
		private $REST_vars;
		private $DBs;
		private $User;
		
		// CONSTRUCTOR //
		function __construct( $REST_vars, &$dbs, &$user ) {
			if ( is_null($dbs) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("CategoryAll Error: Database not supplied\n");
				}
				
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "CategoryAll Error: Database not supplied.");
			}
			
			$this->REST_vars = $REST_vars;
			$this->DBs = &$dbs;
			$this->User = &$user;
			
			switch ( $REST_vars["method"] ) {
				case "get": 
					$this->get();
					break;
				case "post":
					$this->post();
					break;
				case "delete":
				case "put":
				default: 
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], "CategoryAll Error: Request method not supported.");
			}
		}
		// * // 
		
		
		// POST NEW CATEGORY //
		private function post() {
			
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
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "CategoryAll: Insert Category failed!");
			}
			
			// Retrieve categoryID for future reference 
			$categoryID = $this->DBs->insertID();
			if ($GLOBALS["DEBUG"]) {
				print_r("INSERTID: " . $categoryID . "\n");
			}
			if ( !$categoryID ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "CategoryAll: Insert Category failed!");
			}
			
			
			// "Created" HTTP Status code 
			http_response_code($GLOBALS["HTTP_STATUS"]["Created"]);
			
			
			// Return inserted categoryID 
			$JSON = [
				$categoryID
			];
			echo json_encode($JSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
		}
		// * //
		
		
		// GET //
		private function get() {
			
			// Get all categories 
			$select = "
				SELECT `id`, `name`
				FROM `Categories` 
			";
			$binds = null;
			
			$res = $this->DBs->select($select, $binds);
			if ( is_null($res) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "CategoryAll Error: Failed to retrieve request.");
			}
			
			$JSON = [];
			while ($row = $res->fetch_assoc()) {
				$obj = [];
				
				$obj["id"] = $row['id'];
				$obj["name"] = $row['name'];
				
				$JSON[] = $obj;
			}
			//// 
			
			
			echo json_encode($JSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
		}
		// * // 
		
	}
?>