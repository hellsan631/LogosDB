LogosDB
=======

A Database Object Handler Class that makes DB interaction easy for a change.

## Getting Started

### Installation

```
Copy this folder into your project, and create an object class for each table
in your database you want to use this with.
```

### Database Schema

```
Each table in your database should have an ID field, which is a incremental primary
index. If you want a table to use a date, use the timestamp format, and include
date in the name of the field.
```

## Usage

### Creating a new object in the database

```php
//An object can be either declared as a variable, or statically created
//(which takes less memory and time)

//Object as a variable
$user = new User(["username" => "testing", "email" => "email@email.com"]);
$user->createNew();

//Same thing, but done statically (returns the ID of the created object)
User::createSingle(["username" => "testing", "email" => "email@email.com"]);
```

### Saving Many objects to a database

```php
//You can save objects multiple ways.

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

```



Expanding on this readme at a later date.

Visit my blog: 
http://mathew-kleppin.com/blog-page/
