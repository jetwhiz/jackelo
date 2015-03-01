<?
	abstract class Handler {
		protected $REST_vars;
		protected $DBs;
		protected $User;
		
		
		// CONSTRUCTOR //
		function __construct( &$REST_vars, &$dbs, &$user ) {
			if ( is_null($dbs) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r(get_class($this) . " Error: Database not supplied\n");
				}
				
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Database not supplied.");
			}
			
			$this->REST_vars = &$REST_vars;
			$this->DBs = &$dbs;
			$this->User = &$user;
			
			// autostart 
			$this->run();
		}
		// * // 
		
		
		
		// RUN //
		public function run() {
			switch ( $REST_vars["method"] ) {
				case "delete":
				case "get": 
				case "post":
				case "put":
				default: 
					throw new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], get_class($this) . " Error: Request method not supported.");
			}
		}
		// * // 
		
		
		
		// SEND //
		protected function send( $JSON, $code ) {
			
			// Print out header status 
			http_response_code($code);
			
			// Get status text from code # 
			$status = array_search($code, $GLOBALS["HTTP_STATUS"]);
			
			// Wrap in API wrapper 
			$JSON = [
				"results" => $JSON, 
				"status" => $status,
				"code" => $code
			];
			
			// Send to user 
			echo json_encode($JSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
		}
		// * //
		
		
		
		// DELETE //
		protected function delete() {}
		// * // 
		
		
		
		// GET //
		protected function get() {}
		// * // 
		
		
		
		// POST //
		protected function post() {}
		// * // 
		
		
		
		// PUT //
		protected function put() {}
		// * // 
		
	}
?>