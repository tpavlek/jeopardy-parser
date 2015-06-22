<?php

namespace Depotwarehouse\Jeopardy\Parser;

interface Parser
{

    const CLUE_TYPE_TEXT = "text";
    const CLUE_TYPE_IMAGE = "img";
    const DEFAULT_CLUE_TYPE = "text";


    public function parse();

    public function parseNormal();

    public function parseDouble();

    public function parseFinal();

}
