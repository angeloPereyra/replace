[![Build Status](https://travis-ci.com/angeloPereyra/replace.svg?branch=master)](https://travis-ci.com/angeloPereyra/replace)

# Installation
```shell
> composer require angelopereyra/replace
```
# Usage
```php
use Replace\Replaceable;

...

$url = new Replaceable('https://mysite.{domain}.com');
```
From the above example, we can set the value for `{domain}` substring in two ways:
```php
$url->addLookup('domain', 'local');

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
A static helper is also exposed for convenience
```php
$lookup = [
    'type'   => 'Bearer',
    'token'  => 'xoxb-83029aurioDnd'
];

Replaceable::parse('Authorization: @type @token', $lookup, '@++key++')
// prints "Authorization: Bearer xoxb-83029aurioDnd"
```

## Identify tokens
Suppose you have the subject string below:
```text
Hello, {name}. You look {adjective} today. 
```
Replaceable can identify the tokens according to the given token format. In this case, we will be using the default format which is `{++key++}`.


We can identify the tokens using the following usage:
```php
$subject = file_get_contents('...');

$sentence = new Replaceable($subject);

$sentence->identifyTokens(false);
// returns ['name', 'adjective']
```
To identify tokens with respect to word boundary, set the 1st parameter to true:
```php
$sentence->identifyTokens(true);
// returns ['adjective']
```