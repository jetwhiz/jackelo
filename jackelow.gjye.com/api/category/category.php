<?
	/* RESTful queries can take the forms:
		(/)?				-- get all categories 
		#(/)?				-- get info for given categoryID 
		filter/STRING(/)?	-- get all categories that contain STRING
	*/
	
	
	
	// Retrieve categoryID 
	if ( count($queryArray) ) {
		
		// No more options given 
		if ( is_numeric($queryArray[0]) ) {
			
			// Country ID was supplied as a token 
			$REST_vars["categoryID"] = intval(array_shift($queryArray), 10);
			
			// Debug dump 
			if ($GLOBALS["DEBUG"]) {
				print_r("CATEGORY ID: " . $REST_vars["categoryID"] . "\n");
			}
			
			require "category-id.php";
			try {
				$handler = new CategoryID($REST_vars, $DBs, $User);
			} catch (Error $e) {
				$e->kill();
			}
		}
		
		// Filter request 
		elseif( $queryArray[0] == "filter" ) {
			array_shift($queryArray);
			
			// No filter terms supplied 
			if ( !count($queryArray) ) {
				$e = new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], "Category Error: You did not supply search terms.");
				$e->kill();
			}
			
			
			// Clean up search terms 
			$search = array_shift($queryArray);
			$REST_vars["terms"] = preg_replace("/[^\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w ]/u", "", $search);
			
			
			// Debug dump 
			if ($GLOBALS["DEBUG"]) {
				print_r("TERMS: " . $REST_vars["terms"] . "\n");
			}
			
			require "category-filter.php";
			try {
				$handler = new CategoryFilter($REST_vars, $DBs, $User);
			} catch (Error $e) {
				$e->kill();
			}
		}
		
		// Invalid option, ignore 
		else {
			if ($GLOBALS["DEBUG"]) {
				print_r("PARSE ERROR (primary)!\n");
			}
		}
	}
	else {
		// Otherwise get all categories 
		
		require "category-all.php";
		try {
			$handler = new CategoryAll($REST_vars, $DBs, $User);
		} catch (Error $e) {
			$e->kill();
		}
	}
	
	
	
	if ($GLOBALS["DEBUG"]) {
		print_r($REST_vars);
	}
	
?>