<?php
	
	function login($cnonce) {
		global $_USER;
		
		// Clean client nonce 
		$cnonce = preg_replace("/[^A-Za-z0-9 ]/", '', $cnonce);
		
		
		// If not logged in, refuse service 
		if (!array_key_exists('uid', $_USER)) {
			echo "login";
			exit;
		}
		
		
		
		
		// Get cryptographic nonce 
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
		$key = pack('H*', hex2bin($hash));
		
		
		// Prepare plaintext message to send to other server 
		$plaintext = "{" . 
			"'name': '" . $_USER['uid'] . "'"
			. ", 'date': '" . date("Y-m-d H:i:s T") . "'"
			. ", 'snonce': '" . bin2hex($nonce) . "'" 
			. ", 'cnonce': '" . $cnonce . "'"
			. "}";
		//echo $plaintext . "<br><br>";
		
		
		// Prepare CBC encoding IV 
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		
		
		// Encryption 
		$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plaintext, MCRYPT_MODE_CBC, $iv);
		
		
		// Package ciphertext with IV and base-64 encode 
		$ciphertext = $iv . $ciphertext;
		$ciphertext_base64 = base64_encode($ciphertext);
		
		echo  "{ \"session\": \"" . $ciphertext_base64 . "\" }";
		
		
		
		
		// DECRYPT 
		/*
		$ciphertext_dec = base64_decode($ciphertext_base64);
		$iv_dec = substr($ciphertext_dec, 0, $iv_size);
		$ciphertext_dec = substr($ciphertext_dec, $iv_size);
		$plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
		*/
		
		//echo  $plaintext_dec . "<br><br>";
		
		
		
		
		/*
		// Key strengthening 
		$hash = "CS8803";
		for ($i = 0; $i < 5; ++$i) {
			$salt = mcrypt_create_iv(3, MCRYPT_DEV_URANDOM);
			$hash .= crypt("$hash team-technopole-2000", $salt);
		}
		echo bin2hex($hash);
		*/
	}
	
?>
