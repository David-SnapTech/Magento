<?php
/*
 *
 */

class PPS_Paymentmodule_Model_ppsMehods {

    /**
     * @param $amount
     * @param bool $asFloat
     * @return float|string
     */
    public function formatAmount($amount, $asFloat = false)
    {
        $amount = sprintf('%.2F', $amount);
        return $asFloat ? (float)$amount : $amount;
    }

    public function parseHeaderForLocation($content) {
        //!!Should be https when moved into productino !!//
        $words = explode(' ', $content);
        foreach ($words as $word) {
            if(preg_match("/http(.*)/", $word, $results)) {
                Mage::log("Found Location!");
                Mage::log($results);
                return preg_replace('/[^\P{C}\n]+/u', '', $results[0]);  //Only return the complete URL and strip invisible characters
            }
        }
    }

    public function parseHeaderForString($content, $searchString, $delem = false) {
        //!!Should be https when moved into productino !!//
        $content = str_replace("\"", '', $content);
        if($delem) {
            $words = explode($delem, $content);
        }
        else {
            $words = explode(',', $content);
        }
        foreach ($words as $word) {
            if(preg_match("/". $searchString . "(.*)/", $word, $results)) {
                Mage::log("Found Location!");
                $amount = explode(':', $results[0]);
                return preg_replace('/[^\P{C}\n]+/u', '', $amount[1]);  //Remove any invisible characters
            }
        }
    }

    /**
     * called to parse a HTTP header for the settled amount
     * @param $content
     * @return mixed
     */
    public function parseHeaderForSettledAmount($content) {
        //!!Should be https when moved into productino !!//
        $content = str_replace("\"", '', $content);
        $words = explode(',', $content);
        foreach ($words as $word) {
            if(preg_match("/settledAmount(.*)/", $word, $results)) {
                Mage::log("Found Location!");
                $amount = explode(':', $results[0]);
                return preg_replace('/[^\P{C}\n]+/u', '', $amount);  //Remove any invisible characters
            }
        }
    }


    //!!Can probably be removed before production!!//
    /**
     * called to parse a HTTP header for its ID
     * @param $content
     * @return mixed
     */
    public function parseHeadersForId($content) {
        return sizeof((explode("/", $content)) - 1);
        //$values = explode("/", $content);
        //return $values[sizeof($values) - 1];
    }

    //!! Needs to be changed to https before production !!//
    /**
     * called to parse a HTTP header for its payment ID
     * @param $content
     * @return string
     */
    public function parseHeadersForPaymentID($content) {
        Mage::log("Starting function parseHeadersForPaymentID");
        if ($c = preg_match_all ( "/(Location)(:)(\s+)(http)(:)(\/)(\/).*?\/.*?\/.*?(\/)(\d+).*?\/(\d+)/is",$content,$matches )) {
            $paymentid = $matches[10][0];
            return $paymentid;
        } else {
            return '';
        }
    }

    //!! Needs to be changed to https before production !!//
    /**
     * called to parse a HTTP header for its order ID
     * @param $content
     * @return string
     */
    public function parseHeaderForOrderID($content) {
        if ($c = preg_match_all ( "/(Location)(:)(\s+)(http)(:)(\/)(\/).*?\/.*?\/.*?(\/)(\d+)/is",$content,$matches )) {
            $paymentid = $matches[9][0];
            Mage::log("Found order id");
            return $paymentid;
        } else {
            Mage::log("Couldnt' find order ID");
            return '';
        }
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
                            return   trim(substr($header, strrpos($header, "/")));
                        }
                    }
                }
            }
        }
    }

    public function parseResponseForAuthCode($stringToParse) {
        return "1337-Code";
    }

    public function parseResponseForAuthToken($stringToParse) {
        return "1337-Token";
    }

}//End class