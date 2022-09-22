<?php
namespace App\OtherClasses;

use Symfony\Component\HttpClient\HttpClient;

class SpotifyUser
{
    private $token_infos;
    
    public function __construct($_tokenInfos)
    {
        $this->token_infos = $_tokenInfos;
    }

    public function getAuthentification()
    {
        return $this->token_infos['access_token'];
    }

    public function fetchInfos()
    {
        $httpclient = HttpClient::create();
        $response = $httpclient->request('GET','https://api.spotify.com/v1/me',
        [
            'headers' => [
                'Authorization' => 'Bearer '. $this->token_infos['access_token'],
            ]
        ]);

        return $response->toArray();
    }

    public function getUserID()
    {
        return $this->fetchInfos()["id"];
    }

    public function getUserTopTracks()
    {
        $httpclient = HttpClient::create();
        $response = $httpclient->request('GET','https://api.spotify.com/v1/me/top/tracks',
        [
            'headers' => [
                'Authorization' => 'Bearer '. $this->token_infos['access_token'],
                'Content-Type' => 'application/json'
            ]
        ]);

        return $response->toArray();
    }

    public function getUserPlaylists()
    {
        $httpclient = HttpClient::create();
        $response = $httpclient->request('GET','https://api.spotify.com/v1/me/playlists?limit=50',
        [
            'headers' => [
                'Authorization' => 'Bearer '. $this->token_infos['access_token'],
                'Content-Type' => 'application/json'
            ]
        ]);

        return $response->toArray();
    }
}