<?php

/**
 * APIExtClass
 * 
 * API extended class for PHP 8.0.0+
 * https://github.com/deathscore13/apiextclass
 */

abstract class APIExtClass
{
    public const apiNone       = null;  // неизвестно
    public const apiSuccess    = 0;     // успех
    public const apiNotExists  = 1;     // ф-ция не обнаружена

    private ?int $apiResult = self::apiNone;
    private static ?int $apiResultStatic = self::apiNone;

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

    /**
     * Результат выполнения ф-ции
     * 
     * @return int              ИмяКласса::apiNone, ИмяКласса::apiSuccess или ИмяКласса::apiNotExists, 
     */
    public function apiResult(): int
    {
        return $this->apiResult;
    }

    /**
     * Результат статичного выполнения ф-ции
     * 
     * @return int              ИмяКласса::apiNone, ИмяКласса::apiSuccess или ИмяКласса::apiNotExists, 
     */
    public static function apiResultStatic(): int
    {
        return self::$apiResultStatic;
    }

    /**
     * Костыль для аргументов-ссылок
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
     * Статический костыль для аргументов-ссылок
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
}