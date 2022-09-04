# APIExtClass
### API extended class for PHP 8.0.0+

Позволяет расширить класс через `namespace`<br>
Имя `namespace` = имя класса + APIExtClass<br>
Первый параметр не статичной функции будет `$this`<br>
Аргументы-ссылки передавать умеет только через `apiExec()` и `apiExecStatic()`

Советую открыть **`apiextclass.php`** и почитать описания `apiExec()`, `apiExecStatic()`, `apiResult()` и `apiResultStatic()`

**`baseclass.php`**:
```php
// подключение APIExtClass
require('apiextclass.php');

class BaseClass extends APIExtClass // наследие APIExtClass
{
    // метод для тестирование вызова из функции в namespace BaseClassAPIExtClass
    public function test(): void
    {
        // выведем выполнение метода
        echo('BaseClass::test()'.PHP_EOL);
    }
}
```
**`baseclassapi.php`**:
```php
// namespace для поиска методов. может быть несколько с одинаковым именем, что позволяет бесконечно расширять класс
namespace BaseClassAPIExtClass

function _echo(object $obj): void // $obj = $this, просто PHP не позволит использовать это имя
{
    // выведем выполнение функции
    echo('\BaseClassAPIExtClass\_echo()'.PHP_EOL);
    
    // вызовем метод BaseClass::test() для проверки $this
    $obj->test();
}

function _echoStatic(): string // $this отсутствует т.к. статический вызов
{
    // return у статических, не статических вызовов, и через apiExec(), apiExecStatic() работает как у обычных функций
    return '\BaseClassAPIExtClass\_echoStatic()'.PHP_EOL;
}
```
**`main.php`**:
```php
// подключение файлов с классом и "новыми" методами класса
require('baseclass.php');
require('baseclassapi.php');

// создание объекта BaseClass
$q = new BaseClass();

// вызов функции \BaseClassAPIExtClass\_echo()
$q->_echo();

// статический вызов функции \BaseClassAPIExtClass\_echoStatic()
echo(BaseClass::_echoStatic());

// вызов несуществующей функции
BaseClass::_qwerty();
if (BaseClass::apiResultStatic() === BaseClass::apiNotExists) // проверяем нашлась ли функция
    echo('APIExtClassRet::apiNotExists'.PHP_EOL); // не нашлась
```
