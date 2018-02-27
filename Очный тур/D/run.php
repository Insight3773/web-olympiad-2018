<?php

echo '<pre>';
$filesystem = new Filesystem();
$router = new Router($filesystem);

$filesystem->open();
$filesystem->clearOutput();
$router->handle();
$filesystem->close();

class Router
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
        $row = $this->filesystem->getRow();
        $connection = explode(' ', $row);
        $nodesCount = $connection[0];
        $connectionsCount = $connection[1];

        $connectionsData = [];
        for ($i = 1; $i <= $connectionsCount; $i++) {
            $connectionsData[] = explode(' ', $this->filesystem->getRow());
        }

        $requestsCount = $this->filesystem->getRow();

        for ($i = 1; $i <= $requestsCount; $i++) {
            $request = explode(' ', $this->filesystem->getRow());
            // TODO
        }

//        $this->filesystem->appendRow('' . PHP_EOL);
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