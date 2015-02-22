Depotwarehouse.net's Jeopardy Parser
=====================================

This php script parses [J! Archive](http://j-archive.com/) and converts the clues and answers into a JSON format for use with
the [Depotwarehouse.net Jeopardy Player](http://github.com/tpavlek/Jeopardy).

Installation
----------------

Simply clone the repository and run `composer install`. This will require having composer installed on your system.

Configuration and Usage
--------------------------

Edit the file `config/config.php` and include the URL of the j-archive game you wish to convert, as well as an array of 
player names who will be playing in your match.

Then just run
```
php parser.php
```

It will output a file called questions.json into the same folder as the script. This JSON file will contain all the question,
answers and values.

Notes
-------

### Unused Questions

If a question was not shown on a broadcast, it will appear in the json file as
```json
{
  "clue": null,
  "answer": null,
  "value": null
}
```

### Daily Doubles

J-Archive records the value of a daily double as the amount bet. This script will determine the value that the question
would appear as on the board if it was a regular question, and sets that as the value. It will also set a "daily_double" 
key to true.
