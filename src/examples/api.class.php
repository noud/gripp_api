<?php

/**
 * @method array tag_create(array $fields)
 * @method array tag_delete(int $id)
 * @method array tag_get(array $filters, array $options)
 * @method array tag_getone(array $filters, array $options)
 * @method array tag_update(int $id, array $fields)
 */
class com_gripp_API{

    /*
     * Free to use in any way.
     * Created by Gripp.com B.V.
     */

    private $apiconnectorversion = 3011;
    private $apitoken;
    private $url;
    private $id = 1;
    private $batchmode = false;
    private $requests = array();
    private $reponseHeaders = array();

    //experimental auto paging
    private $autopaging = false;
    private $autopaging_in_progress = false;
    private $autopaging_maxresults = 250; //max results per iteration. Fixed value also enforced on serverside.
    private $autopaging_result = array();
    private $lastres = null;

    //added functionality to enable single batch request with autopagina enabled, for easier migration of existing scripts to APIv3

    public function __construct($apitoken, $url = null){
        $url = 'https://api.gripp.com/public/api3.php'; //mandatory from 2018-02-01;
        set_time_limit(0);
        if (!$apitoken){
            throw new \Exception('Api token is required');
        }
        if (!$url){
            throw new \Exception('Url is required');
        }

        if (!strstr($url, 'api3.php')){
            throw new \Exception('This API connector is suitable for the Gripp API v3 only.');
        }

        $this->apitoken = $apitoken;
        $this->url = $url;
    }

    public function getVersion(){
        return $this->apiconnectorversion;
    }

    public function setUrl($url){
        $this->url = $url;
    }

    public function setBatchmode($b){
        $this->batchmode = $b;
    }

    public function getBatchmode(){
        return $this->batchmode;
    }

    public function setAutoPaging($b){
        $this->autopaging = $b;
    }

    public function getAutoPaging(){
        return $this->autopaging;
    }

    public function handleResponseErrors($responses){
        $messages = array();

        foreach($responses as $response){
            if (array_key_exists('error', $response) && !empty($response['error'])){
                if (array_key_exists('error_code', $response)){
                    switch($response['error_code']){
                        default:
                            $messages[] = $response['error'];
                            break;
                    }
                }
                else {
                    $messages[] = $response['error'];
                }
            }
            else{
                unset($this->requests[$response['id']]);
            }
        }

        if (count($messages) > 0){
            throw new \Exception(implode("\n", $messages));
        }

        return $responses;
    }

    function getRawPost(){
        $post = array();

        foreach($this->requests as $r){
            $post[] = array(
                'apiconnectorversion' => $this->apiconnectorversion,
                'method' => $r['class'].'.'.$r['method'],
                'params' => $r['params'],
                'id' => $r['id']
            );
        }

        return $post;
    }

    function run(){
        //call
        $post = $this->getRawPost();
        if (count($post) > 0){
            $post_string = json_encode($post);
            $result = $this->send($post_string);
            $result_decoded = json_decode($result, true);
            $this->lastres = $this->handleResponseErrors($result_decoded);
            return $this->lastres;
        }
        else{
            if ($this->batchmode && $this->autopaging){
                if ($this->autopaging_result) {
                    return $this->autopaging_result;
                }
                else{
                    return $this->lastres;
                }
            }
            else if ($this->batchmode){
                return $this->lastres;
            }
            else{
                return null;
            }
        }
    }

    public function __call($fullmethod, $params){
        //echo "\nEntering: ".$fullmethod.' '.json_encode($params);

        list($class, $method) = explode("_", $fullmethod);
        $id = $this->id++;

        //default filter array empty
        if (!array_key_exists(0, $params)){
            $params[0] = array();
        }

        //default options array empty
        if (!array_key_exists(1, $params)){
            $params[1] = array();
        }

        if ($this->autopaging && strtolower($method) == 'get'){
//            if ($this->getBatchmode()){
//                throw new \Exception('Autopaging not supported in batch-mode');
//            }
            if (!array_key_exists('paging', $params[1])){
                $params[1]['paging'] = array(
                    "firstresult" => 0,
                    "maxresults" => $this->autopaging_maxresults
                );
            }
        }

        $this->requests[$id] = array(
            'class' => $class,
            'method' => $method,
            'params' => $params,
            'id' => $id
        );
        if (!$this->getBatchmode() || count($this->requests) == 1){
            if ($this->autopaging && strtolower($method) == 'get'){

                if (!$this->autopaging_in_progress){
                    $this->autopaging_in_progress = true;
                    $this->autopaging_result = array(
                        array(
                            'id' => $id,
                            'autopaging_result' => true,
                            'autopagina_number_of_calls' => 1,
                            'result' => array(
                                'rows' => array(),
                                'count' => 0,
                                'start' => 0,
                                'limit' => 0,
                                'next_start' => 0,
                                'more_items_in_collection' => false
                            ),
                            'error' => null
                        )
                    );
                }

                $tempres = $this->run();
                $tempres = $tempres[0]['result'];
                $this->autopaging_result[0]['result']['rows'] = array_merge($this->autopaging_result[0]['result']['rows'], $tempres['rows']);
                $this->autopaging_result[0]['result']['count'] = $tempres['count'];
                $this->autopaging_result[0]['result']['start'] = 0;
                $this->autopaging_result[0]['result']['limit'] = $tempres['count'];
                $this->autopaging_result[0]['result']['next_start'] = null;
                $this->autopaging_result[0]['result']['more_items_in_collection'] = false;

                if ($tempres['more_items_in_collection']){
                    $params[1]['paging'] = array(
                        "firstresult" => $tempres['next_start'],
                        "maxresults" => $this->autopaging_maxresults
                    );
                    $this->autopaging_result[0]['autopagina_number_of_calls']++;
                    return $this->__call($fullmethod, $params);
                }
                else{
                    $this->autopaging_in_progress = false;
                    return $this->autopaging_result;
                }
            }
            else{
                if (!$this->getBatchmode()) {
                    return $this->run();
                }
            }
        }
    }

    private function send($post_string) {
        $url =  $this->url;
        $that = $this;
        $that->reponseHeaders = array();

        $options = array(
            CURLOPT_VERBOSE => false,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POSTFIELDS => $post_string,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$this->apitoken
            ),
            CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$headers, $that){
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $name = strtolower(trim($header[0]));
                if (!array_key_exists($name, $that->reponseHeaders))
                    $that->reponseHeaders[$name] = [trim($header[1])];
                else
                    $that->reponseHeaders[$name][] = trim($header[1]);

                return $len;
            }
        );
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $output = trim(curl_exec($ch));

        if ($output == ''){
            throw new \Exception('Got no response from API call: '.curl_error($ch));
        }

        $httpstatuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        switch($httpstatuscode){
            case 503:  //Service Unavailable: thrown by the throttling mechanism (either Nginx or Php)
                //Refire the request after Retry-After seconds.
                if (array_key_exists('retry-after', $this->reponseHeaders)) {
                    usleep($this->reponseHeaders['retry-after'][0]*1000000);
                    return $this->send($post_string);
                }
                else{
                    throw new \Exception('Received HTTP status code 503 without Retry-After header. Cannot automatically resend the request.');
                }

                break;
            case 429:{ //Too many requests: thrown by the API to inform you that your API Request Pack is depleted for this hour.
                throw new \Exception('Received HTTP status code: '.$httpstatuscode.'. Maximum number of request for this hour is reached. Please upgrade your API Request Packs.');
                break;
            }
            case 200:{ //OK
                return $output;
                break;
            }
            default:
                throw new \Exception('Received HTTP status code: '.$httpstatuscode);
                break;
        }
    }

}
?>