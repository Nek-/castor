## Parallel execution

The `parallel` function provides a simple way to execute functions in parallel,
so you do not have to wait for a function to finish before starting another one:

```php
#[AsTask]
function foo(): void
{
    [$foo, bar] = parallel(
        function () {
            return exec('sleep 2 && echo foo', quiet: true);
        },
        function () {
            return exec('sleep 2 && echo bar', quiet: true);
        }
    );
    
    echo $foo->getOutput(); // will print foo
    echo $bar->getOutput(); // will print bar
}
```

The parallel function use the `\Fiber` class to execute the functions in
parallel. The code is not executed in parallel only functions using this concept
will be executed in parallel, which is the case for the `exec` and `watch`
function.

### Watching in parallel

You can also watch in parallel multiple directories:

```
#[AsTask]
function parallel_change()
{
    parallel(
        function () {
            watch('src/...', function ($file, $action) {
                // do something on src file change
            });
        },
        function () {
            watch('doc/...', function ($file, $action) {
                // do something on doc file change
            });
        },
    );
}