<?php
class PPS_Paymentmodule_Model_ppsProxy
{
    private $oAuthHost;
    private $token;
    private $tokenSecret;
    private $request_token_url;
    private $access_token_url;
    private $authorize_token_url;
    private $consumerKey;
    private $consumerSecret;
    private $params;
    private $oa;

    const POST =  "POST";
    const PUT = "PUT";
    const GET = "GET";
    const DELETE = "DELETE";
    const POST_RESPONSE = 201;
    const PUT_RESPONSE = 200;
    const GET_RESPONSE = 200;
    const DELETE_RESPONSE = 204;

    //Define component URL's
    private $standardGateway = "https://api.prioritypaymentsystems.com/checkout/v1.1";
    private $testGateway = "https://api.prioritypaymentsystems.com/checkout/v1.1";

    public function __construct($consumerKey, $consumerSecret, $testMode = false)
    {
        //Change the endpoint if the website is in test mode
        $testMode ? $this->oAuthHost = $this->testGateway : $this->standardGateway;
        $this->request_token_url = $this->oAuthHost."/OAuth/1A/RequestToken";
        $this->access_token_url = $this->oAuthHost."/OAuth/1A/AccessToken";
        $this->authorize_token_url = $this->oAuthHost."/OAuth/1A/Authorize";

        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->oa = new PPS_Paymentmodule_Model_Oauth();

        //Generate OAuth tokens
        $this->generateOAuthTokens();

        $this->params = array('oauth_callback'=> 'callback.php',
            'oauth_consumer_key'=>$this->consumerKey,
            'oauth_nonce'=> sha1(microtime()),
            'oauth_signature_method'=>'HMAC-SHA1',
            'oauth_timestamp'=> time(),
            'oauth_version'=>'1.0'
        );
    }

    public function getPaymentStatus($orderId)
    {
        $serverResponse = $this->getOrder($orderId);
        return $this->parseHeaderForString($serverResponse, "status");
    }

    //Retrieve the OAuth tokens from PPS
    private function generateOAuthTokens()
    {
        $this->params = array('oauth_callback'=> 'callback.php',
            'oauth_consumer_key'=>$this->consumerKey,
            'oauth_nonce'=> sha1(microtime()),
            'oauth_signature_method'=>'HMAC-SHA1',
            'oauth_timestamp'=> time(),
            'oauth_version'=>'1.0');

        $initCall = array();
        $access_data = array();
        //Get request tokens
        parse_str($this->startProcess($this->request_token_url, 'POST'), $initCall);
        //Get access tokens
        parse_str($this->getAccessToken($initCall['oauth_token'], $initCall['oauth_token_secret'], $this->access_token_url,'POST'), $access_data);

        $this->token = $access_data['oauth_token'];
        $this->tokenSecret = $access_data['oauth_token_secret'];

        // Check for valid tokens
        if (empty($this->token) || empty($this->tokenSecret)){
            Mage::log('PRIORITY DIRECT PAYMENT ERROR: Empty token response.');
            return false;
        }
        else
            return true;
    }

    public function startProcess($link, $method) {
        $str = $this->buildHeader('', '', $link, $method);
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

    public function sendData($link, $method, $info, $dataToAppend) {
        $link = $this->oAuthHost.$link;
        if(!empty($dataToAppend))
        {
            foreach($dataToAppend as $data => $key)
                $link .= +"&$key=$data";
        }


        $str = $this->buildHeader($this->token,
            $this->tokenSecret,
            $link,
            $method);
        $unwrappedInfo = null;
        if(!is_null($info))
            foreach($info as $i)
            {
                if(!is_null($i))
                    $unwrappedInfo = $i;
            }
        $furtherUnwrappedInfo = array();
        if(!is_null($unwrappedInfo))
            foreach($unwrappedInfo as $key => $value)
            {
                if(!($value == "") && !is_array($value))
                    $furtherUnwrappedInfo[$key] = $value;
            }

        $options = $this->determineOptions($str, $unwrappedInfo, $method, $link);

        if($options == null)
        {
            Mage::log("HTTP method error");
            Mage::throwException("Order did not complete");
        }

        if(($method == self::GET) || ($method == self::DELETE))
            return $this->sendRequest($options, true);
        else
            return $this->sendRequest($options);
    }

    //build a HTTP header
    private function buildHeader($token, $tokenSecret, $link, $method) {
        $params = $this->params;
        if (isset($token)) {
            if (!empty($token)) {
                $params['oauth_token'] = $token;
            }
        }
        ksort($params);

        // Prepare URL-encoded query string
        $parts = $this->buildString($params, $method, $link);

        $params['oauth_signature'] = $this->buildSignature($parts, $this->consumerSecret, $tokenSecret);

        $str = array();
        foreach ($params as $k => $value) {
            $str[] = $k.'="'.$this->urlencode_oauth($value).'"';
        }

        $str = implode(',', $str);
        return $str;
    }

    private function buildString($params, $method, $link){
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
        $base_string = implode('&', $parts);
        $key = $this->urlencode_oauth($consumerSecret).'&'.$this->urlencode_oauth($tokenSecret);
        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }

    private function determineOptions($str, $unwrappedInfo, $method, $link)
    {
        switch ($method)
        {
            case self::POST:
                $headers = array (
                    'Authorization: OAuth '.$str,
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'echo=true',
                    'Content-Length: ' . strlen(json_encode($unwrappedInfo))
                );

                $options = array (
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_URL => $link,
                    CURLOPT_HEADER => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($unwrappedInfo),
                    CURLOPT_SSL_VERIFYPEER => false
                );

                break;

            case self::GET :
                $headers = array (
                    'Authorization: OAuth '.$str,
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'echo=true',
                    'Content-Length: 0'
                );
                $options = array (
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_HEADER => true,
                    CURLOPT_RETURNTRANSFER => false,
                    CURLOPT_URL => $link,
                    CURLOPT_HTTPGET => true,
                    CURLOPT_SSL_VERIFYPEER => false
                );
                break;

            case self::PUT :

                $headers = array (
                    'Authorization: OAuth '.$str,
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'echo=true',
                    'Content-Length: ' . strlen(json_encode($unwrappedInfo))
                );
                $options = array (
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_URL => $link,
                    CURLOPT_HEADER => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($unwrappedInfo),
                    CURLOPT_SSL_VERIFYPEER => false
                );
                break;

            case self::DELETE :
                $headers = array (
                    'Authorization: OAuth '.$str,
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'echo=true',
                    'Content-Length: ' . strlen(json_encode($unwrappedInfo)),
                    'connection: close'
                );

                $options = array (
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_HEADER => true,
                    CURLOPT_RETURNTRANSFER => false,
                    CURLOPT_URL => $link,
                    CURLOPT_POSTFIELDS => json_encode($unwrappedInfo),
                    CURLOPT_CUSTOMREQUEST => "DELETE",
                    CURLOPT_SSL_VERIFYPEER => false
                );
                break;

            default :
                $options = null;
        }
        return $options;
    }

    private function getAccessToken($token, $tokenSecret, $link, $method) {
        $str = $this->buildHeader($token, $tokenSecret, $link, $method);
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

    private function sendRequest($options, $getHeader = false) {
        $ch = curl_init();

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        curl_close($ch);
        if ($getHeader == true){
            $response = ob_get_contents();
            ob_clean();
            return $response;
        }

        return $response;
    }

    public function getIdFromLocationHeader($response) {
        if( $response &&  strlen($response) > 0 ) {
            $content = explode( '\r\n\r\n', $response );
            if( $content && count($content) > 0 )
            {
                $headers = explode( "\r\n", $content[0]);
                if( $headers && count($headers) > 0 )
                {
                    foreach( $headers as $header )
                    {
                        $headerName = trim(substr($header, 0, strpos($header, ":")));
                        if(strcasecmp($headerName, 'location') == 0){
                            return   trim(substr($header, strrpos($header, "/")+1));  //+1 to remove the leading '/'
                        }
                    }
                }
            }
        }
    }

    public function parseHeaderForLocation($content) {
        $words = explode(' ', $content);
        foreach ($words as $word) {
            if(preg_match("/http(.*)/", $word, $results)) {
                return preg_replace('/[^\P{C}\n]+/u', '', $results[0]);  //Only return the complete URL and strip invisible characters
            }
        }
    }

    public function parseHeaderForString($content, $searchString, $delem = false) {
        $content = str_replace("\"", '', $content);
        if($delem) {
            $words = explode($delem, $content);
        }
        else {
            $words = explode(',', $content);
        }
        foreach ($words as $word) {
            if(preg_match("/". $searchString . "(.*)/", $word, $results)) {
                $amount = explode(':', $results[0]);
                return preg_replace('/[^\P{C}\n]+/u', '', $amount[1]);  //Remove any invisible characters
            }
        }
    }

    private function urlencode_oauth($str) {
        return str_replace( '+', ' ', str_replace('%7E','~',rawurlencode($str)));
    }
}//End class