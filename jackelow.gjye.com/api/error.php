<?
	class Error extends Exception {
		private $httpStatus;
		
		
		// override, allowing specification of HTTP Status Code //
		public function __construct($httpStatus, $message, $code = 1, Exception $previous = null) {
			$this->httpStatus = $httpStatus;
			
			parent::__construct($message, $code, $previous);
		}
		// * //
		
		
		// Override //
		public function __toString() {
			return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
		}
		// * //
		
		
		// Allow user to cleanly kill script if we've hit an unrecoverable error //
		public function kill() {
			
			// Sanity check values given to us (revert to generic if in doubt) 
			if ( !$this->httpStatus || !is_numeric($this->httpStatus) ) {
				$httpStatus = $GLOBALS["HTTP_STATUS"]["Internal Error"];
			}
			if ( !$this->message ) {
				$this->message = "Generic error";
			}
			if ( !$this->code || !is_numeric($this->code) ) {
				$this->code = 1;
			}
			
			// Send HTTP Status code in headers also 
			http_response_code($httpStatus);
			
			// Get status from status code 
			$status = array_search($this->httpStatus, $GLOBALS["HTTP_STATUS"]);
			
			// Prepare JSON error packet 
			$JSON = [
				"results" => [],
				"status" => $status,
				"message" => $this->message,
				"code" => $this->httpStatus
			];
			
			// Send error to user 
			echo json_encode($JSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
			
			// die 
			die(0);
		}
		// * //
	}
?>