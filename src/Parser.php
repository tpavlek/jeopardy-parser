<?php

namespace Depotwarehouse\Jeopardy\Parser;

interface Parser
{

    public function parse();

    public function parseNormal();

    public function parseDouble();

    public function parseFinal();

}
