<?php

/**
 * PHP 8.0.0
 * 
 * позволяет расширить класс через namespace. имя namespace = имя класса + APIExtClass
 * первый аргумент не статичной ф-ции будет $this
 * аргументы-ссылки передавать умеет только через apiExec() и apiExecStatic()
 * пример:
 * 
 *  namespace BaseClassAPIExtClass // namespace для поиска методов. может быть несколько с одинаковым именем, лучше вынести в отдельный файл
 *  {
 *  function _echo(object $obj): void // $obj = $this, просто PHP не позволит использовать это имя
 *  {
 *      echo('\BaseClassAPIExtClass\_echo()'.PHP_EOL); // выведем выполнение текущей ф-ции
 *      $obj->test(); // вызовем метод BaseClass::test() для проверки $this
 *  }
 * 
 *  function _echoStatic(): string // $this отсутствует т.к. статический вызов
 *  {
 *      // return у статических/не статических вызовов, и через apiExec()/apiExecStatic() работает как у обычных ф-ций
 *      return '\BaseClassAPIExtClass\_echoStatic()'.PHP_EOL;
 *  }
 *  }
 * 
 *  namespace // глобальный namespace
 *  {
 *  class BaseClass extends APIExtClass // наследие APIExtClass
 *  {
 *      public function test(): void // тестирование вызова из ф-ции в namespace BaseClassAPIExtClass
 *      {
 *          echo('BaseClass::test()'.PHP_EOL); // выведем выполнение текущего метода
 *      }
 *  }
 * 
 *  $q = new BaseClass(); // создание объекта BaseClass
 *  $q->_echo(); // вызов ф-ции \BaseClassAPIExtClass\_echo()
 *  echo(BaseClass::_echoStatic()); // статический вызов ф-ции \BaseClassAPIExtClass\_echoStatic()
 *  BaseClass::_qwerty();
 *  if (BaseClass::apiResultStatic() === BaseClass::apiNotExists) // проверяем нашлась ли ф-ция
 *      echo('APIExtClassRet::apiNotExists'.PHP_EOL); // не нашлась :(
 *  }
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