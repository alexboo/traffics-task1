<?php

/**
 * Created by PhpStorm.
 * User: alexboo
 * Date: 17.11.15
 * Time: 21:14
 */
class WordsCounter
{
    const WRITE_CYCLES = 100;

    const MAX_WORDS_IN_MEMORY = 100;
    const READ_BYTES_FROM_FILE = 100;

    const READ_FROM_FILE = 'r+';
    const WRITE_TO_FILE = 'w+';


    const READING_FILE = 'data/words.txt';
    const CACHING_FILE = 'data/cache.dat';
    const TEMP_FILE = 'data/temp';

    const TEST_WORDS = 'Мама мыла раму, маме мыла мало.';

    public function __construct()
    {
        $this->generateWords();
    }

    public function countWords()
    {

    }

    public function generateWords()
    {
        if (($fp = $this->openFile(self::READING_FILE, self::WRITE_TO_FILE))) {
            for ($i = 0; $i <= self::WRITE_CYCLES; $i++) {
                fwrite($fp, self::TEST_WORDS);
            }
            fclose($fp);
        }
    }

    protected function openFile($file, $mode)
    {
        try {
            return fopen($file, $mode);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function dumpResult()
    {
        $fp = $this->openFile(self::CACHING_FILE, self::READ_FROM_FILE);
        $fpTemp = $this->openFile(self::TEMP_FILE, self::WRITE_TO_FILE);

        while (($word = fgets($fp, self::READ_BYTES_FROM_FILE)) !== false) {
            $word = explode(" ", $word);
            if (!empty($this->words[$word[0]])) {
                fwrite($fpTemp, $word[0] . ' ' . $this->words[$word[0]] + (int) $word[1]);
                unset($this->words[$word[0]]);
            }
        }

        if (!empty($this->words)) {
            foreach ($this->words as $word => $count) {
                fwrite($fpTemp, $word . ' ' . $count);
            }
        }

        $this->words = [];

        fclose($fp);
        fclose($fpTemp);

        move_uploaded_file(self::TEMP_FILE, self::CACHING_FILE);
    }

    private $words = [];

}