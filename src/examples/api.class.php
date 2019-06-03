<?php
class com_gripp_API{

    private $apitoken;
    private $url;
    private $id = 1;
    private $batchmode = false;
    private $requests = array();

    public function __construct($apitoken, $url){
        if (!$apitoken){
            //throw new Exception('Api token is required');
            throw new \Exception('Api token is required');
        }
        if (!$url){

            throw new \Exception('Url is required');
        }
        $this->apitoken = $apitoken;
        $this->url = $url;
    }

    public function setBatchmode($b){
        $this->batchmode = $b;
    }

    public function getBatchmode(){
        return $this->batchmode;
    }

    public function handleResponseErrors($responses){
        $messages = array();
        foreach($responses as $response){
            if (array_key_exists('error', $response) && !empty($response['error'])){
                $messages[] = $response['error'];
            }
        }

        if (count($messages) > 0){
            throw new \Exception(implode("\n", $messages));
        }
    }

    function getRawPost(){
        $post = array();

        foreach($this->requests as $r){
            $post[] = array(
                'apitoken' => $this->apitoken,
                'method' => $r['class'].'.'.$r['method'],
                'params' => $r['params'],
                'id' => $this->id++
            );
        }

        return $post;
    }

    function run(){
        //call
        $post = $this->getRawPost();
        $this->requests = array();
        if (count($post) > 0){
            $post_string = json_encode($post);
            $result =$this->send($post_string);
            $result_decoded = json_decode($result, true);
            $this->handleResponseErrors($result_decoded);
            return $result_decoded;
        }
        else{
            return null;
        }
    }

    public function __call($fullmethod, $params){
        list($class, $method) = explode("_", $fullmethod);

        if ($this->batchmode){
            $this->requests[] = array(
                'class' => $class,
                'method' => $method,
                'params' => $params
            );
        }
        else{
            $post_string = json_encode(
                array(
                    array(
                        'apitoken' => $this->apitoken,
                        'method' => $class.'.'.$method,
                        'params' => $params,
                        'id' => $this->id++
                    )
                )
            );
            $result = $this->send($post_string);
            $result_decoded = json_decode($result, true);
            $this->handleResponseErrors($result_decoded);
            return $result_decoded;

        }
    }

    public function send($post_string) {
        $url =  $this->url;
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
            CURLOPT_POSTFIELDS => $post_string
        );
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $output = trim(curl_exec($ch));

        if ($output == ''){
            throw new \Exception('Got no response from API call: '.curl_error($ch));
        }

        return $output;
    }

}
?>