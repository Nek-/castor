# Context

For every command that castor run, it uses a `Context` object. This object
contains the default values for the `run` or `watch` function (directory,
environment variables, pty, tty, etc...).

It also contains custom values that can be set by the user and reused in
commands.

The context is immutable, which means that every time you change a value, a new
context is created.

## Using the context

You can get the initial context thanks to the `context()` function:

```php
use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\run;

#[AsTask]
function foo(): void
{
    $context = context();

    echo $context->currentDirectory; // will print the directory of the castor.php file

    $context = $context->withPath('/tmp'); // will create a new context where the current directory is /tmp
    run('pwd', context: $context); // will print "/tmp"
}
```

There is a `variable()` function to get a value stored in the `Context`:

```php
use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\variable;

#[AsTask]
function foo(): void
{
    $foobar = variable('foobar', 'default value');

    // Same as:
    $context = context();
    try {
        $foobar = $context['foobar'];
    } catch (\OutOfBoundsException) {
        $foobar = 'default value;
    }
}
```

## Creating a new context

You can create a new context by declaring a function with
the `Castor\Attribute\AsContext` attribute:

```php
use Castor\Attribute\AsContext;
use Castor\Context;

use function Castor\run;

#[AsContext]
function my_context(): Context
{
    return new Context(environment: ['FOO' => 'BAR']);
}

#[AsTask]
function foo(): void
{
    run('echo $FOO');
}
```

By default the `foo` command will not print anything as the `FOO` environment
variable is not set. If you want to use your new context you can use
the `--context` option:

```bash
$ php castor.phar foo

$ php castor.phar foo --context=my-context
BAR
```

> **Note**
> You can override the context name by setting the `name` argument of the
> `AsContext` attribute.

## Setting a default context

You may want to set a default context for all your commands. You can do that by
setting the `default` argument to `true` in the `AsContext` attribute:

```php
use Castor\Attribute\AsContext;
use Castor\Context;

use function Castor\run;

#[AsContext(default: true, name: 'my_context')]
function create_default_context(): Context
{
    return new Context(['foo' => 'bar'], currentDirectory: '/tmp');
}

#[AsTask]
function foo(Context $context): void
{
    run(['echo', $context['foo']]); // will print bar even if you do not use the --context option
    run('pwd'); // will print /tmp
}
```

## Disabled tasks according to the context

You can disable a task according to the context by using the
`AsTask::enabled` argument:

```php
use Castor\Attribute\AsTask;

#[AsTask(description: 'Say hello, but only in production', enabled: "var('production') == true")]
function hello(): void
{
    echo "Hello world!\n";
}
```

The value can be one of:

* `true`: always enabled (default value)
* `false`: always disabled
* a string: it represents an expression that will be evaluated in the context of
  the task. The task will be enabled if the expression returns `true` and
  disabled otherwise. The expression can use the `var()` function to get the
  value of a variable. Internally, it use the
  [symfony/expression-language](https://symfony.com/doc/current/components/expression_language.html)
  component.

## Getting a specific context

You can get a specific context by its name using the `context()` function:

```php
use Castor\Attribute\AsContext;
use Castor\Context;

use function Castor\run;

#[AsContext(name: 'my_context')]
function create_my_context(): Context
{
    return new Context(['foo' => 'bar'], currentDirectory: '/tmp');
}

#[AsTask]
function foo(): void
{
    $context = context('my_context');

    run(['echo', $context['foo']]); // will print bar even if you do not use the --context option
    run('pwd', context: $context); // will print /tmp
}
```

## Running logic with a specific context or parameters

You may want to run a bunch commands inside a specific directory or with a specific context.
Instead of passing those parameters to each run you can use the `with()` function:

```php
use Castor\Attribute\AsContext;
use Castor\Context;

use function Castor\run;
use function Castor\with;

#[AsContext(name: 'my_context')]
function create_my_context(): Context
{
    return new Context(['foo' => 'bar'], currentDirectory: '/tmp');
}

#[AsTask]
function foo(): void
{
    with(function (Context $context) {
        run(['echo', $context['foo']]); // will print bar even if you do not use the --context option
        run('pwd'); // will print /tmp
    }, context: 'my_context');
}
```
