<?php

/**
 * APIExtClass
 * 
 * API extended class for PHP 8.0.0+
 * https://github.com/deathscore13/apiextclass
 */

abstract class APIExtClass
{
    public const apiNone       = null;  // Неизвестно
    public const apiSuccess    = 0;     // Успех
    public const apiNotExists  = 1;     // Функция не найдена

    private ?int $apiResult = self::apiNone;
    private static ?int $apiResultStatic = self::apiNone;

    /**
     * Результат выполнения функции
     * 
     * @return int              ИмяКласса::apiNone, ИмяКласса::apiSuccess или ИмяКласса::apiNotExists, 
     */
    public function apiResult(): int
    {
        return $this->apiResult;
    }

    /**
     * Результат статичного выполнения функции
     * 
     * @return int              ИмяКласса::apiNone, ИмяКласса::apiSuccess или ИмяКласса::apiNotExists, 
     */
    public static function apiResultStatic(): int
    {
        return self::$apiResultStatic;
    }

    /**
     * Костыль для параметров-ссылок
     * 
     * @param callable $name    Имя функции (первый параметр $this)
     * @param mixed &...$args   Входящие аргументы, в которых работают ссылки, в отличие от магического метода __call()
     * 
     * @return mixed            Возвращаемое значение функции
     */
    public function apiExec(callable $name, mixed &...$args): mixed
    {
        if (function_exists($name = '\\'.static::class.'APIExtClass\\'.$name))
        {
            $this->apiResult = self::apiSuccess;
            return $name($this, ...$args);
        }
        $this->apiResult = self::apiNotExists;
        return false;
    }

    /**
     * Статический костыль для параметров-ссылок
     * 
     * @param callable $name    Имя функции
     * @param mixed &...$args   Входящие аргументы, в которых работают ссылки, в отличие от магического метода __callStatic()
     * 
     * @return mixed            Возвращаемое значение функции
     */
    public static function apiExecStatic(callable $name, mixed &...$args): mixed
    {
        if (function_exists($name = '\\'.static::class.'APIExtClass\\'.$name))
        {
            $this->apiResult = self::apiSuccess;
            return $name(...$args);
        }
        $this->apiResult = self::apiNotExists;
        return false;
    }

    public function __call(string $name, array $args): mixed
    {
        if (function_exists($name = '\\'.static::class.'APIExtClass\\'.$name))
        {
            $this->apiResult = self::apiSuccess;
            return $name($this, ...$args);
        }
        $this->apiResult = self::apiNotExists;
        return false;
    }

    public static function __callStatic(string $name, array $args): mixed
    {
        if (function_exists($name = '\\'.static::class.'APIExtClass\\'.$name))
        {
            self::$apiResultStatic = self::apiSuccess;
            return $name(...$args);
        }
        self::$apiResultStatic = self::apiNotExists;
        return false;
    }
}