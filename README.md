LogosDB v 1.2.1
=======

A Database Object Handler Class that makes DB interaction easy for a change.

## Getting Started

### Requirements

```
Requires PHP 5.5+
Requires PDO
```

### Creating A Model

Copy this folder into your project, and create an object class for each table
in your database you want to use this with.

```php

//right now there is only mysql database compatibility
include "db-handler-mysql.php";

//example object
class User extends DatabaseObject{

    public $id; //already defined in the DatabaseObject class.

    public $username;
    public $email;

}
```

### Controller Usage

```php
include "./_db/objects.php";

//Database settings
Config::write('db.host', 'localhost');
Config::write('db.name', 'db_name');
Config::write('db.user', 'db_user');
Config::write('db.password', 'db_pass');
```

### Database Schema

```
Each table in your database should have an ID field, which is a incremental primary
index. If you want a table to use a date, use the timestamp format, and include
date in the name of the field.
```

## Usage

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

### Saving Many objects to a database

You can save objects multiple ways.

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
User::newInstance(["id" => 10])->save(["email" => "newEmail@gmail.com"]);

//saving to multiple objects at the same time
User::query(['limit'], [100]);

User::saveMultiple(["email" => "newEmail@gmail.com"], ["username" => "testing"]);
```

### Expanded query syntax

Want to add a limit, orderBy, or groupBy to your query results?

```php
User::query('limit', 10)->getList();

User::query(['orderBy', 'limit'], ['id DESC', 10])->getList();

User::query(['orderBy', 'limit'], ['id ASC, username DESC', 10])->getList();

User::query(['orderBy' => 'id ASC', 'limit' => 10])->getList();

//how to use min/max for limit
//Send them in as array!
User::query('limit', [0, 10])->getList();
User::query('limit', ['min' => 0, 'max' => 10])->getList();
//Or if you want to use an array to add more,

User::query(['limit' => [0, 10]])->getList();
User::query(['limit' => ['min' => 0, 'max' => 10]])->getList();
```

Any added limit/orderby/groupby is only added to the next query executed.
If you run any two queries in a row , i.e.
```php
User::query('limit', 10);
User::loadMultiple($array1);

//limit no longer applies here
User::query('limit', 10);
User::CreateMultiple($array2);
```
You will need to add the query to each call to the database if you want it to effect that call.

Also, any time you set a query, the previous query of that type is overwritten.
```php
User::query('limit', 10);
User::query('orderBy', 'id ASC');
User::query('orderBy', 'id DESC');

//would load users in id DESC order
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
