<?php

echo '<pre>';
$filesystem = new Filesystem();
$validator = new Validator($filesystem);

$filesystem->open();
$filesystem->clearOutput();
$validator->handle();
$filesystem->close();

class Validator
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
     * @throws ErrorException
     */
    public function handle()
    {
        while($row = $this->filesystem->getRow()) {
            $data = $this->buildData($row);
            switch ($data['type']) {
                case 'S':
                    $isValid = $this->validateString($data['value'], $data['n'], $data['m']); break;
                case 'N':
                    $isValid = $this->validateNumber($data['value'], $data['n'], $data['m']); break;
                case 'P':
                    $isValid = $this->validatePhone($data['value']); break;
                case 'D':
                    $isValid = $this->validateDate($data['value']); break;
                case 'E':
                    $isValid = $this->validateEmail($data['value']); break;
                default:
                    throw new LogicException('Неверный тип данных');
            }
            $result = $isValid ? 'OK' : 'FAIL';
            $this->filesystem->appendRow($result . PHP_EOL);
        }
    }

    /**
     * @param $value
     * @param $n
     * @param $m
     * @return bool
     */
    public function validateString($value, $n, $m)
    {
        return is_string($value) && strlen($value) >= $n && strlen($value) <= $m;
    }

    /**
     * @param $value
     * @param $n
     * @param $m
     * @return bool
     */
    public function validateNumber($value, $n, $m)
    {
        return is_integer($value) && $value >= $n && $value <= $m;
    }

    /**
     * @param $phone
     * @return bool
     */
    public function validatePhone($phone)
    {
        return preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $phone) ? true : false;
    }

    /**
     * @param $date
     * @return bool
     */
    public function validateDate($date)
    {
        $format = 'd.m.Y H:i';
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * @param $email
     * @return bool
     */
    public function validateEmail($email)
    {
        if (substr($email, 0, 1) == '_') return false;
        return preg_match('/^[A-Za-z0-9_]{4,30}@[A-Za-z]{2,30}\.[a-z]{2,10}$/', $email) ? true : false;
    }

    /**
     * @param $row
     * @return array
     */
    private function buildData($row)
    {
        $options = explode('>', $row);

        $value = str_replace('<', '', $options[0]);

        $params = explode(' ', trim($options[1]));
        $type = $params[0];

        $n = isset($params[1]) ? $params[1] : null;
        $m = isset($params[2]) ? $params[2] : null;

        return [
            'value' => $value,
            'type'  => $type,
            'n'     => $n,
            'm'     => $m,
        ];
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