<?php
namespace App\OtherClasses;

use Symfony\Component\HttpClient\HttpClient;

class TrackHandler
{

    // https://symfony.com/doc/current/http_client.html#handling-exceptions

    public function getTrackInfoByID($trackID,$connectedUser)
    {
        $userAccessToken = $connectedUser->getAuthentification();

        $httpclient = HttpClient::create();
        $response = $httpclient->request('GET',"https://api.spotify.com/v1/tracks/$trackID?market=CA",
        [
            'headers' => [
                'Authorization' => 'Bearer '. $userAccessToken,
            ]
        ]);

        if($statusCode = $response->getStatusCode() != 200)
        {
            return $statusCode;
        }

        return $response->toArray();
    }

    public function getTrackPreviewByID($trackID,$connectedUser)
    {
        $trackInfo = $this->getTrackInfoByID($trackID,$connectedUser);

        return $trackInfo["preview_url"];
    }

    public function searchByKeyword($query,$connectedUser)
    {
        $userAccessToken = $connectedUser->getAuthentification();

        $httpclient = HttpClient::create();
        $response = $httpclient->request('GET',"https://api.spotify.com/v1/search?q=$query&type=track&include_external=audio&market=CA&limit=50",
        [
            'headers' => [
                'Authorization' => 'Bearer '. $userAccessToken,
            ]
        ]);

        if($statusCode = $response->getStatusCode() != 200)
        {
            return $statusCode;
        }

        return $response->toArray();
    }

    public function getSeveralTracksByIds($ids,$connectedUser)
    {
        $userAccessToken = $connectedUser->getAuthentification();

        $httpclient = HttpClient::create();
        $response = $httpclient->request('GET',"https://api.spotify.com/v1/tracks?ids=$ids&market=CA&limit=50",
        [
            'headers' => [
                'Authorization' => 'Bearer '. $userAccessToken,
            ]
        ]);

        if($statusCode = $response->getStatusCode() != 200)
        {
            return $statusCode;
        }

        return $response->toArray()["tracks"];   
    }
}