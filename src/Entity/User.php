<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Symfony\Component\HttpClient\HttpClient;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $unique_name;

    // POSTS BY USER NE MARCHE PAS 

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="creator")
     */
    private $postsByUser;

    private $token_infos;

    public function __construct($_tokenInfos)
    {
        $this->token_infos = $_tokenInfos;

        $userInfos = $this->fetchSpotifyInfos();

        $this->setUniqueName($userInfos["id"]);
    }

    public function getAuthentification()
    {
        return $this->token_infos['access_token'];
    }

    // IL POURRAIT AVOIR CONFUSION ENTRE LES DEUX
    // FAIRE ATTENTION / A CHANGER ?

    // Dequoi je parlais là ?

    // Verifier si on aurait deja les infos dans un buffer a la place ?
    public function fetchSpotifyInfos()
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

    public function getUserID()
    {
        return $this->fetchSpotifyInfos()["id"];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUniqueName(): ?string
    {
        return $this->unique_name;
    }

    public function setUniqueName(string $unique_name): self
    {
        $this->unique_name = $unique_name;

        return $this;
    }

    public function setTokenInfos($_tokenInfos): self
    {
        $this->token_infos = $_tokenInfos;

        return $this;
    }
}
