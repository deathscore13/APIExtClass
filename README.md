# ClassAPIExtension 1.0.0
### Расширение API классов для PHP 8.0.0+<br><br>

Позволяет добавить в класс объектные/статические методы и статические проперти<br><br>
Для доступа к **`private`** и **`protected`** методам/проперти используйте [Reflection API](https://www.php.net/manual/ru/book.reflection.php)<br><br>
Советую открыть **`ClassAPIExtension.php`** и почитать описания `__apiAutoload()`, `__apiAddMethod()`, `__apiMethodExists()`, `__apiCall()`, `__apiAutoloadStatic()`, `__apiAddMethodStatic()`, `__apiMethodExistsStatic()`, `__apiCallStatic()`, `__apiPropertyStatic()`, `__apiIssetStatic()` и `__apiUnsetStatic()`

<br><br>
### Ограничения PHP
1. Передача параметров по ссылке работают только через `__apiCall()` и `__apiCallStatic()`
2. Возврат значения по ссылке работает только через `__apiCall()` и `__apiCallStatic()`
3. Нельзя использовать имена `$this` и `self` вне класса, используйте `$obj` и `$self` или любые другие имена

<br><br>
## Подключение возможностей
`use ClassAPIExtensionObject;` - добавление объектных методов (доступ к `self` и `$this`)<br>
`use ClassAPIExtensionStatic;` - добавление статических методов (доступ к `self`)<br>
`use ClassAPIExtensionPropertyStatic;` - добавление статических проперти<br>
`use ClassAPIExtension;` - добавление всех возможностей

<br><br>
## Пример добавления методов
**`BaseClass.php`**:
```php
// подключение ClassAPIExtension
require('ClassAPIExtension.php');

class BaseClass
{
    // подключение возможностей вызова через объект класса и статический вызов
    use ClassAPIExtensionObject, ClassAPIExtensionStatic;
    
    // метод для тестирование вызова через $this из добавленной функции
    public function test(): void
    {
        // вывод выполнения метода
        echo('BaseClass::test()'.PHP_EOL);
    }
    
    // приватный метод для тестирования вызова через self из добавленной функции
    private static function test2(): void
    {
        // вывод выполнения статического метода
        echo('BaseClass::test2()'.PHP_EOL);
    }
}
```
**`BaseClassAPI/method.php`**:
```php
// реализация нового метода
return function (string $self, object $obj): void
{
    // вызов BaseClass::test()
    $obj->test();

    // вызов приватного статического метода (читайте документацию к Reflection API)
    $r = new ReflectionMethod($self, 'test2');
    $r->setAccessible(true);
    $r->invoke(null);
}
```
**`BaseClassAPI/static/methodStatic.php`**:
```php
// реализация нового статического метода
return function &(string $self, int &$value): void
{
    // получение и возврат значения по ссылке
    return $value;
}
```
**`main.php`**:
```php
// подключение класса
require('BaseClass.php');

// создание объекта BaseClass
$b = new BaseClass();

// регистрация функции для автозагрузки методов
$b->__apiAutoload(function (string $name): ?callable
{
    $name = 'BaseClassAPI/'.$name.'.php';
    if (is_file($name))
        return require($name);
    
    return null;
});

// регистрация функции для автозагрузки статических методов
BaseClass::__apiAutoloadStatic(function (string $name): ?callable
{
    $name = 'BaseClassAPI/static/'.$name.'.php';
    if (is_file($name))
        return require($name);
    
    return null;
});

// вызов метода method(). так как __apiAddMethod() не был выполнен, то будет совершена автозагрузка
$b->method();

// вызов статического метода methodStatic() с рабочими ссылками
$value = 123;
$value2 = &BaseClass::__apiCallStatic('methodStatic', $value);
$value2 = 321;
echo($value.PHP_EOL);
```
<br><br>
## Пример добавления проперти
**`BaseClass.php`**:
```php
// подключение ClassAPIExtension
require('ClassAPIExtension.php');

class BaseClass
{
    // подключение возможности добавления статических проперти
    use ClassAPIExtensionPropertyStatic;
}
```
**`main.php`**:
```php
// подключение класса
require('BaseClass.php');

// создание объекта BaseClass
$b = new BaseClass();

// добавление проперти
$b->property = 1;

// проверка наличия проперти
if (isset($b->property))
    echo('BaseClass::$property = '.$b->property.PHP_EOL); // вывод значения проперти

// удаление проперти
unset($b->property);

// добавление статического проперти
$ps = &BaseClass::__apiPropertyStatic('propertyStatic', 2);

// проверка наличия статического проперти
if (BaseClass::__apiIssetStatic('propertyStatic'))
    echo('BaseClass::$propertyStatic = '.$ps.PHP_EOL); // вывод значения статического проперти

// удаление статического проперти
BaseClass::__apiUnsetStatic('propertyStatic');
```
