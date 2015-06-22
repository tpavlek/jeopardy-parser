<?php

namespace Depotwarehouse\Jeopardy\Parser\Values;

use Depotwarehouse\Jeopardy\Parser\Parser;
use Illuminate\Contracts\Support\Arrayable;

class Clue implements Arrayable
{

    protected $value;
    protected $clue;
    protected $answer;
    protected $is_daily_double;
    protected $type;

    public function __construct($value, $clue, $answer, $is_daily_double = false, $clue_type = Parser::DEFAULT_CLUE_TYPE)
    {
        $this->value = $value;
        $this->clue = $clue;
        $this->answer = $answer;
        $this->is_daily_double = $is_daily_double;
        $this->type = $clue_type;
    }

    public function getValue()
    {
        return $this->value;
    }


    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'clue' => $this->clue,
            'answer' => $this->answer,
            'value' => $this->value,
            'daily_double' => $this->is_daily_double,
            'type' => $this->type
        ];
    }


}
