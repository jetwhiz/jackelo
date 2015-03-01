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