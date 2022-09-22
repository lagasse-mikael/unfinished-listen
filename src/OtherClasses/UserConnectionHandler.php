<?php
namespace App\OtherClasses;

const CLIENT_ID = "8988518e505a441982057410603640ef";
const CLIENT_SECRET = "2ee3babeae694c7987026dd396a8414d";

use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserConnectionHandler
{
    private $client;

    public function __construct(HttpClientInterface $_client)
    {
        $this->client = $_client;
    }

    public function generateAccessToken($code)
    {
        $arrResponse = ['response_code' => '-1' , 'access_token' => null];

        $AUTHORIZATION = "Basic " . base64_encode(CLIENT_ID . ":" . CLIENT_SECRET);
        $response = $this->client->request('POST','https://accounts.spotify.com/api/token',
        [
            'headers' => [
                'Authorization' => $AUTHORIZATION,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => [
                "code" => $code,
                "grant_type" => "authorization_code",
                "redirect_uri" => "http://localhost:4200/spotify_web_api/public/index.php/"]
        ]);

        $statusCode = $response->getStatusCode();

        if($statusCode == 200)
        {
            $responseInfo = $response->toArray();
            $arrResponse['access_token'] = $responseInfo;
        }

        $arrResponse['response_code'] = $response->getStatusCode();

        return $arrResponse;
    }

    // Pas sur 
    public function getLoginPrompt()
    {
        $authURL = "https://accounts.spotify.com/authorize/?client_id=8988518e505a441982057410603640ef&client_secret=2ee3babeae694c7987026dd396a8414d&redirect_uri=http://localhost:4200/spotify_web_api/public/index.php/&scope=playlist-read-private playlist-modify-private user-top-read user-read-playback-position user-read-recently-played user-read-currently-playing user-read-playback-state user-modify-playback-state playlist-read-collaborative playlist-modify-public playlist-read-private playlist-modify-private app-remote-control streaming user-read-email user-library-modify user-library-read user-read-private&response_type=code&show_dialog=true";
        
        return $authURL;
    }
}