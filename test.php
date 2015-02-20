<?php

$value = "$800";
$value = str_replace("$", "", $value);
$int = (int)$value;
echo $int;
