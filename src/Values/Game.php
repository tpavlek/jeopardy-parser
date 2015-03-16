<?php

namespace Depotwarehouse\Jeopardy\Parser\Values;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;

class Game implements Arrayable, Jsonable
{

    protected $categories;
    protected $final;

    public function __construct(Collection $categories, FinalClue $final)
    {
        $this->categories = $categories;
        $this->final = $final;
    }

    /**
     * Returns a new Game instance containing only the categories from the given round.
     *
     * It does this by assuming a baseRoundValue of 3000 (the total value of all question in a category in that round)
     * and checking if the sum of the clues in that category is equal to the expected round value.
     *
     * @param $roundNumber
     * @return Game
     */
    public function onlyRound($roundNumber)
    {
        $baseRoundValue = 3000;
        $categories = $this->categories->filter(function (Category $category) use ($roundNumber, $baseRoundValue) {

            // Sum the clues in this category.
            $totalValue = $category->getClues()->sum(function (Clue $clue) {
                return $clue->getValue();
            });

            // If the total value of the category's clues equal the round number multiplied by the base round value, this is a good category.
            return $totalValue == ($roundNumber * $baseRoundValue);
        });

        return new Game($categories, $this->final);
    }

    public function getFinal()
    {
        return $this->final;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $categories = $this->categories->map(function (Category $category) {
            return $category->toArray();
        })->toArray();

        return [
            "categories" => $categories,
            "final" => $this->final->toArray()
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray());
    }
}
