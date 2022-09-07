# ClassAPIExtension
### Расширение API классов для PHP 8.0.0+<br>

Позволяет расширить класс через `namespace`<br><br>
Имя `namespace` = имя класса + APIExtension<br><br>
Первый параметр не статичной функции будет `$this`<br><br>
Переменные можно проверять через `isset()` и удалять через `unset()`<br><br>
Советую открыть **`ClassAPIExtension.php`** и почитать описания `apiResult()`, `apiResultStatic()`, `apiExec()` и `apiExecStatic()`

<br><br>
### Ограничения PHP
1. Параметры-ссылки работают только через `apiExec()` и `apiExecStatic()`
2. Нельзя использовать имена `$this` и `self` вне класса, используйте `$obj` и `$self`, или любые другие имена
3. Невозможно добавить статические переменные

<br><br>
## Подключение возможностей
`use ClassAPIExtensionObject;` - добавление вызовов через объект класса (доступ к `$this`)<br>
`use ClassAPIExtensionStatic;` - добавление статических вызовов (доступ к `self`)<br>
`use ClassAPIExtensionVars;` - добавление переменных (не использует `ClassAPIExtensionResult`)<br>
`use ClassAPIExtension;` - добавление всех возможностей<br>
`implements ClassAPIExtensionResult` - чтобы вместо `ClassAPIExtensionResult::константа` писать `ИмяКласса::константа`

<br><br>
## Пример добавления методов
**`baseclass.php`**:
```php
// подключение ClassAPIExtension
require('ClassAPIExtension.php');

class BaseClass implements ClassAPIExtensionResult // наследование ClassAPIExtensionResult (можно обойтись и без этого)
{
    // подключение возможностей вызова через объект класса и статический вызов
    use ClassAPIExtensionObject, ClassAPIExtensionStatic;
    
    // метод для тестирование вызова через $this из функции в namespace BaseClassAPIExtension
    public function test(): void
    {
        // вывод выполнения метода
        echo('BaseClass::test()'.PHP_EOL);
    }
    
    // метод для тестирования вызова через self из функции в namespace BaseClassAPIExtension
    public static function test2(): void
    {
        echo('BaseClass::test2()'.PHP_EOL);
    }
}
```
**`baseclassapiextension.php`**:
```php
// namespace для поиска методов. может быть несколько с одинаковым именем, что позволяет бесконечно расширять класс
namespace BaseClassAPIExtension;

function _echo(object $obj): void // $obj = $this, просто PHP не позволит использовать это имя вне класса
{
    // вывод выполнения функции
    echo('\BaseClassAPIExtClass\_echo()'.PHP_EOL);
    
    // вызов метода BaseClass::test() для проверки $this
    $obj->test();
}

function _echoStatic($self): string // $self = self, просто PHP не позволит использовать это имя вне класса
{
    // вызов статического метода BaseClass::test2()
    $self::test2();
    
    // return у статических/не статических вызовов, и через apiExec()/apiExecStatic() работает как у обычных функций
    return '\BaseClassAPIExtension\_echoStatic()'.PHP_EOL;
}
```
**`main.php`**:
```php
// подключение файлов с классом и "новыми" методами класса
require('baseclass.php');
require('baseclassapiextension.php');

// создание объекта BaseClass
$q = new BaseClass();

// вызов функции \BaseClassAPIExtension\_echo()
$q->_echo();

// статический вызов функции \BaseClassAPIExtension\_echoStatic()
echo(BaseClass::_echoStatic());

// вызов несуществующей функции
BaseClass::_qwerty();

// проверка существования функции. если не наследован ClassAPIExtensionResult, то ClassAPIExtensionResult::apiNotExists
if (BaseClass::apiResultStatic() === BaseClass::apiNotExists)
    echo('BaseClass::apiNotExists'.PHP_EOL); // не нашлась
```
<br><br>
## Пример добавления переменных
**`baseclass.php`**:
```php
// подключение ClassAPIExtension
require('ClassAPIExtension.php');

class BaseClass // нет смысла в наследовании ClassAPIExtensionResult
{
    // подключение возможности добавления переменных
    use ClassAPIExtensionVars;
}
```
**`testclass.php`**:
```php
class TestClass // новый класс, который будет записан в переменную
{
    // метод для тестирования
    public function _test(): void
    {
        // вывод выполнения метода
        echo('TestClass::test()'.PHP_EOL);
    }
}
```
**`main.php`**:
```php
// подключение файлов с классом и "новым" классом для записи в переменную
require('baseclass.php');
require('testclass.php');

// создание объекта BaseClass
$q = new BaseClass();

// добавление объекта BaseClass в переменную test
$q->test = new BaseClass();

// добавление объекта TestClass в переменную test2 объекта в переменной test
$q->test->test2 = new TestClass();

// вызов метода из объекта TestClass
$q->test->test2->_test();


// создание обычной переменной
$q->var1 = 1;

// проверка наличия переменной
if (isset($q->var1))
    echo('BaseClass::$var1 = '.$q->var1.PHP_EOL); // вывод значения переменной

// удаление переменной
unset($q->var1);
```
