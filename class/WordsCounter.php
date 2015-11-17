<?php

/**
 * Created by PhpStorm.
 * User: alexboo
 * Date: 17.11.15
 * Time: 21:14
 */
class WordsCounter
{
    const WRITE_CYCLES = 1;

    const MAX_WORDS_IN_MEMORY = 100;
    const READ_BYTES_FROM_FILE = 10000;

    const READING_FILE = 'data/words.txt';
    const CACHING_FILE = 'data/cache.dat';
    const TEMP_FILE = 'data/temp.dat';

    const TEST_WORDS = 'Мама мыла раму, маме мыла мало.';

    public function __construct()
    {
        fclose($this->open(self::CACHING_FILE, 'w+'));
        $this->generateWords();
    }

    /**
     * Count words in test file
     * @throws Exception
     */
    public function countWords()
    {
        if (($fp = $this->open(self::READING_FILE, 'r'))) {
            $content = '';
            while (!feof($fp)) {
                $content .= fread($fp, self::READ_BYTES_FROM_FILE);
                // If the last word is not completely, this part of the words for the next cycle
                $lastSpacePosition = strripos($content, ' ');
                $words = substr($content, 0, $lastSpacePosition);
                $content = substr($content, $lastSpacePosition, mb_strlen($content, 'UTF-8'));

                $this->saveWords($words);

                $this->output("Memory is usage: " . round(memory_get_usage() / 1024) . "kb");
            }

            var_dump($content);
            $this->saveWords($content);

            $this->dumpResult();
        }
    }

    /**
     * Generate test file with words
     * @throws Exception
     */
    public function generateWords()
    {
        $this->output("Generate test file");
        if (($fp = $this->open(self::READING_FILE, 'w+'))) {
            for ($i = 0; $i < self::WRITE_CYCLES; $i++) {
                fwrite($fp, self::TEST_WORDS);
            }
            fclose($fp);
        }
    }

    /**
     * Print total words
     * @throws Exception
     */
    public function printResult()
    {
        $this->output("Result");
        if (($fp = $this->open(self::CACHING_FILE, 'r'))) {
            while (!feof($fp)) {
                echo fread($fp, self::READ_BYTES_FROM_FILE);
            }
            fclose($fp);
        }
    }

    /**
     * Save words in cache
     * @param $string
     */
    protected function saveWords($string)
    {
        $words = $this->prepareString($string);
        $words = explode(' ', $words);

        foreach ($words as $word) {
            if (!empty($word)) {
                $word = trim($word);
                if (empty($this->words[$word])) {
                    $this->words[$word] = 0;
                }
                $this->words[$word]++;
            }
        }

        if (count($this->words) > self::MAX_WORDS_IN_MEMORY) {
            $this->dumpResult();
        }
    }

    /**
     * Remove all noy alphabetic symbols
     * @param $string
     * @return mixed
     */
    protected function prepareString($string)
    {
        return preg_replace("/[^[:alnum:][:space:]]/u", ' ', $string);;
    }

    /**
     * Open file
     * @param $file
     * @param $mode
     * @return resource
     * @throws Exception
     */
    protected function open($file, $mode)
    {
        if (($fp = fopen($file, $mode)) === false) {
            throw new Exception("Can't open file " . $file);
        }

        return $fp;

    }

    /**
     * Write to file
     * @param $fp
     * @param $string
     */
    protected function write($fp, $string)
    {
        fwrite($fp, $string . PHP_EOL);
    }

    /**
     * Dump collected words to file
     * @throws Exception
     */
    protected function dumpResult()
    {
        $this->output("Dump words to file");

        $fp = $this->open(self::CACHING_FILE, 'r');
        $fpTemp = $this->open(self::TEMP_FILE, 'w+');

        if ($fp && $fpTemp) {
            while (($word = fgets($fp, self::READ_BYTES_FROM_FILE)) !== false) {
                $word = explode(" ", $word);
                if (!empty($word[0]) && !empty($word[1])) {
                    if (!empty($this->words[$word[0]])) {
                        $this->write($fpTemp, $word[0] . ' ' . ($this->words[$word[0]] + (int)$word[1]));
                        unset($this->words[$word[0]]);
                    } else {
                        $this->write($fpTemp, $word[0] . ' ' . $word[1]);
                    }
                }
            }

            if (!empty($this->words)) {
                foreach ($this->words as $word => $count) {
                    $this->write($fpTemp, $word . ' ' . $count);
                }
            }

            $this->words = [];

            fclose($fp);
            fclose($fpTemp);

            copy(self::TEMP_FILE, self::CACHING_FILE);
            unlink(self::TEMP_FILE);
        }
    }

    /**
     * Echo string to console
     * @param $string
     */
    protected function output($string)
    {
        echo date('Y-m-d H:i:s') . ' - ' . $string . PHP_EOL;
    }

    private $words = [];
}