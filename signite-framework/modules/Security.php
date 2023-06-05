<?php

namespace Signite\Modules;

define("SECURITY_KEY", "5DwvMHn3V4mWPgsKHWyjAXrHVCdPsJaxiPjdtfEuGkWDlb7hqFcb8eT3K5F7l25/1RPe8H+bvQ/EGqEi4XxqnobBMaLXP7VEJOo7DuxQDiLwXWHyzj3v9C6ozuw+yKZDJ9BG6SV2BG4RURi6C9W9WF4ivFWRwiEA1c2A+o1A//DyovSKI5WfvHUlS74LvvJRqbqTWT2O4m+9X24+C4FnkzOlYGI6euuRjv01NEHkvxuBh6pKz3VtimZM0QD0Lpnclfn2PwTUyTL8BTCcfmzrucGFanLdLoXDA7ZIkBYWUMH71A1V5Eg47m9pyrn5sj9TFAWmWPYdeWHw1KpmKPz78A==");


// class to generate JWT
class JWT {
    private $secret_key;
    private $issuer;
    private $audience;
    private $expiration;
    private $notBefore;
    private $issuedAt;
    private $jwt;
    
    public function __construct($secret_key, $issuer, $audience, $expiration, $notBefore, $issuedAt) {
        $this->secret_key = $secret_key;
        $this->issuer = $issuer;
        $this->audience = $audience;
        $this->expiration = $expiration;
        $this->notBefore = $notBefore;
        $this->issuedAt = $issuedAt;
    }
    
    public function generateJWT($data) {
        $header = $this->base64url_encode(json_encode(array("typ" => "JWT", "alg" => "HS256")));
        $payload = $this->base64url_encode(json_encode(array("iss" => $this->issuer, "aud" => $this->audience, "exp" => $this->expiration, "nbf" => $this->notBefore, "iat" => $this->issuedAt, "data" => $data)));
        $signature = hash_hmac("sha256", $header . "." . $payload, $this->secret_key, true);
        $signature_encoded = $this->base64url_encode($signature);
        $this->jwt = $header . "." . $payload . "." . $signature_encoded;
        return $this->jwt;
    }

    public function get_bearer_token() {
        $headers = $this->get_authorization_header();
    	
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public function get_authorization_header(){
    	$headers = null;
    	
    	if (isset($_SERVER['Authorization'])) {
    		$headers = trim($_SERVER["Authorization"]);
    	} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
    		$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    	} else if (function_exists('apache_request_headers')) {
    		$requestHeaders = apache_request_headers();
    		// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
    		$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
    		//print_r($requestHeaders);
    		if (isset($requestHeaders['Authorization'])) {
    			$headers = trim($requestHeaders['Authorization']);
    		}
    	}
    	
    	return $headers;
    }

    public static function sbase64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function verifyJWT($jwt, $secret_key) {
        $tokenParts = explode('.', $jwt);
    	$header = base64_decode($tokenParts[0]);
    	$payload = base64_decode($tokenParts[1]);
    	$signature_provided = $tokenParts[2];
    
    	// check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
    	$expiration = json_decode($payload)->exp;
    	$is_token_expired = ($expiration == false) ? false : ($expiration - time()) < 0;
    
    	// build a signature based on the header and payload using the secret
    	$base64_url_header = JWT::sbase64url_encode($header);
    	$base64_url_payload = JWT::sbase64url_encode($payload);
    	$signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $secret_key, true);
    	$base64_url_signature = JWT::sbase64url_encode($signature);
    
    	// verify it matches the signature provided in the jwt
    	$is_signature_valid = ($base64_url_signature === $signature_provided);
    	
    	if ($is_token_expired || !$is_signature_valid) {
    		return false;
    	} else {
    		return json_decode($payload, true);
    	}
    }
    
    public function getJWT() {
        return $this->jwt;
    }
}

class Security {
    public static function generateJWT($data) {
        $jwtObject = new JWT(SECURITY_KEY, "Signite", "Signite", time() + 3600, time(), time());
        $jwt = $jwtObject->generateJWT($data);
        return $jwt;
    }

    public static function generateBlowfishHash($password) {
        return crypt($password, '$2y$10$' . substr(md5(uniqid(rand(), true)), 0, 22));
    }

    public static function generatePasswordHash($password) {
        return Security::generateBlowfishHash($password);
    }

    public static function verifyJWT($jwt) {
        return JWT::verifyJWT($jwt, SECURITY_KEY);
    }

    public static function verifyPasswordHash($password, $hash) {
        return crypt($password, $hash) == $hash;
    }

    public static function safePost() {
        foreach ($_POST as $key => $value) {
            $_POST[$key] = htmlspecialchars($value);
        }
    }
}
