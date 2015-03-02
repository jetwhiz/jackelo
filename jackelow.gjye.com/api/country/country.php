<?
	/* RESTful queries can take the forms:
		(/)?				-- get all countries 
		#(/)?				-- get country for given countryID 
		#/cities(/)?		-- get all cities for given countryID
	*/
	
	
	// Are we asking for city information? 
	$REST_vars["cityID"] = null;
	
	
	// Retrieve countryID 
	if ( count($queryArray) && is_numeric($queryArray[0]) ) {
		
		// Country ID was supplied as a token 
		$REST_vars["countryID"] = intval(array_shift($queryArray), 10);
		
		// Debug dump 
		if ($GLOBALS["DEBUG"]) {
			print_r("COUNTRY ID: " . $REST_vars["countryID"] . "\n");
		}
		
		
		// No more options given 
		if ( !count($queryArray) ) {
			require "country-id.php";
			try {
				$handler = new CountryID($REST_vars, $DBs, $User);
			} catch (Error $e) {
				$e->kill();
			}
		}
		
		// City request 
		elseif( $queryArray[0] == "city" ) {
			array_shift($queryArray);
			
			// City ID supplied 
			if ( count($queryArray) && is_numeric($queryArray[0]) ) {
				$REST_vars["cityID"] = intval(array_shift($queryArray), 10);
				
				// Debug dump 
				if ($GLOBALS["DEBUG"]) {
					print_r("CITY ID: " . $REST_vars["cityID"] . "\n");
				}
			}
			
			require "country-city.php";
			try {
				$handler = new CountryCity($REST_vars, $DBs, $User);
			} catch (Error $e) {
				$e->kill();
			}
		}
		
		// Invalid option, ignore 
		else {
			if ($GLOBALS["DEBUG"]) {
				print_r("PARSE ERROR (primary)!\n");
			}
			$e = new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], "RESTful Error: Bad request.");
			$e->kill();
		}
	}
	else {
		// Otherwise get all countries
		
		require "country-all.php";
		try {
			$handler = new CountryAll($REST_vars, $DBs, $User);
		} catch (Error $e) {
			$e->kill();
		}
	}
	
	
	
	if ($GLOBALS["DEBUG"]) {
		print_r($REST_vars);
	}
	
?>