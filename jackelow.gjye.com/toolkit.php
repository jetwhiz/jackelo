<? 
	class Toolkit {
		
		
		// Determine if the given date range is valid // 
		public static function daterange_valid($start, $end) {
		  
		  // Convert all to UNIX timestamps 
		  $startTS = strtotime($start);
		  $endTS = strtotime($end);
		  
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
		
		
		// Create a new user for a given GT Login //
		public static function create_user( &$DBs, $username ) {
			$userID = 0;
			
			// See if user already exists // 
			$select = "
				SELECT `id`
				FROM `Users` 
				WHERE `username` = ?
				LIMIT 1
			";
			$binds = [];
			$binds[0] = "s";
			$binds[] = $username;
			
			$res = $DBs->select($select, $binds);
			if ( is_null($res) ) {
				echo "user fail";
				die;
			}
			
			$row = $res->fetch_assoc();
			
			// No user exists with this ID -- create them 
			if ( !$row ) {
				
				// Perform INSERT for Users table 
				$insert = "
					INSERT INTO `Users` (`username`) 
					VALUES (?)
				";
				
				// Bind insert params 
				$binds = [];
				$binds[0] = "s";
				$binds[] = $username;
				
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