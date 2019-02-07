# Usage
```php
$url = new Replaceable('https://mysite.{domain}.com');
```
From the above example, we can set the value for `{domain}` substring in several ways:
```php
$url->addLookup('domain', 'local');

// setting the property itself
$url['domain'] = 'local'

// dumping a dictionary
$url->setLookup([
    'domain' => 'local'
]);
```
Then cast the object to string to get the result
```php
echo (string)$url; // prints "https://mysite.local.com"
```

## Modify the token format
Pass a string as a second argument to the constructor to customize what your token would look like:
```php
$url = new Replaceable('https://mysite.$$domain.com', '$$++key++');
// ++key++ is the 🔑
```
In some cases you might just need to pass a callable:
```php
$url = new Replaceable('https://mysite.UwU domain UwU.com', function($key) {
    return 'UwU ' . $key . ' UwU';
});
```

> This library is brought to you by: ~~Overkill Solutions Gang and Wheel Reinvention Inc™~~