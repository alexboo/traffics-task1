<?php

require_once 'class/WordsCounter.php';

$counter = new WordsCounter();

$counter->countWords();

$counter->printResult();