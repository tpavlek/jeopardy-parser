<?php

namespace Depotwarehouse\Jeopardy\Parser\Values;

use Illuminate\Contracts\Support\Arrayable;

class FinalClue implements Arrayable
{

    protected $clue;
    protected $answer;
    protected $category;

    public function __construct($category, $clue, $answer)
    {
        $this->category = $category;
        $this->clue = $clue;
        $this->answer = $answer;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            "category" => $this->category,
            "clue" => $this->clue,
            "answer" => $this->answer
        ];
    }
}
