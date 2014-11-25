LogosDB v 1.4.*
=======

LogosDB is a database micro-framework for creating simple DB interaction without the need for a full MVC structure.

The idea is that for small projects, creating APIs, or when you just don't want or need to implement a fully featured
MVC framework (Phalcon, Laravel, CodeIgniter, Zend), LogosDB has your back. LogosDB is a bare-bones Model Interaction
Framework for working with objects inside databases. Its pretty good on the performance too!

## Getting Started

### Requirements

```
Requires PHP 5.5+
Requires PDO
```

### Installation (via composer)

Installation via composer is simple. Just add the following to your composer.json file

```JSON
{
    "require": {
        "hellsan631/logosdb": "1.4.*"
    }
}
```

After updating composer, extend the class with a logos object

```php
class User extends Logos_MySQL_Object{
    public $username;
    public $email;
}
```

and make sure to include your autoloader

```php
include "./vendor/autoload.php";
```

For those who don't have composer, just download as a zip, and include the autoload file in
your php header

### Creating A Model

Create an object class for each table in your database you want to use this with.

```php
//example object
class User extends Logos_MySQL_Object{

    public $id; //already defined in the DatabaseObject class.

    public $username;
    public $email;

}
```

### Database Setup

We use the config class, which creates a simpleton, for inputting our DB connection data. Add this somewhere
in your header (or you can add this to autoload.php)

```php
//Database settings
Config::write('db.host', 'localhost');
Config::write('db.name', 'db_name');
Config::write('db.user', 'db_user');
Config::write('db.password', 'db_pass');
```

### Database Schema

MySQL
```
Each table in your database should have an ID field, which is a incremental primary
index. If you want a table to use a date, use the timestamp format, and include
date in the name of the field.
```

## Usage

For a quick list of classes and what they do, head on over to the database interface
https://github.com/hellsan631/LogosDB/blob/master/lib/LogosDB/db/db-interface.php

### Creating a new object in the database

An object can be either declared as a variable, or statically created
(which takes less memory and time)

```php
//Object as a variable
$user = new User(["username" => "testing", "email" => "email@email.com"]);
$user->createNew();

//Same thing, but done statically (returns the ID of the created object)
User::createSingle(["username" => "testing", "email" => "email@email.com"]);
```

### Creating Many objects to a database

You can create objects multiple ways.

```php
//Want to create 100 new identical objects?
User::createMultiple(["username" => "testing", "email" => "email@email.com"], 100);

//want each object to be different?
$users = [];
$count = 0;

while($count < 100){
    array_push($users, ["username" => "testing",
                        "email" => "email@email.com",
                        "other_var" => $count]);

    $count++;
}

User::createMultiple($users);
```

### Saving Changes to an object already in the database

```php
//Saving a single object
User::loadSingle(["id" => 10])->save(["email" => "newEmail@gmail.com"]);

//or

User::saveSingle(["email" => "newEmail@gmail.com"], ["id" => 10]);

//saving to multiple objects at the same time
User::saveMultiple(["email" => "newEmail@gmail.com"], ["username" => "testing"]);
```

### Expanded query syntax

Want to add a limit, orderBy, or groupBy to your query results?

```php
User::query('limit', 10);
User::query(['orderBy', 'limit'], ['id DESC', 10]);
User::query(['orderBy', 'limit'], ['id ASC, username DESC', 10]);
User::query(['orderBy' => 'id ASC', 'limit' => 10]);

//Add getList to the end of your query to get a list of that classes objects
User::query('limit', 100)->getList();

//how to use min/max for limit
//Send them in as array!
User::query('limit', [0, 10])->getList();
User::query('limit', ['min' => 0, 'max' => 10])->getList();

//Or if you want to use an array to add more,
User::query(['limit' => [0, 10], 'orderBy' => 'id ASC'])->getList();
User::query(['limit' => ['min' => 0, 'max' => 10], 'orderBy' => 'id ASC'])->getList();
```

Any added limit/orderby/groupby is only added to the next query executed.
If you run any two queries in a row , i.e.
```php
User::query('limit', 10);
User::loadMultiple($array1);

//limit 10 no longer applies here
User::CreateMultiple($array2);
```

You need to add the query to each call to the database if you want it to effect that call.

Also, any time you set a query, the previous query of that type is overwritten.

```php
User::query('limit', 10);
User::query('orderBy', 'id ASC');
User::query('orderBy', 'id DESC');

//would load 10 users in id DESC order
User::loadMultiple($array1);
```

### Automatically Converts JSON

You can use JSON or an object anywhere you want.

```php
$JSON_STRING = '{"username": "testing", "email": "testing@mail.com"}';

$user = new User($JSON_STRING);//It works!

//Use JSON anywhere!
User::newInstance(["id" => 10])->save($JSON_STRING);
User::createMultiple($JSON_STRING, 100);
```

### Adheres to Model

You don't have to worry about what your sending into an object, or if you've
created dynamically declared variables in that object

```php
$JSON_STRING = '{"username": "testing", "email": "testing@mail.com", "size": "small"}';

$user = new User($JSON_STRING);//It works!

var_dump($user);
//object(User)[98]
//    public 'username' => string 'testing' (length=7)
//    public 'email' => string 'testing@mail.com' (length=16)

$user->updateObject($JSON_STRING);

var_dump($user);
//object(User)[98]
//    public 'username' => string 'testing' (length=7)
//    public 'email' => string 'testing@mail.com' (length=16)
//    public 'size' => string 'small' (length=5)

//Now that the $user has a dynamic variable, lets try and save it.

$user->save();
//UPDATE user SET username = :username, email = :email WHERE id = :id
```

## Contributing

Please feel free to fork, push, pull and all that other good Git stuff!

# Thanks

(Expanding on this readme at a later date)

Visit my blog: 
http://mathew-kleppin.com/blog-page/
