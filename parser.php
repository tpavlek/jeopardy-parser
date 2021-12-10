<?php
error_reporting( E_ALL & ~E_WARNING & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

require 'vendor/autoload.php';
require 'config/config.php';

$parser = null;
$game_id = "0";
if ($config['url'] != "") {
    if ($argv[1] !== null) {
		// take game_id from command line instead of config
		// http://www.j-archive.com/showgame.php?game_id=7093
		$game_id = $argv[1];
		$config['url'] = "http://www.j-archive.com/showgame.php?game_id=" . $game_id;
    } else {
		$game_id = explode("=", $config['url'])[1];
    }
    print $game_id . "\n";
    
    $parser = new \Depotwarehouse\Jeopardy\Parser\WebParser($config['url']);
} else {
    $parser = new \Depotwarehouse\Jeopardy\Parser\FileParser(file_get_contents($config['file']));
}

$game = $parser->parse();

if ($game !== null) {

    if (is_array($game)) {
        // As a hack for the web parser we return two game objects in an array representing each round

        file_put_contents('output/questions-rd1-'.$game_id.'.json', json_encode(mergePlayersWithGame($game[0]->toArray(), $config)));
        file_put_contents('output/questions-rd2-'.$game_id.'.json', json_encode(mergePlayersWithGame($game[1]->toArray(), $config)));

		// combined game data
		file_put_contents('output/'.$game_id.'.json', json_encode( [ mergeGame($game[0]->toArray()), mergeGame($game[1]->toArray()) ] ) );
    } else {
        file_put_contents('output/questions-rd1-'.$game_id.'.json', json_encode(mergePlayersWithGame($game->onlyRound(1)->toArray(), $config)));
        file_put_contents('output/questions-rd2-'.$game_id.'.json', json_encode(mergePlayersWithGame($game->onlyRound(2)->toArray(), $config)));
    }

}

function mergeGame(array $gameData)
{
	$game = [];

	foreach ($gameData as $key => $value) {
		$game[$key] = $value;
	}

	return $game;
}

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
