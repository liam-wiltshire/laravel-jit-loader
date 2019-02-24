# liam-wiltshire/laravel-jit-loader

liam-wiltshire/laravel-jit-loader is an extension to the default Laravel Eloquent model to 'very lazy eager load' relationships.

# Very Lazy Eager Load?
In order to avoid [N+1 issues](https://secure.phabricator.com/book/phabcontrib/article/n_plus_one/), you'd normally load your required relationships while building your collection:

```php
$books = App\Book::with(['author', 'publisher'])->get();
```

Or otherwise after the fact, but before use:

```php
$books = App\Book::all();

if ($someCondition) {
    $books->load('author', 'publisher');
}
```

In some situations however, this may not be possible - perhaps front-end developers are able to make changes to templates without touching the code, or perhaps during development you know don't which relationships you'll need anyway.
This change will track if your models belong to a collection, and if they do and a relationship is called that hasn't already been loaded, the relationship will be loaded across the whole collection just in time for use.

# Does This Work?
Yes. At least, it does in our production Laravel app. It's also been tested against a (rather constructed) test, pulling out staff, companies and addresses - while this isn't a 'real life' representation, it should give an idea of what it can do:

```php
    public function handle()
    {
        //Count the number of queries
        $querycount = 0;
        DB::listen(function ($query) use (&$querycount) {
            $querycount++;
        });

        $startTime = microtime(true);


        $staff = Staff::where('name', 'LIKE', 'E%')->orWhere('name', 'LIKE', 'P%')->get();

        /**
         * @var Staff $st
         */
        foreach ($staff as $st) {
            /**
             * @var Company $company
             */
            $company = $st->company;
            echo "\n\nName: {$st->name}\n";
            echo "Company Name: {$company->name}\n";
            foreach ($company->address as $address) {
                echo "Addresses: {$address->address_1}, ";
            }
        }

        $endTime = microtime(true);

        echo "\n\n=========================\n\n";
        echo "Queries Run: {$querycount}\n";
        echo "Execution Time: " . ($endTime - $startTime) . "\n";
        echo "Memory:" . memory_get_peak_usage(true)/1024/1024 . "MiB";
        echo "\n\n";
    }
```

Running this locally against a database with 200 companies, 1157 addresses and 39685 staff:

## Without JIT loading:
Queries Run: 10739
Execution Time: 16.058979034424
Memory:68MiB


## With JIT loading:
Queries Run: 6
Execution Time: 1.6715261936188
Memory:26MiB

# Installation
liam-wiltshire/laravel-jit-loader is available as a composer package:
`composer require liam-wiltshire/laravel-jit-loader`

Once installed, have your models extend the `\LiamWiltshire\LaravelJitLoader\Model` class instead of the default eloquent model, and JIT loading will be automatically enabled.

# Limitations
This is an early release based on specific use cases. At the moment the autoloading will only be used when the relationship is loaded like a property e.g. `$user->company->name` instead of `$user->company()->first()->name`. I am working on supporting relations loaded in alternate ways, however there is more complexity in that so there isn't a fixed timescale as of yet!

With any eager loading, a sufficiently large collection can cause memory issues. The JIT model specifies a threshold for autoloading. This is set to 6000 by default, but can be changed by overriding the `$autoloadThreshold` property on a model-by-model basis.
