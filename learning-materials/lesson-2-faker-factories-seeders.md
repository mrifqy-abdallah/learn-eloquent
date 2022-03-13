# Welcome to Lesson Two of Eloquent by Example!

Last time we got comfortable with spinning up database migrations, and dealing with how to make changes with them. Then we learned a fairly rudimentary way of seeding some data for our development and testing.

Today we're going to learn one more important step in creating an efficient workflow when we develop, and how to integrate it with our migrations from yesterday.

## Things we'll learn:  
- Faker
- Factories
- Advanced Seeders

## Model creation:
As a review - let's go destroy what we made yesterday. We know from yesterday we can't just delete the migrations file because that is registered in the `migrations` table and "dogs" has been added to our database, so doing that would cause a lot of problems. Let's do it correctly with a rollback:

```bash
php artisan migrate:rollback
```

and then delete the `<TIMESTAMP>_create_table_dogs.php` file.

You should have noticed that three different migrations - the two out-of-the-box User and Password migrations, and the one we wrote - were all rolled back and now we don't have any tables in our database. This is because `rollback` works on the "batch" (which you can see in the migrations table as a field) and we ran those three all at the same time. We could have used the new (to v5.3) `--step=1` to only remove the "dogs" table migration. I won't go into deeper details about migrations here but good idea to play with the different commands so you are completely comfortable. There are a few nice switches that can eliminate a bit of typing once you get going.

In the last lesson we created a table and then inserted data into it via our seeder. The problem was, we didn't yet have a Laravel Model for "dogs" and so we had to do straight database statements with the DB facade. A Laravel Model is important to us because it is more than just a way to interact with our database; it is a fundamental part of our Eloquent ORM and will help us build relationships to other data, set up Events to fire when we take certain actions, and a host of other things we'll be learning in this course. So we want to back up and start using a `Dog` model.

We could simple create it using:

```bash
php artisan make:model Dog
```

but I promised to teach you better workflow, not just a series of commands, so let's try this, instead:

```bash
php artisan make:model Dog -m
```

It made our migration file! Do you see that little `-m` flag? That's the trigger - it will take the name of our model and assume a lowercase, pluralized form for a table name. Go ahead and open that migration file (`<TIMESTAMP>_create_dogs_table.php`), add the "name" field like we did yesterday, and migrate it.

So now we have a very simple `Dog` model that we'll dive more into in the next lesson. For now, though, let's go back to our DogsTableSeeder class. Instead of using the `DB` facade, we're going to recreate this seeding with the new `Dog` model. It looks very similar, but we use `create()` instead. So now we will have:

```php
public function run()
{
    \App\Dog::truncate();
    \App\Dog::create(['name' => 'Joe']);
    \App\Dog::create(['name' => 'Jock']);
    \App\Dog::create(['name' => 'Jackie']);
    \App\Dog::create(['name' => 'Jane']);
}
```

Run this with:

```bash
php artisan db:seed --class=DogsTableSeeder
```

and look at the results in the table. Wow! Now the table has `created_at` and `updated_at` timestamps added!

This is just one of the advantages of using a Model class instead of the database insert. It's worth stopping for just a moment to think about the difference at a little more conceptual level. When we use the `DB` class facade, the only work that Laravel is doing for us is creating a db connection, parsing the values we pass it into an sql "INSERT" statement and sending it to the database.

Doing this via the Eloquent Model, on the other hand, means we are actually instantiating a very "opinionated" class. What does that mean, exactly? Eloquent will assume that if you create a model called "Dog" that your table name is called "dogs" and your primary key is "id" - so you don't have to tell it that. It assumes you will want a "created_at" and "updated_at" field - so you don't have to tell it that. We're going to dive much deeper into this in coming lessons, but that's a short answer as to why we don't want to "just write sql".

## Model Factories:

The above Seeder is very nice when you just need a few records, but what about times when you need to load up 100 dogs? Can you even think of 100 dog names? Obviously we want to use a loop for this, but where will the names come from? Random strings leave us with shouting, "Fetch, Qswdcg!" There must be better.

There is! If you look in your 'database/factories/' directory you'll find a file called `ModelFactory`. This is exactly what is says on the tin: a "factory" to create instances of a given Model class. Let's look at the example for Users that is already included:

```php
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});
```

That looks a little scary, but we can cut to the important parts. `$factory` is an instance of an EloquentFactory and that's already more than we need to know for this; in this file we are simply defining what we mean when we say, "Give me a new App\User::class instance". The other interesting thing for us is the `Faker\Generator $faker`. This is a wonderful library that will give us realistic fake data that we can seed from.

Let's make a factory for dogs and then see how to use it in our seeder. Add this code below the existing factory for User (you can have many factories all in the same file):

```php
$factory->define(App\Dog::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->firstName,
    ];
});
```

and now in our seeder:

```php
public function run()
{
    \App\Dog::truncate();

    (new Faker\Generator)->seed(123);

    factory(App\Dog::class, 50)->create();
}
```

After truncating the table, we do something very important: We give it a seed label. This "123" can be anything, actually, but it assures that the same data will be generated for us each time. This is how we are able to set up the testing user's app to be exactly like ours (without sending them a database file). Try running it with and without that line, and checking your "dogs" table after each. Finally, we call the Helper function `factory(<class>, <count>)` and it will create 50 dogs for us, each with a real but randomly selected name.

There's last tip to learn before we finish for today that will save us having to remember and type out that entire command. Open your file called `database/seeds/DatabaseSeeder.php`. This is a parent class where we can call each individual seeder we make, such as our `DogsTableSeeder`. Add this:

```php
public function run()
{
    $this->call(DogsTableSeeder::class);
}
```

Now we can recreate our entire database with seed data with one simple command:

```bash
php artisan migrate:refresh --seed
```

Two lessons done - and we've still barely touched Eloquent! That's going to change starting tomorrow, but what we have done is learned to quickly and painlessly creating a reproducible development environment that will save us a lot of time and frustration as we create our project.