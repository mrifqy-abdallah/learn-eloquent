# Welcome to Lesson Four of Eloquent by Example!

Yesterday we started learning the basics of Models and a new tool called Tinker. You may have noticed that, despite this being a Laravel course, we still haven't built any routes or web pages!

Laravel is a very big ecosystem, covering far more than just standard web pages. I think that learning to look at an isolated section of it, like we are doing with Eloquent, will be very helpful toward developing not only our speed and workflow but also our understanding of each of them. Therefore, we'll be sticking with the command line and Tinker for a little while longer. It may feel a little awkward if you aren't used to working this way, but it will have big payoffs in the end.

This lesson we are going to start doing some database querying!

> I create Cat instead of Dog model instance in this part

## Things we'll learn:
- Model scopes

One of my biggest aims of this course is to help people become proficient, not just knowledgeable, about using the artisan tools. So, we are going to rebuild our tables and data. Don't worry - we're getting quick at this now!

1. Here is a command we haven't used yet with migrate - "reset". This will rollback all migrations so we can start over.
    ```bash
    php artisan migrate:reset
    ```

2. Delete files: _create_dogs_table, /databases/seeders/DogsTableSeeder.php and /app/Dogs.php

You should probably run `composer dump-autoload` after doing this, as files sometimes get registered in the autoloader and cause problems later when they are "missing".

3. Do you remember how to make the migration and model at the same time?
    ```bash
    php artisan make:model Dogs -m
    ```

4. In our migration, we want Dogs to have a name and age field. Your up() function will look like this:
    ```bash
    public function up()
    {
        Schema::create('dogs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('age')->nullable();
            $table->timestamps();
        });
    }
    ```

5. Let's make a new seeder so we have a few rows:
    ```bash
    php artisan make:seeder DogsTableSeeder
    ```

6. For this exercise, we all want to have the same data so we can make some specific queries, so we'll use our old technique for creating seed data instead of using Faker:
    ```php
    public function run()
    {
        \App\Dogs::truncate();

        \App\Dogs::create(['name' => 'Joe', 'age' => 5 ]);
        \App\Dogs::create(['name' => 'Jock', 'age' => 7 ]);
        \App\Dogs::create(['name' => 'Jackie', 'age' => 2 ]);
        \App\Dogs::create(['name' => 'Jane', 'age' => 9 ]);
    }
    ```

7. Finally, let's run the migration AND seeder:
    ```bash
    php artisan migrate
    php artisan db:seed --class=DogsTableSeeder
    ```

## Scopes:
Ok, let's get back into our Tinker and start querying!

We have fours dogs, aged 2, 5, 7 and 9. We'd like to see all the dogs older than 6 years of age. The simple way to do this is to just put a where() clause on our Dogs model, like so:

```bash
php artisan tinker

App\Dogs::where('age', '>', 6)->get();
```

You should see the lovely Jock and Jane come back.

Something that confuses many people when they get started with Eloquent is that it is built on top of the QueryBuilder, but the results come back as a Collection object. That means you're going to see a lot of similarly named functions that seem to be different depending on whether you are building the query, or manipulating the returned records. In this case we are using where() to create a where clause in our query, not filtering the resultset. For this course we will be using the QueryBuilder functions to build up sql statements, which you can read up on further under that section in the docs.

The code we just ran works, but it has a few problems from a design and maintainability standpoint. With this code in our controller it's just like we are hard-coding sql statements all over our application. If we needed to change that age for some reason, someone might change it in 10 places, but forget the other 3, introducing bugs. It also doesn't explain why we are grabbing only dogs older than 6.

Let's use something called "scopes" to make a cleaner constraint.

```php
class Dogs extends Model
{
    function scopeAgeGreaterThan($query, $age) {
        return $query->where('age', '>', $age);
    }
}
```

This follows a very specific naming convention, which is a common thread in Laravel. The function should start with "scope" and the first parameter will be the injected QueryBuilder instance, which you can name anything but usually $q or $query is used for clarity. You can now use this function like this (restart your Tinker session for it to take affect):

```bash
App\Dogs::ageGreaterThan(6)->get();
```

Because the scope takes a QueryBuilder instance, you can actually string together any amount of functions such as multiple where(), orderBy(), etc.

This is nice, but it still is not very clear what the purpose is. The sad fact is, as dogs get older, they have trouble running away from rabbits and as a consequence are often bitten. We want to know which of our dogs needs to get its Rabbit Bite Shots. Let's make some changes to our model to state that in the code:

```php
class Dogs extends Model
{
    function dogsRequiringAntiRabbitBiteShot(){
        return $this->ageGreaterThan(6);
    }

    function scopeAgeGreaterThan($query, $age) {
        return $query->where('age', '>', $age);
    }
}
```

Restart Tinker and run it:

```bash
(new App\Dogs)->dogsRequiringAntiRabbitBiteShot()->get();
```

Notice a couple of syntax points above. dogsRequiringAntiRabbitBiteShot() is just an ordinary function on our Model object, and so it has access to the scopes in the same way our new App\Dogs does. Because of this, however, we cannot use the static :: to call it (without going down a rabbit hole of subsequent changes).

The example above is kept intentionally simple, and so if you are asking yourself "Why not just call the scope 'scopeDogsRequiringAntiRabbitBiteShot()'?" and do it in a single step, you are absolutely right. Real-world applications, however, usually have a set of criteria that defines a certain status or group (for example, age limits), and then later business reporters ask to see subsets of those (all dogs older than 6). I think you'll find that designing your models using smaller parts instead of a single, complete function in a repository can really help keep out duplicate code and will make much more sense when you go back to make changes six months later.

Tomorrow's lesson will introduce Global Scopes as well as the built-in Laravel scopes such as soft deletes. We won't keep repeating all the rebuilding steps each day, so come back to this lesson if you need help to remember how to reset your database for later lessons.

See you tomorrow!