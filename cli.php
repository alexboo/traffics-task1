<?php

require_once 'class/WordsCounter.php';

$counter = new WordsCounter();

$counter->generateWords();

$counter->countWords();

$counter->printResult();