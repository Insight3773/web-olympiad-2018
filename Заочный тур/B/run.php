<?php

//echo '<pre>';
new Calendar();

class Calendar
{
    const INPUT_FILE = 'input.txt';
    const OUTPUT_FILE = 'output.txt';

    const BLANK_DAYS = '__'; // Символы для заполнения пустых ячеек
    const DAYS_IN_WEEK = 7; // Количество дней в неделе

    /**
     * @var array
     */
    private $calendar = [];

    /**
     * День недели от 1 до 7
     *
     * @var int
     */
    private $currentDayOfWeek = 1;

    /**
     * Неделя от 1 до 4
     *
     * @var int
     */
    private $currentWeek = 1;

    public function __construct()
    {
        $params = $this->open();
        $month = $params[0];
        $year = $params[1];

        $calendar = $this->handle($month, $year);
        $this->save($calendar);
        echo $calendar; // Выводим в основной поток просто для удобства
    }

    /**
     * Получение календаря
     *
     * @param string $month
     * @param string $year
     * @return string
     */
    private function handle($month, $year)
    {
        $monthStartsFrom = date('N', mktime(0, 0, 0, $month, 1, $year));
        $daysInMonth     = date('t', mktime(0, 0, 0, $month, 1, $year));

        $this->currentDayOfWeek = $monthStartsFrom;

        // Наполняем массив днями
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $day = str_pad($day, 2, '0', STR_PAD_LEFT);
            $this->calendar[$this->currentWeek][] = $day;
            if ($this->currentDayOfWeek % self::DAYS_IN_WEEK === 0) {
                $this->currentWeek++;
                $this->currentDayOfWeek = 0;
            }
            $this->currentDayOfWeek++;
        }

        // Заполняем пустые ячейки
        $this->calendar[1] = array_pad($this->calendar[1], -7, self::BLANK_DAYS);
        $this->calendar[count($this->calendar)] = array_pad($this->calendar[count($this->calendar)],7, self::BLANK_DAYS);

        // Обьединяем массив в строку
        $calendar = [];
        foreach ($this->calendar as $key => $columns) $calendar[$key] = implode(' ', $columns);
        $calendar = implode(PHP_EOL, $calendar);

        return $calendar;
    }

    /**
     * Получение входных данных из файла
     *
     * @return array
     */
    private function open()
    {
        $file = fopen(self::INPUT_FILE, 'rb');
        $inputRow = trim(fgets($file));
        return explode(' ', $inputRow);
    }

    /**
     * Сохранение результата в файл
     *
     * @param string $string
     */
    private function save($string) {
        file_put_contents(self::OUTPUT_FILE, $string);
    }
}