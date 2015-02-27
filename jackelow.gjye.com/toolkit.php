<? 
	class Toolkit {
		
		
		// Get last element of array by reference // 
		public static function &array_last(&$array) {
			if (!is_array($array)) {return $array;}
			if (!count($array)) {return null;}
			end($array);
			return $array[key($array)];
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
					if ( in_array($row[$keys[0]], $obj) ) {
						continue;
					}
					
					$obj[] = $row[$keys[0]];
				}
			}
			
			return $obj;
		}
		// * // 
		
	}
?>