<?php

echo '<pre>';
$filesystem = new Filesystem();
$ip = new IP($filesystem);

$filesystem->open();
$filesystem->clearOutput();
$ip->handle();
$filesystem->close();

class IP
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return void
     */
    public function handle()
    {
        while ($ip = $this->filesystem->getRow()) {
            $pieces = explode('::', $ip);
            $padPieces = [];

            foreach ($pieces as $key => $piece) {
                $blocks = explode(':', $piece);

                $padBlocks = [];
                foreach ($blocks as $block) {
                    $padBlocks[] = str_pad($block, 4, 0, STR_PAD_LEFT);
                }

                $padPieces[$key] = $padBlocks;
            }

            $secondBlockCount = isset($padPieces[1]) ? count($padPieces[1]) : 0;

            $padPieces[0] = array_pad($padPieces[0], 8 - $secondBlockCount, '0000');

            if (isset($padPieces[1])) {
                $result = array_merge($padPieces[0], $padPieces[1]);
            } else $result = $padPieces[0];

            $result = implode(':', $result);
            $this->filesystem->appendRow($result . PHP_EOL);
        }
    }


}

/**
 * Class Filesystem
 */
class Filesystem
{

    /**
     * @var string
     */
    private $input;

    /**
     * @var string
     */
    private $output;

    /**
     * Filesystem constructor.
     *
     * @param string $input
     * @param string $output
     */
    public function __construct($input = 'input.txt', $output = 'output.txt')
    {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * @var null|resource
     */
    private $file = null;

    /**
     * Получение входных данных из файла
     *
     * @return string
     */
    public function open()
    {
        $this->file = fopen($this->input, 'rb');
        return $this->file;
    }

    /**
     * Считывает строку из открытого файла
     *
     * @return string
     * @throws ErrorException
     */
    public function getRow()
    {
        if (!$this->file) throw new ErrorException('Не открыт файл');
        return trim(fgets($this->file, 4096));
    }

    /**
     * Сохранение результата в файл
     *
     * @param string $string
     * @return bool
     */
    public function save($string)
    {
        return (bool) file_put_contents($this->output, $string);
    }

    /**
     * Очищает файл
     */
    public function clearOutput()
    {
        return (bool) file_put_contents($this->output, "");
    }

    /**
     * Добавление строки в файл
     *
     * @param string $string
     * @return bool
     */
    public function appendRow($string)
    {
        return (bool) file_put_contents($this->output, $string, FILE_APPEND);
    }

    /**
     * Закрытие файла
     *
     * @return bool
     */
    public function close()
    {
        return fclose($this->file);
    }

}