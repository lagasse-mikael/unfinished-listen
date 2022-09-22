<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request,Response};
use Symfony\Component\Routing\Annotation\Route;

use App\OtherClasses\{UserConnectionHandler,TrackHandler};
use App\Entity\{User,Post};

use Symfony\Contracts\HttpClient\HttpClientInterface;

// FAIRE UN AUTRE CONTROLLER POUR ROUTES AJAX

class MainController extends AbstractController
{
    private $userConnectionHandler;
    private $trackHandler;

    public function __construct(HttpClientInterface $_client)
    {
        $this->userConnectionHandler = new UserConnectionHandler($_client);
        $this->trackHandler = new TrackHandler($_client);
    }
    /**
     * @Route("/", name="main")
     */
    public function index(Request $request,HttpClientInterface $client,ManagerRegistry $doctrine): Response
    {
        // Trop de niaisage icitte , à changer.
        // redirect vers une autre route , index c'est la page de base et non l'atterisage.

        // On check si on a une erreur apres notre connexion : 
        if($request->get('error') != null)
        {
            // unset($this->currentUser);
        }
        else if($accessCode = $request->get('code'))
        {
            $response = $this->userConnectionHandler->generateAccessToken($accessCode);
            
            if($response['response_code'] != 200)
            {
                dd("ERROR : " . $response['response_code']);
            }
            else
            {
                $em = $doctrine->getManager();
                
                $connectedUser = new User($response['access_token']);
                if($possibleUser = $em->getRepository(User::class)->getUserByID($connectedUser->getUserID()))
                {
                    $possibleUser->setTokenInfos($response['access_token']);
                    $connectedUser = $possibleUser;
                } 
                else
                {
                    $em->persist($connectedUser);
                    $em->flush();
                }

                $request->getSession()->set('connectedUser',$connectedUser);

                
                return $this->redirectToRoute('feed');
            }
        }
        else        // Si tout ça est faux , on check si on a deja ete connecter : 
            {
            if($connectedUser = $request->getSession()->get('connectedUser'))
            {
                return $this->redirectToRoute('feed');
            }   
        }
        
        return $this->render('main/index.html.twig');
    }

    /**
     * @Route("/my_profile", name="myProfile")
     */
    public function profile(Request $request,HttpClientInterface $client): Response
    {
        $connectedUser = $request->getSession()->get('connectedUser');
        if($connectedUser)
        {
            return $this->render('profile/myprofile.html.twig',["userInfos" => $connectedUser->fetchSpotifyInfos(),'userSongs' => $connectedUser->getUserTopTracks()]);
        }
        else
        {
            return $this->redirectToRoute('loginPage');
        }
    }

    /**
     * @Route("/feed", name="feed")
     */
    public function feed(Request $request,HttpClientInterface $client,ManagerRegistry $doctrine): Response
    {
        // Utiliser orderBy à la place.
        // Plus en placeholder qu'autre chose 'array_reverse'

        $connectedUser = $request->getSession()->get('connectedUser');
        $posts = $doctrine->getManager()->getRepository(Post::class)->findAll();

        $posts = $this->loadTracksFromPost($posts,$connectedUser);
        
        // Le code d'erreur est encore dans la variable et non dans un parametre ; à changer
        if(gettype($posts) == 'array')
            $postsByDate = array_reverse($posts);
        else
            return $this->redirectToRoute('error_route');

        return $this->render('main/feed.html.twig',['posts' => $postsByDate]);
    }
    
    /**
     * @Route("/login", name="loginPage")
     */
    public function login(Request $request,HttpClientInterface $client): Response
    {
        $loginPromptURL = $this->userConnectionHandler->getLoginPrompt();

        return $this->redirect($loginPromptURL);
    }

    /**
     * @Route("/disconnect", name="disconnect")
     */
    public function disconnect(Request $request): Response
    {
        $request->getSession()->clear();

        return $this->redirectToRoute('main');
    }

    /**
     * @Route("/debug", name="debug_route")
     */
    public function debug(Request $request,HttpClientInterface $client)
    {
        $connectedUser = $request->getSession()->get('connectedUser');
        $trackInfos = $this->trackHandler->getTrackInfoByID("4RVtBlHFKj51Ipvpfv5ER4",$connectedUser);
        
        return $this->render("animations.html.twig",["trackInfos" => $trackInfos]);
    }

    /**
     * @Route("/postSong", name="debug_post", methods={"POST","GET"})
     */
    public function post(Request $request,HttpClientInterface $client,ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();

        $connectedUser = $request->getSession()->get('connectedUser');
        if(!$connectedUser) return new Response(498);

        $connectedUser = $em->getRepository(User::class)->getUserByID($connectedUser->getUserID());
        
        if($request->isMethod('POST'))
        {
            $post = new Post();

            $post->setCreator($connectedUser);
            $post->setTrackID($request->request->get('trackID'));

            $em->persist($post);
            $em->flush();

            return new Response(201);
        }
        
        return $this->redirectToRoute('error_route');
    }

    /**
     * @Route("/search", name="search_route")
     */
    public function search(Request $request,HttpClientInterface $client)
    {
        $query = $request->query->get('query');
        
        if(!$query)
        {
            return $this->render("main/searchResults.html.twig",["resultTracks" => array()]);
        }

        $connectedUser = $request->getSession()->get('connectedUser');

        if(!$connectedUser)
            return $this->redirectToRoute('error_route');

        $searchResults = $this->trackHandler->searchByKeyword($query,$connectedUser);

        $obtainedTracks = $searchResults["tracks"]["items"];
        $request->getSession()->set('obtainedTracks',$obtainedTracks);

        // faire une function qui va s'occuper de ça

        $arrEmptyPreviewURLTracks = array();
        foreach ($obtainedTracks as $pos => $track) {
            if(!$track["preview_url"])
                $arrEmptyPreviewURLTracks[$pos] = $track;
        }

        if(count($arrEmptyPreviewURLTracks) > 0)
        {
            $missingPreviewURLs = $this->getMissingPreviewURLs($arrEmptyPreviewURLTracks,$connectedUser);

            $properListOfTracks = array();
            foreach ($obtainedTracks as $pos => $track) {
                if(!$track["preview_url"])
                    $track["preview_url"] = $missingPreviewURLs[$track["id"]];
                $properListOfTracks[] = $track;
            }

            $obtainedTracks = $properListOfTracks;
        }

        return $this->render("main/searchResults.html.twig",["resultTracks" => $obtainedTracks]);
    }

    /**
     * @Route("/error", name="error_route")
     */
    public function errorPage(): Response
    {
        return $this->render("error.html.twig");
    }

    // AJAX

    /**
     * @Route("/fetchAUT")
     */
    public function AUT_USER(Request $request): Response
    {
        // Verifier si elle est valide eventuellement.
        $connectedUser = $request->getSession()->get('connectedUser');
        if($connectedUser)
        {
            return new Response($connectedUser->getAuthentification());
        }
        else
        {
            return new Response(null);
        }
    }

    // AUTRES
    public function getMissingPreviewURLs($arrTracks,$connectedUser)
    {
        $arrIds = array_column($arrTracks,'id');
        $stringIds = implode(",",$arrIds);
    
        $searchResults = $this->trackHandler->getSeveralTracksByIds($stringIds,$connectedUser);
    
        foreach ($searchResults as $key => $value) {
            $searchResults[$value["id"]] = $value["preview_url"];
            unset($searchResults[$key]);
        }
    
        return $searchResults;
    }

    // Y donner un nom plus clair...
    public function loadTracksFromPost($arrPosts,$connectedUser)
    {
        $arrIds = array_column($arrPosts,'trackID');
        $stringIds = implode(",",$arrIds);

        // Mettre un parametre en reference avec un message d'erreur
        $tracks = $this->trackHandler->getSeveralTracksByIds($stringIds,$connectedUser);

        if(gettype($tracks) != 'array')
        {
            // Tracks contient le code d'erreur
            return $tracks;
        }

        foreach ($arrPosts as $key => $post) {
            foreach ($tracks as $key => $track) {
                if($post->getTrackID() == $track['id'])
                {
                    $post->setLoadedTrack($track);
                }
            }
        }

        return $arrPosts;
    }
}
