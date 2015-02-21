<?php

use Symfony\Component\DomCrawler\Crawler;

require_once 'vendor/autoload.php';
require_once 'config/config.php';

$client = new \Goutte\Client();

$crawler = $client->request('GET', $config['url']);

$rounds = $crawler->filter('table.round');

$categories = processRound($rounds->first());

unset($categories[5]);
$game = [];
$game['contestants'] = [ ];

foreach ($config['players'] as $player) {
    $game['contestants'][] = [
        'name' => $player,
        'score' => 0
    ];
}

$game['categories'] = $categories;

file_put_contents("questions.json", json_encode($game));


function processRound(\Symfony\Component\DomCrawler\Crawler $round)
{
    $categoryNames = $round->filter('td.category_name')->each(function (Crawler $element) {
        return $element->text();
    });

    $clues = $round->filter('td.clue')->each(function (Crawler $clueElement) {

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
            $value = cleanValue($clueElement->filter('td.clue_value_daily_double')->first()->text());
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

    return $categories;
}

function cleanValue($value)
{
    $value = str_replace("$", "", $value);
    $value = str_replace("DD:", "", $value);
    $value = trim($value);
    return (int)$value;
}
