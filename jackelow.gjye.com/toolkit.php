<? 
	class Toolkit {
		
		
		// Convert links to hyperlinks in given string // 
		public static function clickify($str) {
			
			// Sanitize (possibly again) just in case 
			$FLAGS = ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE;
			$str = htmlspecialchars($str, $FLAGS, "UTF-8", false); // false = don't re-encode 
			
			// Regex replace links with hyperlinks 
			return preg_replace_callback(
					'!(((f|ht)tp(s)?:)(//[-a-zA-Zа-яА-Я0-9.]+)(/[-a-zA-Zа-яА-Я0-9/._~$&()*+,;=:@%]+)?(\?[-a-zA-Zа-яА-Я()0-9%_+.~&;=]*)?(#[-a-zA-Zа-яА-Я()0-9%_+.~&;=]*)?)!',
					function ($matches) {
						if ( filter_var($matches[1], FILTER_VALIDATE_URL) ) {
							return '<a href="' . $matches[1] . '">' . $matches[1] . '</a>';
						}
						
						return $matches[1];
					},
					$str
			);
		}
		// * //
		
		
		// Determine if the given date range is valid // 
		public static function daterange_valid($start, $end) {
			
			// Convert all to UNIX timestamps 
			$startTS = strtotime($start);
			$endTS = strtotime($end);
			
			// Make sure the dates are valid (not Feb 31) 
			$dS = date('Y-m-d', $startTS);
			$dE = date('Y-m-d', $endTS);
			if ( !$dS || $dS != $start || !$dE || $dE != $end ) {
				return false;
			}
			
			return (($startTS <= $endTS));
		}
		// * //
		
		
		// Determine if the given date range is within range of start - end dates (and valid) // 
		public static function daterange_bounded($start, $end, $dateStart, $dateEnd) {
			
			// Convert all to UNIX timestamps 
			$startTS = strtotime($start);
			$endTS = strtotime($end);
			$dateStartTS = strtotime($dateStart);
			$dateEndTS = strtotime($dateEnd);
			
			// Make sure the dates are valid (not Feb 31) 
			$dS = date('Y-m-d', $startTS);
			$dE = date('Y-m-d', $endTS);
			$ddS = date('Y-m-d', $dateStartTS);
			$ddE = date('Y-m-d', $dateEndTS);
			if ( !$dS || $dS != $start || !$dE || $dE != $end
				|| !$ddS || $ddS != $dateStart || !$ddE || $ddE != $dateEnd 
			) {
				return false;
			}
			
			return ( ($dateStartTS >= $startTS) && ($dateStartTS <= $endTS)
				&& ($dateEndTS >= $startTS) && ($dateEndTS <= $endTS)
				&& ($dateStartTS <= $dateEndTS)
			);
		}
		// * //
		
		
		// Strip empty/null elements from array //
		public static function array_clean(&$array) {
			$temp = array_filter($array, function($v) {
				return !is_null($v) && isset($v) && $v != "";
			});
			$temp = array_slice($temp, 0);
			
			return $temp;
		}
		// * //
		
		
		// Get last element of array by reference // 
		public static function &array_last(&$array) {
			if (!is_array($array)) {return $array;}
			if (!count($array)) {return null;}
			end($array);
			return $array[key($array)];
		} 
		// * // 
		
		
		// Create a new user for a given GT Login - demo or GT accounts only //
		public static function create_user( &$DBs, $username ) {
			$userID = 0;
			$demo = 0;
			
			// If user is a demo user, randomly generate username 
			if ( $username == "demo#" ) {
				$randomID = bin2hex(openssl_random_pseudo_bytes(5, $cstrong));
				if (!$cstrong) {
					echo "weak";
					die;
				}
				
				$username = $randomID;
				$demo = 1;
			}
			////
			
			
			// See if user already exists // 
			$select = "
				SELECT `id`
				FROM `Users` 
				WHERE `username` = ? AND `networkID` = ?
				LIMIT 1
			";
			$binds = [];
			$binds[0] = "si";
			$binds[] = $username;
			if ( $demo ) {
				$binds[] = $GLOBALS["NETWORKS"]["demo"];
			}
			else {
				$binds[] = $GLOBALS["NETWORKS"]["GT"];
			}
			
			$res = $DBs->select($select, $binds);
			if ( is_null($res) ) {
				echo "user fail";
				die;
			}
			
			$row = $res->fetch_assoc();
			
			
			// If demo user is being created 
			if ( $demo ) {
				
				// If userID already exists, this is a problem (duplicate demo user) 
				if ( $row ) {
					echo "duplicate demo user fail";
					die;
				}
				
				// Perform INSERT for Users table 
				$insert = "
					INSERT INTO `Users` (`username`, `networkID`) 
					VALUES (?, ?)
				";
				
				// Bind insert params 
				$binds = [];
				$binds[0] = "si";
				$binds[] = $username;
				$binds[] = $GLOBALS["NETWORKS"]["demo"];
				
				// Perform insertion (and ensure row was inserted) 
				$affected = $DBs->insert($insert, $binds);
				if ( !$affected ) {
					echo "demo user not created (insert fail)";
					die;
				}
				
				// Retrieve userID for future reference 
				$userID = $DBs->insertID();
				if ( !$userID ) {
					echo "demo user not created (get ID fail)";
					die;
				}
			}
			
			// No user exists with this ID -- create them 
			elseif ( !$row ) {
				
				// Perform INSERT for Users table 
				$insert = "
					INSERT INTO `Users` (`username`, `networkID`) 
					VALUES (?, ?)
				";
				
				// Bind insert params 
				$binds = [];
				$binds[0] = "si";
				$binds[] = $username;
				$binds[] = $GLOBALS["NETWORKS"]["GT"];
				
				// Perform insertion (and ensure row was inserted) 
				$affected = $DBs->insert($insert, $binds);
				if ( !$affected ) {
					echo "user not created (insert fail)";
					die;
				}
				
				// Retrieve userID for future reference 
				$userID = $DBs->insertID();
				if ( !$userID ) {
					echo "user not created (get ID fail)";
					die;
				}
			}
			
			// User already exists -- use existing userID
			else {
				$userID = $row["id"];
			}
			
			return $userID;
		}
		// * //
		
		
		// Takes a set of rows from DB results and returns a JSON-format object // 
		public static function build_json(&$results) {
			$obj = [];
			
			// iterate through all rows, building object $obj 
			while ($row = $results->fetch_assoc()) {
				$keys = array_keys($row);
				
				// for multiple columns we must return an array of objects 
				if ( count($keys) > 1 ) {
					$obj2 = [];
					
					foreach ($row as $key => $value) {
						$obj2[$key] = $value;
					}
					
					$obj[] = $obj2;
				}
				
				// if there is only one column, return a simple array  
				else {
					
					// Skip duplicates 
					if ( in_array($row[$keys[0]], $obj) ) {
						continue;
					}
					
					$obj[] = $row[$keys[0]];
				}
			}
			
			return $obj;
		}
		// * // 
		
		
		// Calculates the great-circle distance between two points with the Vincenty formula //
		/*
		 * @param float $latitudeFrom Latitude of start point in [deg decimal]
		 * @param float $longitudeFrom Longitude of start point in [deg decimal]
		 * @param float $latitudeTo Latitude of target point in [deg decimal]
		 * @param float $longitudeTo Longitude of target point in [deg decimal]
		 * @param float $earthRadius Mean earth radius in [m]
		 * @return float Distance between points in [m] (same as earthRadius)
		*/
		public static function gps_distance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
			// convert from degrees to radians
			$latFrom = deg2rad($latitudeFrom);
			$lonFrom = deg2rad($longitudeFrom);
			$latTo = deg2rad($latitudeTo);
			$lonTo = deg2rad($longitudeTo);
			
			$lonDelta = $lonTo - $lonFrom;
			$a = pow(cos($latTo) * sin($lonDelta), 2) +
				pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
			$b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
			
			$angle = atan2(sqrt($a), $b);
			return $angle * $earthRadius;
		}
		// * //
	}
?>