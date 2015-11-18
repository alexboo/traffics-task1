<?php

/**
 * Created by PhpStorm.
 * User: alexboo
 * Date: 17.11.15
 * Time: 21:14
 */
class WordsCounter
{
    const WRITE_CYCLES = 100000000;

    const MAX_WORDS_IN_MEMORY = 100;
    const READ_BYTES_FROM_FILE = 10000;

    const READING_FILE = 'data/words.txt';
    const CACHING_FILE = 'data/cache.dat';
    const TEMP_FILE = 'data/temp.dat';

    const TEST_WORDS = 'Мама мыла раму, маме мыла мало.';

    const READ_FILE = 'r';
    const WRITE_TO_FILE = 'w+';

    public function __construct()
    {
        // Очищаем файл в котором хранятся результаты подсчета
        $this->close($this->open(self::CACHING_FILE, self::WRITE_TO_FILE));

        $this->usedMemory();
    }

    /**
     * Подсчет слов в файле
     * @throws Exception
     */
    public function countWords()
    {
        $this->usedMemory();
        if (($fp = $this->open(self::READING_FILE, self::READ_FILE))) {
            $content = '';
            while (!feof($fp)) {
                $content .= fread($fp, self::READ_BYTES_FROM_FILE);
                // Есть вероятность что слово будет считано целиком
                $lastSpacePosition = strripos($content, ' ');
                if ($lastSpacePosition !== false) {
                    $words = substr($content, 0, $lastSpacePosition);
                    $content = ltrim(substr($content, $lastSpacePosition, mb_strlen($content, 'UTF-8')), ' ');

                    $this->saveWords($words);
                }
            }

            $this->saveWords(rtrim($content));

            $this->dumpResult();
        }

        $this->usedMemory();
    }

    /**
     * Генерация тестового файла со словами
     * @throws Exception
     */
    public function generateWords()
    {
        $this->output("Generate test file");
        if (($fp = $this->open(self::READING_FILE, self::WRITE_TO_FILE))) {
            for ($i = 0; $i < self::WRITE_CYCLES; $i++) {
                fwrite($fp, self::TEST_WORDS . PHP_EOL);
            }
            $this->close($fp);
        }
        $this->usedMemory();
    }

    /**
     * Вывод итог подсчета
     * @throws Exception
     */
    public function printResult()
    {
        $this->output("Result");
        if (($fp = $this->open(self::CACHING_FILE, self::READ_FILE))) {
            while (!feof($fp)) {
                echo fread($fp, self::READ_BYTES_FROM_FILE);
            }
            $this->close($fp);
        }
    }

    /**
     * Подсчет слов и сохранение слов в кэше
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
     * Удаление всех не используемых в подсчете символов
     * @param $string
     * @return mixed
     */
    protected function prepareString($string)
    {
        return preg_replace("/[^[:alnum:][:space:]]/u", ' ', $string);;
    }

    /**
     * Открытие файла
     * @param $file - путь до файла
     * @param $mode - тип доступа
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
     * Запись в файл
     * @param $fp - ссылка на файл
     * @param $string - строка для записи
     */
    protected function write($fp, $string)
    {
        fwrite($fp, $string . PHP_EOL);
    }

    /**
     * Закрыть доступ к файлу
     * @param $fp
     */
    protected function close($fp)
    {
        fclose($fp);
    }

    /**
     * Сохранение подсчитаных слов в кэширующий файл
     * @throws Exception
     */
    protected function dumpResult()
    {
        $this->output("Dump words to file");

        $fp = $this->open(self::CACHING_FILE, self::READ_FILE);
        $fpTemp = $this->open(self::TEMP_FILE, self::WRITE_TO_FILE);

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

            $this->close($fp);
            $this->close($fpTemp);

            copy(self::TEMP_FILE, self::CACHING_FILE);
            unlink(self::TEMP_FILE);
        }
    }

    /**
     * Вывод строки в консоль
     * @param $string
     */
    protected function output($string)
    {
        echo date('Y-m-d H:i:s') . ' - ' . $string . PHP_EOL;
    }

    /**
     * Выводит сколько оперативной памяти использует скрипт
     */
    protected function usedMemory()
    {
        $this->output("Memory is usage: " . round(memory_get_usage() / 1024) . "kb");
    }

    private $words = [];
}