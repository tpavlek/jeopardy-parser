<?php

require 'vendor/autoload.php';
require 'config/config.php';

$parser = null;
if ($config['url'] != "") {
    $parser = new \Depotwarehouse\Jeopardy\Parser\WebParser($config['url']);
} else {
    $parser = new \Depotwarehouse\Jeopardy\Parser\FileParser(file_get_contents($config['file']));
}

$game = $parser->parse();



file_put_contents('output/questions-rd1.json', json_encode(mergePlayersWithGame($game->onlyRound(1)->toArray(), $config)));
file_put_contents('output/questions-rd2.json', json_encode(mergePlayersWithGame($game->onlyRound(2)->toArray(), $config)));


function mergePlayersWithGame(array $gameData, $config)
{
    $game = [];
    $game['contestants'] = [ ];

    foreach ($config['players'] as $player) {
        $game['contestants'][] = [
            'name' => ucfirst(strtolower($player)),
            'score' => 0
        ];
    }

    foreach ($gameData as $key => $value) {
        $game[$key] = $value;
    }

    return $game;
}
