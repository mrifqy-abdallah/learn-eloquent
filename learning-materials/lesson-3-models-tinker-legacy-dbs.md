# Welcome to Lesson Three of Eloquent by Example!

Last time we learned how to create a standard Eloquent Model with the aim of using it in our ModelFactories and Seeders. We started to see some the differences between talking directly to our database with the DB facade and using the Eloquent ORM.

It's time now to manipulating our output data.

## Things we'll learn:
- Model attributes
- Tinker
- Data Transformers

Today our boss has given us a small assignment - he wants us to build a Laravel application that will talk to an existing table in a database, so he can do basic CRUD statements against it. We won't need to use migrations or seeders for this assignment, but we'll need to figure out a few adjustments to our model.

Here's his legacy table...so 1990!

```sql
CREATE TABLE `TblContacts`
(
`Contacts_ID` INT NOT NULL AUTO_INCREMENT ,
`Contacts_Name` VARCHAR(50) NOT NULL ,
`Contacts_Email` VARCHAR(50) NOT NULL ,
PRIMARY KEY (`Contacts_ID`)
) ENGINE = InnoDB;

INSERT INTO `TblContacts`
(`Contacts_ID`, `Contacts_Name`, `Contacts_Email`)
VALUES ('1', 'Jeff', 'jeff@codebyjeff.com');
```

Not only would we really love to modernize this a bit, but there are a few things that prevent us from taking advantage of Laravel's reflection logic. However, for the time being this is what we are stuck with. Knowing that eventually we'd like to update it all, however, let's be clever in setting up our model to "future proof" it for that day.

Let's create a model to start working with. You still remember our artisan command?

```bash
php artisan make:model Contacts
```

Here's a new tool that is going to be invaluable to us: Tinker. Tinker is a REPL for php that comes included with Laravel. If you have ever played with the Ruby interactive tool then you know what Tinker is. Let's try it out quickly before using it with Laravel.

> NOTE: As of v5.4, you must install Tinker as a separate package. Fortunately, it is very simple to do:
> ```bash
> composer require laravel/tinker
> ```

Once the package has been installed, you should add Laravel\Tinker\TinkerServiceProvider::class to the providers array in your config/app.php configuration file. That's it!

```bash
php artisan tinker

$i = 1;
$f = 3;
$i + $f;    # Output: 4
```

Notice that Tinker "remembers" variables during your session, so you can test more complex code from the commandline. More importantly, it has hooks into Laravel so we can test code as if we were inside the application itself. No more littering our codebase with dd() calls!

Let's check if our new Contacts model is working with Tinker:

```bash
php artisan tinker

App\Contacts::all();
```

Hmmm....It says our base table, "contacts", is not found. Of course it's not - our table is called "TblContacts". One of the very nice things about working with Laravel is that it will assume certain naming conventions, and if you use them, there is no need to set up any annotation of configuration.

For example, every model has a `protected $table` variable which can be set manually, but if not, it assumes a snake_cased version of the model itself. Since we called this model `Contacts`, it builds the underlying queries for a table called `contacts` - which doesn't exist in our legacy database because we have `TblContacts`. Not a problem - we'll just set that at the top of the model:

```php
class Contacts extends Model
{
    protected $table = 'TblContacts';
}
```

Try your Tinker command again (you will have to exit Tinker and open it again, unfortunately. Tinker sessions will not detect code changes), and you will see that it returns a Collection (the standard return object from Eloquent) with a single model instance of the record we inserted for "Jeff".

Let's try one more think in Tinker - let's get contact number 1. What do you think will happen?

```bash
php artisan tinker

App\Contacts::find(1);

# Output:
# "Illuminate\Database\QueryException with message 'SQLSTATE[42S22]: Column not found: 1054 Unknown column 'TblContacts.id' in 'where clause' (SQL: select * from TblContacts where TblContacts.id = 1 limit 1)'"
```

As expected, it is searching for a primary key called id and can't find that, either. Another easy fix:

```php
class Contacts extends Model
{
    protected $table = 'TblContacts';

    protected $primaryKey = 'Contacts_ID';
}
```

and things work fine again.

If you read through the docs and source code you'll find that pretty much anything you might need to configure on a database table can be done. We'll look at a few others later in this series, but for a moment I want to stop and think about output.

We already know that we'd like to someday get rid of that archaic naming scheme. The problem we face is this - if we start building our application on top of this existing model, then our code will be littered with `$contact->Contacts_Name` everywhere and we will have a major refactoring job ahead of us. We'd really like to put another layer between our model and the output we will eventually use in the views.

I'm going to show you the two most common ways of doing this, one a native Laravel functionality and the other a third-party library. In Laravel itself we can simply add a function that accesses the model output itself and allows you to modify the value before it goes on to the rest of the application. Here's an example:

```php
public function contactName(){
    return $this->Contacts_Name;
}
```

and you would access your value with `$contact->contactName()`. You see this style used when working with Doctrine or other datamappers, where a getter and setter is required. This works, and creates a nice wrapper around our fields so we can change the table fields names later without touching the rest of the application, but it can be rather tiresome to setup.

A second option is to bring in a completely separate object called a "transformer" that has the job of presenting your data. One of the most popular of these is "Fractals", found at the The Php League: http://fractal.thephpleague.com/. Let's pull this in and see how it's different.

```bash
composer require league/fractal
```

We're going to create a new directory in the "app\" folder called "Transformers" and a class, "ContactsTransformer".

```php
//ContactsTransformer.php

namespace App\Transformers;

use App\Contacts;
use League\Fractal;

class ContactsTransformer extends Fractal\TransformerAbstract
{
    public function transform(Contacts $contact)
    {
        return [
            'id'      => (int) $contact->Contacts_ID,
            'name'    => $contact->Contacts_Name,
            'email'   => $contact->Contacts_Email,
        ];
    }
}
```

This class will transform our data much like a presenter, giving us a cleaner output that is popular for use with apis but serves us very well for our purposes. To get our desired output, we use the following code (this is a Tinker version; we would modify it to use in our controller):

```php
$fractal = new League\Fractal\Manager();
$resource = new League\Fractal\Resource\Collection(App\Contacts::all(), new App\Transformers\ContactsTransformer);
$rootScope = $fractal->createData($resource);
$rootScope->toArray();
```

Two things you should notice if you run that code in Tinker; one, you now have nice output with your renamed fields. Later, you can change the table's fieldnames and modify them in one place, here in the ContactsTransformer, and be confident it will be picked up throughout your application.

The second thing is that this is an ugly pain to work with. Obviously in your actual code you can abstract some of this out, but an easier way to is to take advantage of one of the many great Laravel libraries built to bridge Laravel and Fractals. I'll list some at the end of this lesson.

So today we learned a little bit about how our Model works, and how we can control it to use legacy tables and naming schemas as well as a couple of techniques for aliasing our output. It's time now to start hitting that database!