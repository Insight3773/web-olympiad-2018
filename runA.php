<?php

new Ip();

Class Ip
{

    const INPUT_FILE = './input.txt';
    const OUTPUT_FILE = './output.txt';

    /**
     * Ip constructor.
     * @throws ErrorException
     */
    public function __construct() {
        $file = fopen(self::INPUT_FILE, 'r');
        if ($file) {
            $ip = trim(fgets($file));
            $city = $this->getCityByIp($file, $ip);
            $this->save($city);

            fclose($file);
        }

    }

    /**
     * Получение города по указанному ip
     *
     * @param resource $file
     * @param $ip
     * @return string
     * @throws ErrorException
     */
    private function getCityByIp($file, $ip) {
        while (($row = fgets($file, 4096)) !== false) {
            $params = explode(' ', $row);
            $minIp = trim(array_shift($params));
            $maxIp = trim(array_shift($params));
            $city  = trim(implode(' ', $params));

            $isInRange = $this->isIpInRange($ip, $minIp, $maxIp);
            if ($isInRange) return $city;
        }

        throw new ErrorException('Нарушено условие задачи');
    }

    /**
     * Сохранение результата в файл
     *
     * @param string $string
     */
    private function save($string) {
        file_put_contents(self::OUTPUT_FILE, $string);
    }

    /**
     * Проверяет наличие ip в диапазоне
     *
     * @param string $ip
     * @param string $min
     * @param string $max
     * @return boolean bool
     */
    private function isIpInRange($ip, $min, $max) {
        return ip2long($min) <= ip2long($ip) && ip2long($ip) <= ip2long($max);
    }
}