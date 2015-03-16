<?php

namespace Depotwarehouse\Jeopardy\Parser\Values;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class Category implements Arrayable
{

    protected $name;
    protected $clues;

    public function __construct($name, Collection $clues)
    {
        $this->name = $name;
        $this->clues = $clues;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Collection
     */
    public function getClues()
    {
        return $this->clues;
    }




    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'questions' => $this->getClues()->toArray()
        ];
    }
}
