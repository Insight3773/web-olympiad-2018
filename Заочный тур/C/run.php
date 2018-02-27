<?php

echo '<pre>';
$filesystem = new Filesystem();
$logHandler = new LogHandler($filesystem);

$filesystem->open();
$filesystem->clearOutput();
$resultStatus = $logHandler->handle();
echo $resultStatus;
$filesystem->close();

class LogHandler
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * LogHandler constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Основной обработчик
     *
     * @return true
     */
    public function handle()
    {
        $dateFrom = $this->filesystem->getRow();
        $dateTo   = $this->filesystem->getRow();
        $ip       = $this->filesystem->getRow(); // ip or all

        $connections = [];

        if ($ip == 'all') {
            while ($connection = $this->filesystem->getRow()) {
                $data = $this->buildConnectionData($connection);

                if ($this->isDateInRange($data['date'], $dateFrom, $dateTo)) {
                    if (!isset($connections[$data['ip']][$data['url']])) $connections[$data['ip']][$data['url']] = ['url' => $data['url'], 'visits' => 0];
                    $connections[$data['ip']][$data['url']]['visits'] += 1;
                }
            }
        } else {
            while ($connection = $this->filesystem->getRow()) {
                $data = $this->buildConnectionData($connection);

                if ($data['ip'] == $ip && $this->isDateInRange($data['date'], $dateFrom, $dateTo)) {
                    if (!isset($connections[$data['ip']][$data['url']])) $connections[$data['ip']][$data['url']] = ['url' => $data['url'], 'visits' => 0];
                    $connections[$data['ip']][$data['url']]['visits'] += 1;
                }
            }
        }

        foreach ($connections as $key => &$connection) {
            usort($connection, function($a, $b) {
                return $a['visits'] < $b['visits'] && $a['url'] < $b['url'];
            });

            $this->filesystem->appendRow($key . PHP_EOL);

            foreach ($connection as $item) {
                $row = $item['visits'] . ' ' . $item['url'];
                $this->filesystem->appendRow($row . PHP_EOL);
            }
        }

        return true;
    }

    /**
     * Находится ли дата в указанном диапазоне
     *
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @return bool
     */
    private function isDateInRange($date, $dateFrom, $dateTo)
    {
        return strtotime($date) >= strtotime($dateFrom) && strtotime($date) <= strtotime($dateTo);
    }

    /**
     * Обрабатывает исходную строку и формирует массив данных подключения
     *
     * @param $connectionRow
     * @return array
     */
    private function buildConnectionData($connectionRow)
    {
        $rowParts = explode(' ', $connectionRow);
        $ip = $rowParts[0];

        $date = str_replace('[', '', $rowParts[3]);
        $date = $this->formatConnectionDate($date);

        $url = $rowParts[9];

        return [
            'ip'   => $ip,
            'date' => $date,
            'url'  => $url
        ];
    }

    /**
     * Форматирует исходную строку даты в заданный формат
     *
     * @param $dateString
     * @return string
     */
    private function formatConnectionDate($dateString)
    {
        // TODO добавлять чесовой пояс если не пройдет
        $formatFrom = 'd/M/Y:H:i:s';
        $formatTo   = 'd.m.Y H:i:s';
        $date = DateTime::createFromFormat($formatFrom, $dateString);
        return $date->format($formatTo);
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
        return trim(fgets($this->file));
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