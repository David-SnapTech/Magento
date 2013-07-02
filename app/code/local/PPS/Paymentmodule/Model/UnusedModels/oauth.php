<?php 

class PPS_Paymentmodule_Model_Oauth {

    protected $token = '';
    protected $tokenSecret = '';
    protected $headers;


	public function startProcess($consumerKey, $consumerSecret, $link, $method, $params) {
		//echo "Starting function startProcess <br>";

	  	$str = $this->buildHeader($consumerSecret, '', '', $link, $method, $params);
	  	$headers = array(
	  			'Authorization: OAuth ' . $str,
	  			'Content-Length: 0',
	  			'Connection: close'
	  	);
	  	$options = array(
	  			CURLOPT_HTTPHEADER => $headers,
	  			CURLOPT_URL => $link,
	  			CURLOPT_POST => true,
	  			CURLOPT_RETURNTRANSFER => true,
	  			CURLOPT_SSL_VERIFYPEER => false
	  	);
	  	return $this->sendRequest($options);
	}

	public function getAccessToken($consumerKey, $consumerSecret, $token, $tokenSecret, $link, $method, $params) {

		$str = $this->buildHeader($consumerSecret, $token, $tokenSecret, $link, $method, $params);
        //Mage::throwException("Token: " . $token . " TokenSecret: " . $tokenSecret);
	  	$headers = array (
	  			'Authorization: OAuth ' . $str,
	  			'Content-Length: 0',
                'Connection: close'
        );

	  	$options = array (
	  			CURLOPT_HTTPHEADER => $headers,
	  			CURLOPT_URL => $link,
	  			CURLOPT_POST => true,
	  			CURLOPT_RETURNTRANSFER => true,
	  			CURLOPT_SSL_VERIFYPEER => false
	  	);

	  	return $this->sendRequest($options);
	}

	private function buildHeader($consumerSecret, $token, $tokenSecret, $link, $method, $params) {
		if (isset($token)) {
	  		if (!empty($token)) {
	  			$params['oauth_token'] = $token;
	  		}
	  	}
	  	ksort($params);
	  	
	  	// Prepare URL-encoded query string
	  	$parts = $this->buildString($params, $method, $link);
	  
	  	$params['oauth_signature'] = $this->buildSignature($parts, $consumerSecret, $tokenSecret);
	  
	  	$str = array();
	  	foreach ($params as $k => $value) {
	  		$str[] = $k.'="'.$this->urlencode_oauth($value).'"';
	  	}
	  
	  	$str = implode(',', $str);
	  	return $str;
	}
  
	private function buildString($params, $method, $link){
		//echo "Starting function buidString <br>";
	  	$q = array();
	  	foreach ($params as $key => $value) {
	  		$q[] = $this->urlencode_oauth($key).'='.$this->urlencode_oauth($value);
	  	}
	  
	  	$q = implode('&', $q);
	  
	  	// Build array to sign
	  	$parts = array (
	  			$method,
	  			$this->urlencode_oauth($link),
	  			$this->urlencode_oauth($q)
	  	);
	  	return $parts;
	}
  
	private function buildSignature($parts, $consumerSecret, $tokenSecret){
		//echo "Starting function buildSignature <br>";
		$base_string = implode('&', $parts);
	  	$key = $this->urlencode_oauth($consumerSecret).'&'.$this->urlencode_oauth($tokenSecret);
	  	$signature = base64_encode(hash_hmac('sha1', $base_string, $key, true));
	  	//echo "The signature is: " . $signature;
	  	//echo "The base string is: " . $base_string;
	  	return $signature;
	}
  
	private function sendRequest($options, $getheader = 'false') {
		Mage::log("sendRequest function started.  Options:");
		Mage::log($options);
		Mage::log("sendRequest function end");
		$ch = curl_init();
		if($getheader == 'true') {

		}
		//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
		curl_setopt_array($ch, $options);
        Mage::log("Starting OAuth Options");
        foreach($options as $option)
            Mage::log($option);
        Mage::log("Ending OAuth Optoins");
		$response = curl_exec($ch);
		curl_close($ch);
		Mage::log("Server Response: ");
		Mage::log($response);
		Mage::log("End server response");
		//echo ($response);
		//echo "<br>***** End Server Response *****<br>";
		if ($getheader == 'true'){
			$response = ob_get_contents();
			ob_clean();
		}
		return $response;
	}

    private function sendDeleteRequest($options, $getheader = 'false') {
        Mage::log("sendDeleteRequest function started.  Options:");
        Mage::log($options);

        $ch = curl_init();
        if($getheader == 'true') {

        }
        //curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        Mage::log("Server Response: ");
        Mage::log($response);
        Mage::log("End server response");
        //echo ($response);
        //echo "<br>***** End Server Response *****<br>";
        if ($getheader == 'true'){
            $response = ob_get_contents();
            ob_clean();
        }
        return $response;
    }

    public function sendDeleteData($consumerSecret, $link, $method, $token, $tokenSecret, $params, $merchId, $info) {
        Mage::log("Starting function sendData");
        //echo "Starting function sendData <br>";
        $str = $this->buildHeader($consumerSecret, $token, $tokenSecret, $link, $method, $params);
        $headers = array (
            'Authorization: OAuth '.$str,
            'Content-Type: application/json',
            'Accept: application/json',
            'echo=true',
            'Content-Length: ' . strlen(json_encode($info),
            'connection: close')
        );

        $options = array (
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_URL => $link,
            CURLOPT_POSTFIELDS => json_encode($info),
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_SSL_VERIFYPEER => false
        );
        Mage::log("Create Payment, Options:");
        Mage::log($options);
        return $this->sendDeleteRequest($options, 'true', $link, $headers, $info);
    }//End createPayment function


    private function urlencode_oauth($str) {
  		return str_replace( '+', ' ', str_replace('%7E','~',rawurlencode($str)));
	}

	public function sendData($consumerSecret, $link, $method, $token, $tokenSecret, $params, $merchId, $info) {
		$str = $this->buildHeader($consumerSecret, $token, $tokenSecret, $link, $method, $params);
		$headers = array (
				'Authorization: OAuth '.$str,
				'Content-Type: application/json',
				'Accept: application/json',
	  			'echo=true',
				'Content-Length: ' . strlen(json_encode($info))
		);
	
		$options = array (
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_HEADER => true,
				CURLOPT_RETURNTRANSFER => false,
				CURLOPT_URL => $link,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode($info),
				CURLOPT_SSL_VERIFYPEER => false
		);
		Mage::log("Create Payment, Options:");
		Mage::log($options);
		return $this->sendRequest($options, 'true');
	}//End createPayment function
	
	
    function createHeader($JSON=null){
        echo "Starting function createHeader ";

        foreach ($this->makeSignature() as $key=>$value) {
            Mage::log("Key: $key  Value: $value");
            $str[] = $this->urlencode_oauth($key) . '="' . $this->urlencode_oauth($value).'"';
            }

        $str = implode(',',$str);

        $headers = array(
            'Authorization: OAuth '.$str,
            'Content-Type: application/json',
            'Accept: application/json',
	    	'Content-Length: 0'
            );  
        


        $this->headers=$headers;
        return $this->headers;
     }//End createHeader function

	public function getResponse($consumerSecret, $link, $method, $token, $tokenSecret, $params, $merchId, $info) {
        Mage::log("Starting function getResponse");
        //$link = 'http://test.api.mxmerchant.com/v1/payment/' . $orderId;
        $str = $this->buildHeader($consumerSecret, $token, $tokenSecret, $link, $method, $params);
        $headers = array (
            'Authorization: OAuth '.$str,
            'Content-Type: application/json',
            'Accept: application/json',
            'echo=true',
            'Content-Length: 0'  // . strlen(json_encode($info))
        );
		$ch = curl_init();
		$options = array (
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_HEADER => true,
				CURLOPT_RETURNTRANSFER => false,
				CURLOPT_URL => $link,
				//CURLOPT_POST => true,
				CURLOPT_HTTPGET => true,
				//CURLOPT_POSTFIELDS => json_encode($info),
				CURLOPT_SSL_VERIFYPEER => false
		);
        curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888'); //!!Needs to be removed before put into production!!//
		curl_setopt_array($ch, $options);
		//curl_setopt_array($ch, $options);
		$response = curl_exec($ch);
		curl_close($ch);
		Mage::log("Server Response: usingGET");
		Mage::log($response);
        $response = ob_get_contents();
        ob_clean();
	    return $response;
	}




}//End class

?>