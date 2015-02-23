<?php

use Symfony\Component\DomCrawler\Crawler;

require_once 'vendor/autoload.php';
require_once 'config/config.php';

$client = new \Goutte\Client();

$crawler = $client->request('GET', $config['url']);

$roundNumber = 1;

$rounds = $crawler->filter('table.round')->each(function($round) use (&$roundNumber) {
    $categories = processRound($round);
    $roundNumber++;
    return $categories;
});

$roundNumber = 1;
foreach ($rounds as $round) {
    $game = [];
    $game['contestants'] = [ ];

    foreach ($config['players'] as $player) {
        $game['contestants'][] = [
            'name' => $player,
            'score' => 0
        ];
    }

    $game['categories'] = $round;

    file_put_contents("questions-rd{$roundNumber}.json", json_encode($game));
    $roundNumber++;
}




function processRound(\Symfony\Component\DomCrawler\Crawler $round, $roundNumber = 1)
{
    $categoryNames = $round->filter('td.category_name')->each(function (Crawler $element) {
        return $element->text();
    });

    $clueNumber = 0;

    $clues = $round->filter('td.clue')->each(function (Crawler $clueElement) use (&$clueNumber, $roundNumber) {
        $clueNumber++;

        $clue = null;
        $answer = null;
        $value = null;
        $daily_double = false;

        $text = trim($clueElement->text());
        if ($text == null || $text == "") {
            return ['clue' => $clue, 'answer' => $answer, 'value' => $value, 'daily_double' => $daily_double];
        }
        $clue = $clueElement->filter('td.clue_text')->first()->text();
        $answerMouseover = $clueElement->filter('div')->getNode(0)->attributes->getNamedItem('onmouseover')->nodeValue;
        $matches = [];
        preg_match('{<em class="correct_response">(.*)</em>}', $answerMouseover, $matches);

        $answer = $matches[1];
        try {
            $value = cleanValue($clueElement->filter('td.clue_value')->first()->text());
        } catch (InvalidArgumentException $exception) {
            // Should be thrown if we hit a daily double.

            // We need to determine the proper value for this clue, not what the wager was.
            $valueModifier = ceil($clueNumber / 6);
            $baseClueValue = $roundNumber * 200;
            $value = $valueModifier * $baseClueValue;
            $daily_double = true;
        }

        return ['clue' => $clue, 'answer' => $answer, 'value' => $value, 'daily_double' => $daily_double];
    });

    $categories = [];
    $catIndex = 0;

    foreach ($categoryNames as $categoryName) {
        $clueIndex = 0;
        $catQuestions = array_filter(
            $clues,
            function ($clue) use (&$catIndex, &$clueIndex) {
                if ($clueIndex % 6 == $catIndex) {
                    $clueIndex++;
                    return true;
                }
                $clueIndex++;
                return false;
            }
        );

        $categories[] = [
            "name" => $categoryName,
            "questions" => array_values($catQuestions)
        ];
        $catIndex++;
    }

    // We only want the first five categories, our Jeopardy tool does not support six. Drop the last one.
    unset($categories[5]);

    return $categories;
}

/**
 * Clean the clue values from the DOM.
 *
 * They all contain dollar signs, which we don't want, and if the clue was a daily double, the value is preceeded by "DD: "
 *
 * @param $value
 * @return int
 */
function cleanValue($value)
{
    $value = str_replace("$", "", $value);
    $value = str_replace("DD:", "", $value);
    $value = trim($value);
    return (int)$value;
}
