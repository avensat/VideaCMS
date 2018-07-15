<?php

namespace App\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class YoutubeHelper
{
    private $apiKey;

    public function __construct($youtubeApiKey)
    {
        $this->apiKey = $youtubeApiKey;
    }

    public function getVideoInfo($id){

        $url = "https://www.googleapis.com/youtube/v3/videos?part=statistics&id=" . $id . "&key=" . $this->apiKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$url);
        $result=curl_exec($ch);
        curl_close($ch);

        $data = json_decode($result);
        if(isset($data->items)){
            return $data->items[0]->statistics;
        } else {
            return "Error";
        }
        

    }
}