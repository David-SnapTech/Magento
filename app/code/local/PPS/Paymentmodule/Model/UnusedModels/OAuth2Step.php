<?php
  class ParamsAndHeaders {
      
      protected $params;
      public $link;
      protected $method;
      protected $query;
      protected $consumerKey;
      protected $consumerSecret;
      protected $base_string;
      public $headers;
      protected $response;
      protected $token;
      protected $tokenSecret;
      
      
        function __construct($method,$link,$consumerKey,$consumerSecret,$token,$tokenSecret) {
            Mage::log("Constructor");
            $this->method=$method;
            $this->link=$link;
            $this->consumerKey=$consumerKey;
            $this->consumerSecret=$consumerSecret;
            $this->token = $token;
            $this->tokenSecret = $tokenSecret;
        }
        
        
       
        function setupParams(){
            
            $this->params = array(
               'oauth_consumer_key'=>$this->consumerKey,
               'oauth_nonce'=>'1234',
               'oauth_signature_method'=>'HMAC-SHA1',
               'oauth_timestamp'=> '1360271497',
               'oauth_version'=>'1.0'
               );
            
            if($this->token) {
                $this->params['oauth_token']=$this->token;
            }
            ksort($this->params);


             return   $this->params;
          
      }

        function makeQuery(){
          
          
          foreach (  $this->setupParams() as $key=>$value) {
            $query[] = urlencode_oauth($key).'='.urlencode_oauth($value);
            }
          $query = implode('&',$query);
          $this->query=$query;
          
          return $this->query;
  
      }
      
        function makeBaseString(){
          $parts = array(
                        urlencode_oauth($this->method),
                        urlencode_oauth($this->link),
                        urlencode_oauth($this->makeQuery())
                        );
          
          $this->base_string = implode('&',$parts);
          return $this->base_string;
      }
    
       function makeSignature(){
         
         $key = urlencode_oauth($this->consumerSecret) . '&';
         
         if ($this->tokenSecret){
            $key .= urlencode_oauth($this->tokenSecret);
         }
       
        
        $signature = base64_encode(hash_hmac('sha1',$this->makeBaseString(),$key,true));
        $this->params['oauth_signature'] = $signature;
        return $this->params;
     }
     
       function createHeader($JSON=null){
         
        foreach ($this->makeSignature() as $key=>$value) {
            $str[] = urlencode_oauth($key) . '="'.urlencode_oauth($value).'"';
            }

        $str = implode(',',$str);
    
        $headers = array(
            'Authorization: OAuth '.$str,
            'Content-Type: application/json',
            'Accept: application/json'
            //'Connection: close'
            );  
        $this->headers=$headers;
        return $this->headers;
     }
     
}

function urlencode_oauth($str) {
  return
    str_replace('+',' ',str_replace('%7E','~',rawurlencode($str)));
  }
  
function sendRequest($headers,$link){
         
           
           $options = array(
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_URL => $link,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true, 
                CURLOPT_SSL_VERIFYPEER => false
                ); 
         
        $ch = curl_init(); 
        curl_setopt_array($ch, $options); 
        $res = curl_exec($ch); 
        curl_close($ch);

        return $res;
     }

?>
