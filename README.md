Troy Pavlek's Jeopardy Parser
=====================================

This php script parses [J! Archive](http://j-archive.com/) and converts the clues and answers into a JSON format for use with
[Troy's Jeopardy Player](http://github.com/tpavlek/Jeopardy).

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

It will output two files: questions-rd1.json and questions-rd2.json into the output folder. These JSON files will contain all the questions,
answers and values.

### Parsing from Files

If you have written your own Jeopardy game in a Google Doc or text document, this software can parse it into JSON for you
automatically. Your file should be in the format
```
Category: Some category

Clue
Answer

Clue
Answer
...

Category: Other Category

Clue
Answer

...

Final Category: Final Jeopardy Category Content
Final Clue: Some Clue
Final Answer: Some Answer
```

The software will then parse this into a game object and automatically assign the proper scores.

You can make any question a Daily Double by prepending "DD: " to the answer text for example.

```
This is a game hosted by Alex Trebek
DD: Jeopardy
```

Set the filename in `config/config.php` then run `php new_parser.php`.

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
