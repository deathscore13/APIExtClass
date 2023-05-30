<?php

/**
 * ClassAPIExtension
 * 
 * Расширение API классов для PHP 8.0.0+
 * https://github.com/deathscore13/ClassAPIExtension
 */

trait ClassAPIExtensionObject
{
    private array $__apiQueue = [];             /**< Очередь для автозагрузки методов */
    private array $__apiMethods = [];           /**< Добавленные методы */
    private array $__apiReflectionData = [];    /**< Данные Reflection API для методов */

    /**
     * Добавление callback функции для автозагрузки методов
     * 
     * @param callable $callback    Имя функции или анонимная функция | callback(string $name): ?callable
     *                              Если callback функция вернёт не анонимную функцию, то будет выполнен function_exists
     *                              Верните null, если функция не была найдена
     * @param bool $prepend         Если true, то указанная функция будет помещена в начало очереди
     */
    public function __apiAutoload(callable $callback, bool $prepend = false): void
    {
        if (is_string($callback) && !function_exists($callback))
            throw new Exception('Argument #1 ($callback) must be a valid callback, function "'.$callback.
            '" not found or invalid function name');

        if (in_array($callback, $this->__apiQueue))
            return;
        
        if ($prepend)
            array_unshift($this->__apiQueue, $callback);
        else
            $this->__apiQueue[] = $callback;
    }

    /**
     * Добавление метода для вызова
     * 
     * @param string $name          Имя метода
     * @param callable $callback    Callback функция
     */
    public function __apiAddMethod(string $name, callable $callback): void
    {
        if (array_key_exists($name, $this->__apiMethods))
            throw new Exception('Cannot redeclare '.self::class.'::'.$name.'()');
        
        if (is_string($callback) && !function_exists($callback))
            throw new Exception('Argument #1 ($callback) must be a valid callback, function "'.$callback.
            '" not found or invalid function name');
        
        $this->__apiMethods[$name] = $callback;
    }

    /**
     * Проверка наличия добавленного метода
     * 
     * @param string $name          Имя метода
     * 
     * @return bool                 true если метод был добавлен, false если нет
     */
    public function __apiMethodExists(string $name): bool
    {
        return isset($this->__apiMethods[$name]);
    }

    /**
     * Вызов метода с рабочими ссылками
     * 
     * @param string $name          Имя функции | name(string $self, object $this, ...): mixed
     * @param mixed &...$args       Входящие аргументы, в которых работают ссылки, в отличие от магического метода __call()
     * 
     * @return mixed                Возвращаемое значение функции
     */
    public function &__apiCall(string $name, mixed &...$args): mixed
    {
        if (!isset($this->__apiMethods[$name]))
        {
            foreach ($this->__apiQueue as $callback)
            {
                $ret = $callback($name);

                if ($ret !== null && is_callable($ret))
                {
                    $this->__apiAddMethod($name, $ret);
                    break;
                }
            }

            if (!isset($this->__apiMethods[$name]))
                throw new Exception('Call to undefined method '.self::class.'::'.$name.'()');
        }

        if (!isset($this->__apiReflectionData[$name]))
            $this->__apiReflectionData[$name] = (new ReflectionFunction($this->__apiMethods[$name]))->returnsReference();
        
        if ($this->__apiReflectionData[$name])
            return $this->__apiMethods[$name](self::class, $this, ...$args);
        
        $buffer = $this->__apiMethods[$name](self::class, $this, ...$args);
        return $buffer;
    }

    public function __call(string $name, array $args): mixed
    {
        return $this->__apiCall($name, ...$args);
    }
}

trait ClassAPIExtensionStatic
{
    private static array $__apiQueueStatic = [];            /**< Очередь для автозагрузки статических методов */
    private static array $__apiMethodsStatic = [];          /**< Добавленные статические методы */
    private static array $__apiReflectionDataStatic = [];   /**< Данные Reflection API для статических методов */

    /**
     * Добавление callback функции для автозагрузки статических методов
     * 
     * @param callable $callback    Имя функции или анонимная функция | callback(string $name): ?callable
     *                              Если callback функция вернёт не анонимную функцию, то будет выполнен function_exists
     *                              Верните null, если функция не была найдена
     * @param bool $prepend         Если true, то указанная функция будет помещена в начало очереди
     */
    public static function __apiAutoloadStatic(callable $callback, bool $prepend = false): void
    {
        if (is_string($callback) && !function_exists($callback))
            throw new Exception('Argument #1 ($callback) must be a valid callback, function "'.$callback.
            '" not found or invalid function name');

        if (in_array($callback, self::$__apiQueueStatic))
            return;
        
        if ($prepend)
            array_unshift(self::$__apiQueueStatic, $callback);
        else
            self::$__apiQueueStatic[] = $callback;
    }

    /**
     * Добавление статического метода для вызова
     * 
     * @param string $name          Имя метода
     * @param callable $callback    Callback функция
     */
    public static function __apiAddMethodStatic(string $name, callable $callback): void
    {
        if (array_key_exists($name, self::$__apiMethodsStatic))
            throw new Exception('Cannot redeclare '.self::class.'::'.$name.'()');
        
        if (is_string($callback) && !function_exists($callback))
            throw new Exception('Argument #1 ($callback) must be a valid callback, function "'.$callback.
            '" not found or invalid function name');
        
        self::$__apiMethodsStatic[$name] = $callback;
    }

    /**
     * Проверка наличия добавленного статического метода
     * 
     * @param string $name          Имя метода
     * 
     * @return bool                 true если метод был добавлен, false если нет
     */
    public static function __apiMethodExistsStatic(string $name): bool
    {
        return isset(self::$__apiMethodsStatic[$name]);
    }

    /**
     * Вызов статического метода с рабочими ссылками
     * 
     * @param string $name          Имя функции | name(string $self, ...): mixed
     * @param mixed &...$args       Входящие аргументы, в которых работают ссылки, в отличие от магического метода __callStatic()
     * 
     * @return mixed                Возвращаемое значение функции
     */
    public static function &__apiCallStatic(string $name, mixed &...$args): mixed
    {
        if (!isset(self::$__apiMethodsStatic[$name]))
        {
            foreach (self::$__apiQueueStatic as $callback)
            {
                $ret = $callback($name);

                if ($ret !== null && is_callable($ret))
                {
                    self::__apiAddMethodStatic($name, $ret);
                    break;
                }
            }

            if (!isset(self::$__apiMethodsStatic[$name]))
                throw new Exception('Call to undefined method '.self::class.'::'.$name.'()');
        }

        if (!isset(self::$__apiReflectionDataStatic[$name]))
            self::$__apiReflectionDataStatic[$name] = (new ReflectionFunction(self::$__apiMethodsStatic[$name]))->returnsReference();
        
        if (self::$__apiReflectionDataStatic[$name])
            return self::$__apiMethodsStatic[$name](self::class, ...$args);
        
        $buffer = self::$__apiMethodsStatic[$name](self::class, ...$args);
        return $buffer;
    }

    public static function __callStatic(string $name, array $args): mixed
    {
        return self::__apiCallStatic($name, ...$args);
    }
}

trait ClassAPIExtensionPropertyStatic
{
    private static array $__apiPropertiesStatic = [];      /**< Добавленные статические проперти */
    
    /**
     * Добавление/получение статической проперти
     * 
     * @param string $name          Имя проперти
     * @param mixed $value          Новое значение проперти (если не указано, то не меняет значение)
     * 
     * @return mixed                Значение проперти
     */
    public static function &__apiPropertyStatic(string $name, mixed $value = 0): mixed
    {
        if (func_num_args() === 2)
            self::$__apiPropertiesStatic[$name] = $value;
        else if (!isset(self::$__apiPropertiesStatic[$name]))
            throw new Exception('Access to undeclared static property '.self::class.'::$'.$name);
        
        return self::$__apiPropertiesStatic[$name];
    }
    
    /**
     * Проверка наличия добавленной статической проперти
     * 
     * @param string $name          Имя проперти
     * 
     * @return bool                 true если проперти была добавлена, false если нет
     */
    public static function __apiIssetStatic(string $name): bool
    {
        return isset(self::$__apiPropertiesStatic[$name]);
    }
    
    /**
     * Удаление добавленной статической проперти
     * 
     * @param string $name          Имя проперти
     */
    public static function __apiUnsetStatic(string $name): void
    {
        unset(self::$__apiPropertiesStatic[$name]);
    }
}

trait ClassAPIExtension
{
    use ClassAPIExtensionObject, ClassAPIExtensionStatic, ClassAPIExtensionPropertyStatic;
}
