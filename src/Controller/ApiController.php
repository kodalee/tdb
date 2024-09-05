<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class ApiController extends AbstractController
{
    #[Route('/api', name: 'api_index', methods: ['GET'])]
    // json pretty print
    public function index(): JsonResponse
    {
        $_routes = $this->container->get('router')->getRouteCollection()->all();

        $routes = [];
        foreach ($_routes as $route) {
            if (str_starts_with($route->getPath(), '/api')) {
                $routes[$route->getPath()] = [
                    'methods' => $route->getMethods(),
                    'defaults' => $route->getDefaults(),
                ];
            }
        }

        return $this->json($routes);
    }

    #[Route('/api/players/steamid:{steamid}', name: 'api_players', methods: ['GET'])]
    public function player(
        string $steamid
    ): JsonResponse {
        return $this->json($this->getPlayer($steamid));
    }

    #[Route('/api/players/steamid:{steamid}/{scope}', name: 'api_players_scope', methods: ['GET'])]
    public function playerScope(
        string $steamid,
        string $scope
    ): JsonResponse {
        $_scope = explode(",", $scope);
        return $this->json($this->getPlayer($steamid, $_scope));
    }

    #[Route('/api/players/search/{name_or_steamid}', name: 'api_players_search', methods: ['GET'])]
    public function search(
        string $name_or_steamid
    ): JsonResponse {
        return $this->json($this->searchPlayers($name_or_steamid));
    }

    public function searchPlayers(
        string $name_or_steamid,
        int $limit = 10
    ): array {
        if ($limit > 250) {
            $limit = 250;
        }
        $results = json_decode(file_get_contents(
            "https://titsrp.com/api/server/findPlayer",
            false,
            stream_context_create(["http" => [
                "method" => "POST",
                "content" => http_build_query([
                    "info" => $name_or_steamid,
                    "limit" => $limit
                ]),
                "header" => "Content-Type: application/x-www-form-urlencoded\r\n"
            ]])
        ), true);
        return $results;
    }

    public function getPlayer(
        string $steamid,
        array $scope = ['steam', 'server', 'reputation', 'bans', 'discord', 'gamble', 'weekactivity', 'ccs']
    ): array {
        $data = [];

        $steaminfo = json_decode(file_get_contents(sprintf("https://steamid.venner.io/raw.php?input=%s", $steamid)), true);

        if ($steaminfo["error"] ?? false) {
            return [
                "error" => "Invalid SteamID"
            ];
        }

        if (in_array('steam', $scope)) {
            $data['steam'] = $steaminfo;
        }

        if (in_array('server', $scope)) {
            $loadmenu = file_get_contents(sprintf("https://titsrp.com/loadmenu/index.php?steamid=%s", $steaminfo["steamid64"]));
            $lastlogin = explode("\"", explode("var lastlogin = \"", $loadmenu)[1])[0];
            $money = explode("\"", explode("var wallet = \"", $loadmenu)[1])[0];
            $rawmoney = intval($money);
            $data['server'] = json_decode(file_get_contents(sprintf("https://titsrp.com/api/server/getDetails/%s", $steaminfo["steamid"])), true);
            $data['server']['last_login'] = intval($lastlogin);
            $data['server']['money'] = $rawmoney;
        }

        if (in_array('reputation', $scope)) {
            $replist = json_decode(file_get_contents(sprintf("https://www.titsrp.com/rep/fetch.php?steamid=%s", $steaminfo["steamid"])), true);
            $tot = 0;
            $pos = [];
            $neg = [];
            $nan = [];
            for ($i = 0; $i < count($replist); $i++) {
                $tot = $tot + $replist[$i]["rating"];
                switch ($replist[$i]["rating"]) {
                    case -1:
                        array_push($neg, $replist[$i]);
                        break;
                    case 0:
                        array_push($nan, $replist[$i]);
                        break;
                    case 1:
                        array_push($pos, $replist[$i]);
                        break;
                    default:
                        break;
                }
            }

            $data['reputation'] = [
                "total" => $tot,
                "count" => count($replist),
                "list" => array_merge($pos, $nan, $neg)
            ];
        }

        if (in_array('bans', $scope)) {
            $data['bans'] = json_decode(file_get_contents(sprintf("https://www.titsrp.com/bans/fetchall.php?steamid=%s", $steaminfo["steamid64"])), true);
        }

        if (in_array('discord', $scope)) {
            $discordinfo = json_decode(file_get_contents(sprintf("https://titsrp.com/api/discord/isverified/%s", $steaminfo["steamid64"])), true);
            $data['discord'] = $discordinfo;
        }

        if (in_array('gamble', $scope)) {
            $gambleinfo = json_decode(file_get_contents(sprintf("https://titsrp.com/api/server/gamblingstats/%s", $steaminfo["steamid64"])), true);
            $data['gamble'] = $gambleinfo;
        }

        if (in_array('weekactivity', $scope)) {
            $weekactivityinfo = json_decode(file_get_contents(sprintf("https://titsrp.com/api/server/playertime/%s/1", $steaminfo["steamid64"])), true);
            $data['weekactivity'] = $weekactivityinfo;
        }

        if (in_array('ccs', $scope)) {
            $ccs = json_decode(file_get_contents(sprintf("https://titsrp.com/api/server/activeccs/%s", $steaminfo["steamid64"])), true);
            $data['ccs'] = $ccs;
        }

        // $discordinfo = json_decode(file_get_contents(sprintf("https://titsrp.com/api/discord/isverified/%s", $steaminfo["steamid64"])), true);
        // $gambleinfo = json_decode(file_get_contents(sprintf("https://titsrp.com/api/server/gamblingstats/%s", $steaminfo["steamid64"])), true);
        // $weekactivityinfo = json_decode(file_get_contents(sprintf("https://titsrp.com/api/server/playertime/%s/1", $steaminfo["steamid64"])), true);
        // $ccs = json_decode(file_get_contents(sprintf("https://titsrp.com/api/server/activeccs/%s", $steaminfo["steamid64"])), true);

        $data["_scope"] = $scope;

        return $data;
    }
}
