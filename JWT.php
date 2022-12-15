<?php

    // JSON Web Token class
    Class JWT {
        protected $headers = ['alg'=>'HS256','typ'=>'JWT'];
        protected $secret = "dfsjfsdfdsa£x-'knjgsd###as";
        
        private function base64url_encode($str) {
            // Encode string
            return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
        }

        public function generate($payload) {
            // Generate payload (combine headers, payload and signature)

            // Encode headers
            $headers_encoded = $this->base64url_encode(json_encode($this->headers));
        
            // Encode payload
            $payload_encoded = $this->base64url_encode(json_encode($payload));
            
            // Generate signature from enocded headers and payload, used to encode signature
            $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $this->secret, true);
            $signature_encoded = $this->base64url_encode($signature);
            
            // Combine as JWT
            $jwt = "$headers_encoded.$payload_encoded.$signature_encoded";
    
            return $jwt;
        }

        public function validate($jwt) {
            // split the jwt
            $tokenParts = explode('.', $jwt);
            if (count($tokenParts) === 3) {
                $header = base64_decode($tokenParts[0]);
                $payload = base64_decode($tokenParts[1]);
                $signature_provided = $tokenParts[2];
            
                // build a signature based on the header and payload using the secret
                $base64_url_header = $this->base64url_encode($header);
                $base64_url_payload = $this->base64url_encode($payload);
                $signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $this->secret, true);
                $base64_url_signature = $this->base64url_encode($signature);
            
                // verify it matches the signature provided in the jwt
                $is_signature_valid = ($base64_url_signature === $signature_provided);
                
                if (!$is_signature_valid) {
                    return FALSE;
                } else {
                    return TRUE;
                }
            } else {
                return FALSE;
            }
            
        }

        public function decode($token) {
            // Decode token, return payload
            return json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))));
        }
    }

    // Create instance
    $jwt = new JWT();

    // Get token from Authorization header
    $headers = apache_request_headers();
    $authorization = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    $token = '';

    // If Authorization header is present, get the token from it
    if ($authorization) {
        $token = explode(' ', $authorization)[1];
    }

?>