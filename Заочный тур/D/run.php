<?php

//echo '<pre>';
$filesystem = new Filesystem();
$filter     = new Filter($filesystem);

$filesystem->open();
$result = $filter->handle(); // Исключение не ловим потому что ... я так захотел
echo $result; // Чисто для удобства выводим данные в основной поток
$filesystem->save($result);
$filesystem->close();

/**
 * Class Filter
 */
class Filter
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Filter constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Основной обработчик
     *
     * @return string
     */
    public function handle()
    {
        // Получаем кол-во строк с товарами
        $productsCount = $this->filesystem->getRow();

        // Получаем параметры товаров. для удобства используется сущность ТОВАР
        $products = [];
        for ($i = 1; $i <= $productsCount; $i++)
            $products[] = $this->parseParamsRow($this->filesystem->getRow());

        // Получаем кол-во строк с запросами
        $queriesCount = $this->filesystem->getRow();

        // Считаем сколько товаров подходит конкретному запросу
        $queriesCounter = [];
        for ($i = 1; $i <= $queriesCount; $i++) {
            $query = $this->parseParamsRow($this->filesystem->getRow());
            $queriesCounter[$i] = 0;
            foreach ($products as $product) {
                if (count(array_intersect_assoc($query, $product)) === count($query)) $queriesCounter[$i]++;
            }
        }

        $result = implode(PHP_EOL, $queriesCounter);

        return $result;
    }

    /**
     * Разбиваем строку в массив
     *
     * @param $row
     * @return array
     */
    private function parseParamsRow($row)
    {
        $row = str_replace(' ', '&', $row);
        parse_str($row, $params);
        return $params;
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
    public function open() // TODO закрыть
    {
        $this->file = fopen($this->input, 'rb'); //TODO проверить открытие и наличие
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
        return trim(fgets($this->file));
    }

    /**
     * Сохранение результата в файл
     *
     * @param string $string
     * @return bool
     */
    public function save($string) {
        $status = (bool) file_put_contents($this->output, $string);
        return $status;
    }

    /**
     * Закрытие файла
     *
     * @return bool
     */
    public function close() // TODO закрыть
    {
        return fclose($this->file);
    }

}