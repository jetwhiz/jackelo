<?
	class Authenticate {
		protected $DBs;
		
		
		// CONSTRUCTOR //
		function __construct( &$dbs ) {
			if ( is_null($dbs) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r(get_class($this) . " Error: Database not supplied\n");
				}
				
				throw new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], get_class($this) . " Error: Database not supplied.");
			}
			
			$this->DBs = &$dbs;
		}
		// * // 
		
		
		
		// Assertion version of getUser; dies if not logged in //
		public function assert_login() {
			
			$user = $this->getUser();
			if ( is_null( $user ) ) {
				throw new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], get_class($this) . " Error: Failed to authenticate.");
			}
			
			return;
		}
		// * //
		
		
		
		// Tell is if the visitor is logged in or not //
		public function isLoggedIn() {
			
			// If they have a sessionID, see if it's good 
			if ( $_COOKIE["sessionID"] ) {
				try {
					$User = new User($this->DBs, $_COOKIE["sessionID"]);
				} catch (Error $e) {
					return false;
				}
				
				return true;
			}
			
			// No sessionID -->  not logged in 
			return false;
		}
		// * //
		
		
		
		// Obtain the current user // 
		// @param: forceLogin - if not logged in, make them log in
		public function getUser( $forceLogin = true ) {
			
			
			// If they have a sessionID, see if it's good ... otherwise expire it and force login 
			if ( $_COOKIE["sessionID"] ) {
				try {
					$User = new User($this->DBs, $_COOKIE["sessionID"]);
				} catch (Error $e) {
					
					if ( $forceLogin ) {
						setcookie("sessionID", "0", time()-3600, "/", $_SERVER['HTTP_HOST'], true, true);
						$PATH = strtok($_SERVER["REQUEST_URI"],'?');
						header("Location: https://" . $_SERVER['HTTP_HOST'] . $PATH);
						die; // die to be safe (redirection) 
					}
					
					return null;
				}
				
				return $User;
			}
			
			
			
			// no challenge provided .. send them to the GT Login page (dies) 
			if (!array_key_exists('session', $_POST)) {
				$this->challenge();
				
				// challenge redirects, so die to be safe 
				die;
			}
			
			// we have an encrypted session key 
			$json = $this->verifyChallenge();
			if ( is_null($json) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Authenticate: Failed to authenticate\n");
				}
				
				return null;
			}
			
			// Get userID based on username (or create if non-existent) 
			$userID = Toolkit::create_user( $this->DBs, $json["name"] );
			if ( !$userID ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Authenticate: Failed to get user ID\n");
				}
				
				return null;
			}
			
			// Authenticated, now log user in -- redirects upon success (dies) 
			if ( !$this->login( $userID, $json["cnonce"] ) ) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Authenticate: Failed to log in\n");
				}
				
				return null;
			}
			
			
			// We should never get this far 
			if ($GLOBALS["DEBUG"]) {
				print_r("Authenticate: Untrapped assertion\n");
			}
			return null;
		}
		// * //
		
		
		
		// Send user to GT Login with a randomly-generated nonce prepared // 
		public function challenge() {
			
			
			// Set test cookie 
			$val = 1;
			if ( $_COOKIE["test"] ) {
				$val = $_COOKIE["test"];
			}
			
			if ( $val > $GLOBALS["LOOP_DETECT"] ) {
				setcookie("test", "0", time()-3600, "/", $_SERVER['HTTP_HOST'], true, true);
				echo "loop detected";
				die;
			}
			else {
				setcookie("test", $val+1, time()+3600, "/", $_SERVER['HTTP_HOST'], true, true);
			}
			
			
			// Perform INSERT for Sessions table 
			$insert = "
				INSERT INTO `Sessions` (`id`)
				VALUES (?)
			";
			
			
			// Generate client nonce 
			$nonce = bin2hex(openssl_random_pseudo_bytes(10, $cstrong));
			if (!$cstrong) {
				echo "weak";
				die;
			}
			
			// Hash nonce 
			$nonce = hash('ripemd160', $nonce);
			
			// Bind insert params 
			$binds = [];
			$binds[0] = "s";
			$binds[] = $nonce;
			
			// Perform insertion (and ensure row was inserted) 
			$affected = $this->DBs->insert($insert, $binds);
			if ( !$affected ) {
				echo "not inserted";
				die;
			}
			
			// Send them to Gatech login -> brings them back here with QS session set 
			header("Location: " . $GLOBALS["GATECH_WIDGET"] . "?" . urlencode($nonce));
			die;
		}
		// * //
		
		
		
		// Verify that they have succeeded the GT Login challenge //
		public function verifyChallenge() {
			
			// Make sure they can have cookies set successfully
			if ( !$_COOKIE["test"] ) {
				echo "no cookies";
				return NULL;
			}
			setcookie("test", "0", time()-3600, "/", $_SERVER['HTTP_HOST'], true, true);
			
			
			// Decrypt provided challenge response 
			$json = $this->decryptChallenge($_POST["session"]);
			
			
			// Make sure too much time hasn't passed since cnonce was created // 
			$select = "
				SELECT `id`, `datetime`
				FROM `Sessions` 
				WHERE `id` = ?
				LIMIT 1
			";
			$binds = [];
			$binds[0] = "s";
			$binds[] = $json["cnonce"];
			
			$res = $this->DBs->select($select, $binds);
			if ( is_null($res) ) {
				echo "session pull fail";
				return NULL;
			}
			
			$row = $res->fetch_assoc();
			if ( !$row ) {
				echo "bad cnonce given";
				return NULL;
			}
			$seconds = strtotime($row["datetime"]) - strtotime($json["date"]);
			if ( abs($seconds) > $GLOBALS["CCHALLENGE_TO"] ) {
				echo "cnonce timeout";
				return NULL;
			}
			//// 
			
			
			// Make sure too much time hasn't passed since snonce was created 
			$seconds = time() - strtotime($json["date"]);
			if ( abs($seconds) > $GLOBALS["SCHALLENGE_TO"] ) {
				echo "snonce timeout";
				return NULL;
			}
			
			
			return $json;
		}
		// * //
		
		
		
		// DECRYPT GIVEN SESSION (RETURNS JSON OBJECT) //
		private function decryptChallenge($encChallenge) {
			
			// Parse auth configuration file 
			$ini_array = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../dbconf.ini", true);
			if (!$ini_array) {
				if ($GLOBALS["DEBUG"]) {
					print_r("Auth: Cannot access dbconf.ini\n");
				}
				
				return null;
			}
			
			$hash = $ini_array["auth"]["key"];
			$key = pack('H*', hex2bin($hash));
			
			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
			
			$ciphertext_dec = base64_decode($encChallenge);
			$iv_dec = substr($ciphertext_dec, 0, $iv_size);
			$ciphertext_dec = substr($ciphertext_dec, $iv_size);
			$plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
			
			
			// Get position of JSON end (strip null padding from end) 
			$pos = strpos( $plaintext_dec, "}" );
			
			// Make sure we decrypted correctly 
			if ( !$pos || substr($plaintext_dec, 0, 1) != "{" ) {
				echo "key read fail";
				die;
			}
			
			// Clean up to make sure json_decode doesn't complain 
			$plaintext_dec = substr( $plaintext_dec, 0, $pos+1);
			
			
			//echo $plaintext_dec . "<br><br>";
			
			
			// Convert decrypted challenge response to JSON object 
			$json = json_decode($plaintext_dec, true);
			
			// If no cnonce given, die 
			if (!array_key_exists('cnonce', $json) || $json["cnonce"] == "") {
				echo "no cnonce";
				die;
			}
			
			// If no user name given, die 
			if (!array_key_exists('name', $json) || $json["name"] == "") {
				echo "no name";
				die;
			}
			$json["name"] = preg_replace("/[^A-Za-z0-9]/", '', $json["name"]);
			
			// If no date given, die 
			if (!array_key_exists('date', $json) || $json["date"] == "") {
				echo "no date";
				die;
			}
			
			return $json;
		}
		// * //
		
		
		
		// Log user in if challenge succeeded //
		public function login($userID, $tempSessionID) {
			// LOG THE USER IN (ASSIGN THEIR USERNAME TO THIS SESSION) //
			
			// Generate session ID 
			$sessionID = bin2hex(openssl_random_pseudo_bytes(10, $cstrong));
			if (!$cstrong) {
				echo "weak";
				return false;
			}
			$sessionID = hash('ripemd160', $sessionID);
			
			// Perform INSERT for Sessions table 
			$insert = "
				UPDATE `Sessions` 
				SET `userID` = ?, `id` = ?
				WHERE `id` = ?
			";
			
			// Bind insert params 
			$binds = [];
			$binds[0] = "sss";
			$binds[] = $userID;
			$binds[] = $sessionID;
			$binds[] = $tempSessionID;
			
			// Perform insertion (and ensure row was inserted) 
			$affected = $this->DBs->insert($insert, $binds);
			if ( !$affected ) {
				echo "not logged in (update fail)";
				return false;
			}
			
			// Set session cookie 
			setcookie("sessionID", $sessionID, time()+$GLOBALS["COOKIE_EXPR"], "/", $_SERVER['HTTP_HOST'], true, true);
			////
			
			
			// Redirect 
			$PATH = strtok($_SERVER["REQUEST_URI"],'?');
			header("Location: https://" . $_SERVER['HTTP_HOST'] . $PATH);
			die;
		}
		// * //
		
	}
?>