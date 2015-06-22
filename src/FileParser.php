<?php

namespace Depotwarehouse\Jeopardy\Parser;

use Depotwarehouse\Jeopardy\Parser\Values\Category;
use Depotwarehouse\Jeopardy\Parser\Values\Clue;
use Depotwarehouse\Jeopardy\Parser\Values\FinalClue;
use Depotwarehouse\Jeopardy\Parser\Values\Game;
use Depotwarehouse\Toolbox\Strings;
use Illuminate\Support\Collection;

/**
 * Class FileParser
 *
 * Parses a jeopardy game from a given file. The file is expected to be in the form of:
 *
 * ```
 * Category
 *
 * Question
 * Answer
 *
 * Question
 * Answer
 * ```
 * @package Depotwarehouse\Jeopardy\Parser
 */
class FileParser implements Parser
{

    const CATEGORIES_PER_ROUND = 5;
    const CATEGORY_DELIMITER = "Category: ";
    const FINAL_CATEGORY_DELIMITER = "Final Category: ";

    const CLUE_TYPE_IMAGE_DELIMITER = "IMG: ";

    protected $file_contents;

    public function __construct($file_contents)
    {
        // In case we have windows line endings, replace to unix.
        $file_contents = str_replace("\r\n", "\n", $file_contents);
        $this->file_contents = $file_contents;
    }

    public function parse()
    {
        $lines = explode("\n", $this->file_contents);
        $categories = new Collection();

        $currentCategory = null;
        $currentClues = new Collection();
        $roundModifier = 1;
        $clueValues = [ 200, 400, 600, 800, 1000 ];
        $currentClueValue = 0;

        $currentClue = null;

        $finalCategory = "";
        $finalClue = "";
        $finalAnswer = "";

        foreach ($lines as $line) {
            if ($line == "") {
                continue;
            }

            // Determine if the line denotes a category (it will begin with self::CATEGORY_DELIMITER if it is.
            if (Strings\starts_with($line, self::CATEGORY_DELIMITER)) {

                if ($currentCategory == null) {
                    // If we aren't already in a category, simply set the category to what the line says it is.
                    $currentCategory = substr($line, strlen(self::CATEGORY_DELIMITER));
                } else {
                    // If we *are* in a category, we want to complete the object with all our current clues, and then start
                    // a new category and associated collection.
                    $category = new Category($currentCategory, $currentClues);
                    $categories[] = $category;
                    $currentClues = new Collection();
                    $currentClueValue = 0;

                    // If we have file a round's worth of categories, up the round modifier to increase clue values.
                    if (count($categories) == self::CATEGORIES_PER_ROUND) {
                        $roundModifier++;
                    }
                    $currentCategory = substr($line, strlen(self::CATEGORY_DELIMITER));
                }

                continue;
            }

            if (Strings\starts_with($line, self::FINAL_CATEGORY_DELIMITER)) {
                $category = new Category($currentCategory, $currentClues);
                $categories[] = $category;

                $finalCategory = substr($line, strlen(self::FINAL_CATEGORY_DELIMITER));
                continue;
            }

            if (Strings\starts_with($line, "Final Clue: ")) {
                $finalClue = substr($line, strlen("Final Clue: "));
                continue;
            }

            if (Strings\starts_with($line, "Final Answer: ")) {
                $finalAnswer = substr($line, strlen("Final Answer: "));
                continue;
            }

            // If we're here, we know we have a clue.
            if ($currentClue == null) {
                $currentClue = $line;
                continue;
            } else {
                $daily_double = false;
                $clue_type = Parser::DEFAULT_CLUE_TYPE;

                if (Strings\starts_with($currentClue, self::CLUE_TYPE_IMAGE_DELIMITER)) {
                    // If the current clue is actually an image clue, we'll set the type, and remove the pieces from the
                    // front.
                    $clue_type = Parser::CLUE_TYPE_IMAGE;
                    $currentClue = substr($currentClue, strlen(self::CLUE_TYPE_IMAGE_DELIMITER));
                }

                if (Strings\starts_with($line, "DD: ")) {
                    $line = substr($line, 4);
                    $daily_double = true;
                }
                $clue = new Clue($clueValues[$currentClueValue] * $roundModifier, $currentClue, $line, $daily_double, $clue_type);
                $currentClues->push($clue);
                $currentClueValue++;
                $currentClue = null;
            }
        }

        $game = new Game($categories, new FinalClue($finalCategory, $finalClue, $finalAnswer));
        return $game;
    }

    public function parseNormal()
    {
        // TODO: Implement parseNormal() method.
    }

    public function parseDouble()
    {
        // TODO: Implement parseDouble() method.
    }

    public function parseFinal()
    {
        // TODO: Implement parseFinal() method.
    }


}
