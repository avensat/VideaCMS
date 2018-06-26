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
        $client = new Client();
        try {
            $response = $client->request('GET', $url, ['type' => 'text/json']);
            return json_decode($response->getBody()->getContents())->items[0]->statistics;
        } catch (GuzzleException $e) {
            return "Erreur Youtube";
        }
    }
}