<?php

/**
 * Created by PhpStorm.
 * User: alexboo
 * Date: 17.11.15
 * Time: 21:14
 */
class WordsCounter
{
    const WRITE_CYCLES = 1000000;

    const MAX_WORDS_IN_MEMORY = 100;
    const READ_BYTES_FROM_FILE = 100;

    const READ_FROM_FILE = 'r';
    const WRITE_TO_FILE = 'w';

    const READING_FILE = 'data/words.txt';
    const CACHING_FILE = 'data/cache.dat';

    const TEST_WORDS = 'Мама мыла раму, маме мыла мало.';

    public function __construct()
    {
        $this->generateWords();
    }

    public function generateWords()
    {
        if (($fp = $this->openFile(self::WRITE_TO_FILE))) {
            for ($i = 0; $i <= self::WRITE_CYCLES; $i++) {
                fwrite($fp, self::TEST_WORDS);
            }
            fclose($fp);
        }
    }

    protected function openFile($mode)
    {
        try {
            return fopen(self::READING_FILE, $mode);
        } catch (Exception $e) {
            return false;
        }
    }

}