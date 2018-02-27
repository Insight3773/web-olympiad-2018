<?php

echo '<pre>';
$filesystem = new Filesystem();
$casino = new Casino($filesystem);

$filesystem->open();
$filesystem->clearOutput();
$casino->handle();
$filesystem->close();

class Casino
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
        $betsCount = $this->filesystem->getRow();

        $bets = [];
        for ($i = 1; $i <= $betsCount; $i++) {
            $bet = explode(' ', $this->filesystem->getRow());
            $bets[$bet[0]] = $bet;
        }

        $gamesCount = $this->filesystem->getRow();

        $balance = 0;
        for ($i = 1; $i <= $gamesCount; $i++) {
            $game = explode(' ', $this->filesystem->getRow());
            if (isset($bets[$game[0]])) $bet = $bets[$game[0]]; else continue;

            if ($bet[2] == $game[4]) {
                $balance += $bet[1] * $this->getCoefficient($game) - $bet[1];
            } else {
                $balance -= $bet[1];
            }
        }

        $this->filesystem->appendRow($balance);
    }

    /**
     * @param $game
     * @return mixed
     */
    private function getCoefficient($game)
    {
        switch ($game[4]) {
            case 'L': return $game[1];
            case 'R': return $game[2];
            case 'D': return $game[3];
            default: throw new LogicException('Неверные данные');
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