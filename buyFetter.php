<?php

$Slaves = new app(
    'Bearer .....',
    't.me/vyxelfan'
);

while (true) {
    $Slaves->getSlavesWithoutFetter(function ($slave_id) use ($Slaves) {
        sleep(mt_rand(1, 2));
        $Slaves->buyFetter($slave_id);
    });
}

class app {

    /**
     * Bearer токен
     *
     * @var [string]
     */
    private static $authorization;
    /**
     * Название работы
     *
     * @var [string]
     */
    private static $jobName;
    /**
     * Айди раба
     *
     * @var [integer]
     */
    private static $randomSlave;

    /**
     * Подключение скрипта
     *
     * @param string $authorization Bearer токен
     * @param string $jobName       Название работы
     */
    public function __construct(
        string $authorization,
        string $jobName = null
    ) {
        self::$authorization = $authorization;
        self::$jobName = ($jobName) ?: "Скрипт на пхп!";
    }

    /**
     * Покупка раба
     *
     * @param integer $user_id  Айди раба, если не указано, будет рандом
     * @return array            Выводит информацию о купленном рабе
     */
    public static function buySlave(int $user_id = 1): array {
        self::antiFlood();

        if ($user_id == 1) {
            self::$randomSlave = mt_rand(1, 646306305);
        } else self::$randomSlave = $user_id;
        self::outputString(['Покупка раба @id' . self::$randomSlave]);

        return self::sendRequest(
            'https://pixel.w84.vkforms.ru/HappySanta/slaves/1.0.0/buySlave',
            [
                'slave_id' => self::$randomSlave
            ]
        );
    }

    /**
     * Установка работы рабу
     *
     * @return array    Выводит информацию о рабе
     */
    public static function jobSlave(): array {
        self::outputString(['Выдача работы @id' . self::$randomSlave]);

        return self::sendRequest(
            'https://pixel.w84.vkforms.ru/HappySanta/slaves/1.0.0/jobSlave',
            [
                'slave_id' => self::$randomSlave,
                'name' => self::$jobName
            ]
        );
    }

    /**
     * Покупка оковы рабу
     *
     * @param integer $user_id  Айди раба, если не указано, будет рандом
     * @return array            Выводит информацию о рабе
     */
    public static function buyFetter(int $user_id = 1): array {
        if ($user_id !== 1)
            self::$randomSlave = $user_id;
        self::outputString(['Покупка оковы @id' . self::$randomSlave]);

        return self::sendRequest(
            'https://pixel.w84.vkforms.ru/HappySanta/slaves/1.0.0/buyFetter',
            [
                'slave_id' => self::$randomSlave
            ]
        );
    }

    /**
     * Проверка всех рабов на наличие надетых оков
     *
     * @param Closure $list
     * @return void
     */
    public static function getSlavesWithoutFetter(Closure $list): void {
        self::outputString(['Проверка рабов без оков']);

        $slaves = self::sendRequest('https://pixel.w84.vkforms.ru/HappySanta/slaves/1.0.0/start', [], false);

        foreach ($slaves['slaves'] as [
            'id' => $id,
            'fetter_price' => $fetter_price,
            'fetter_to' => $fetter_to
        ]) {
            if ($fetter_to == 0) {
                if ($fetter_price < 100) {
                    $list($id);
                } else {
                    // Если цена оков будет больше 100 гривен
                }
            }
        }

        self::outputString(['Все рабы в оковах!']);
    }

    /**
     * Логер
     *
     * @param array $messages   Сообщение
     * @return void
     */
    private static function outputString(array $messages = []): void {
        echo PHP_EOL . '[' . date('H:i:s, d.m.y') . ']' . implode("\n> ", $messages);
    }

    /**
     * Анти-флуд 
     *
     * @param integer $sleep    Задержка в секундах
     * @return void
     */
    private static function antiFlood(int $sleep = 1): void {
        sleep($sleep + mt_rand(0, 2));
    }

    /**
     * Отправка запроса
     *
     * @param string $url       Url куда слать запрос
     * @param array $data       Параметры, если не нужны, оставьте пустой массив
     * @param boolean $needPost Если нужен запрос без POST параметра
     * @return array            Выводит информацию собсна
     */
    private static function sendRequest(
        string $url,
        array $data = [],
        bool $needPost = true
    ): array {
        // self::antiFlood();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . self::$authorization
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($needPost) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        self::outputString(['Выполнено!']);
        $result = json_decode($result, 1);

        if (isset($result['error'])) {

            switch ($result['error']['message']) {
                case 'ErrLowMoney app_error':
                    self::outputString([
                        'Ошибка!',
                        'Сообщение ошибки: ' . $result['error']['message'],
                        'Код ошибки: ' . $result['error']['code'],
                        'Пропускаем раба, сликом дорогой :с'
                    ]);

                    return self::buySlave();
                    break;

                case 'ErrFloodFetter app_error':
                    $randomTime = mt_rand(300, 5400);
                    self::outputString([
                        'Ошибка!',
                        'Сообщение ошибки: ' . $result['error']['message'],
                        'Код ошибки: ' . $result['error']['code'],
                        'Приостановка скрипта на ' . ceil($randomTime / 60) . ' минут!'
                    ]);
                    self::antiFlood($randomTime);

                    self::outputString(['Повторный запрос']);
                    return self::sendRequest($url, $data);
                    break;

                default:
                    $randomTime = mt_rand(60, 120);
                    self::outputString([
                        'Ошибка!',
                        'Сообщение ошибки: ' . $result['error']['message'],
                        'Код ошибки: ' . $result['error']['code'],
                        'Приостановка скрипта на ' . $randomTime . ' секунд!',
                        'Неизвестная ошибка, отправьте текст в issue!'
                    ]);
                    self::antiFlood($randomTime);

                    self::outputString(['Повторный запрос']);
                    return self::sendRequest($url, $data);
                    break;
            }
        } else return $result;
    }
}
