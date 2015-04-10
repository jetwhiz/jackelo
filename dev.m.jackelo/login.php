<?php
	
	function login($getquery) {
		global $_USER;
		
		// If not logged in, refuse service 
		if (!array_key_exists('uid', $_USER)) {
			echo "login";
			exit;
		}
		
		
		// Parse string given into array
		$expl = explode("-", $getquery, 2);
		$myGET["cnonce"] = $expl[0];
		$myGET["dh"] = $expl[1];
		
		
		// Clean client nonce 
		$cnonce = preg_replace("/[^A-Za-z0-9 ]/", '', $myGET["cnonce"]);
		
		// Clean DH key -- Diffie-Hellman key (mobile login) 
		$dhKey = preg_replace("/[^A-Za-z0-9]/" , '', $myGET["dh"]);
		
		
		
		// Get cryptographic server nonce 
		$nonce = openssl_random_pseudo_bytes(5, $cstrong);
		if (!$cstrong) {
			echo "weak";
			exit;
		}
		
		
		// Prepare strengthened key 
		$ini_array = parse_ini_file("conf.ini", true);
		if (!$ini_array) {
			echo "can't get conf";
			exit;
		}
		$hash = $ini_array["auth"]["key"];
		$key = pack('H*', $hash);
		
		
		// Prepare plaintext message to send to other server 
		$plaintext = "{" . 
			"\"name\": \"" . $_USER['uid'] . "\""
			. ", \"date\": \"" . date("Y-m-d H:i:s T") . "\""
			. ", \"snonce\": \"" . bin2hex($nonce) . "\"" 
			. ", \"cnonce\": \"" . $cnonce . "\""
			. ", \"DHkey\": \"" . $dhKey . "\""
			. "}";
		//echo $plaintext . "<br><br>";
		
		
		// Prepare CBC encoding IV 
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		
		// Encryption 
		$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plaintext, MCRYPT_MODE_CBC, $iv);
		
		// Package ciphertext with IV 
		$ciphertext = $iv . $ciphertext;
		
		// base-64 encode ciphertext (to send as JSON packet) 
		$ciphertext_base64 = base64_encode($ciphertext);
		
		// Generate HMAC of iv-ciphertext
		$hmac = hash_hmac('ripemd160', $ciphertext_base64, $key);
		
		
		// Send to client 
		echo  "{ \"session\": \"" . $hmac . '@' . $ciphertext_base64 . "\" }";
	}
	
?>
