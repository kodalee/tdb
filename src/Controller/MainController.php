<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig');
    }

    #[Route('/players', name: 'app_players', methods: ['GET'])]
    public function players(): Response
    {
        return $this->render('main/players.html.twig');
    }

    #[Route('/players', name: 'app_players_search', methods: ['POST'])]
    public function searchPlayers(): Response
    {
        $name = $_POST['search'] ?? '';


        $api = new ApiController();
        $players = $api->searchPlayers($name);

        return $this->render('main/players.html.twig', [
            'players' => $players,
            'search' => $name,
            'api' => $api,
        ]);
    }

    #[Route('/players/{steamid}', name: 'app_player', methods: ['GET'])]
    public function player(string $steamid): Response
    {
        $api = new ApiController();
        $player = $api->getPlayer($steamid);

        return $this->render('main/player.html.twig', [
            'player' => $player,
            'api' => $api,
        ]);
    }
}
