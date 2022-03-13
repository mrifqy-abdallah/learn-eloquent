# Welcome to Lesson One of Eloquent by Example!

I hope you're excited to get going! We have a lot of things to cover, but I'm sure with these short, daily lessons, in a few weeks you'll be developing in Laravel quickly and confidently.

Today's lesson is all about those two words - "quick" and "confident". Developers spend way too much time trying to track down little bugs and strange behaviors reported by their testers, and all of this can be reduced if not completely eliminated by knowing your tools and building a good work environment.

## Things we'll learn:
- Migrations
- Seeders

Today's code is going to be completely throwaway, and so I'm going to skip TDD for this. Believe it or not, TDD will help you even at the earliest stages of just designing and creating your database schema. I didn't think that would be the case either - until the first time I tried it and saw how many simple mistakes I was catching that I would have had to go back and fix otherwise. That said, we have to walk before we can run, so let's focus on learning our tools.

As I've described before, rather than just walking you through the docs, these lessons are each going to contain an assignment you might actually have at your job and we'll work together to learn how to accomplish it. Here's what we have for today:

The application you're enhancing at work needs a new "dogs" module, and the boss has asked you to get things set up in the database that the team will be building on later. You need to create the table in a way that can be easily added to the other developers' locals and shared servers, as well as the Eloquent Model.

But wait! We know from our terrible last job where nothing was run efficiently that one of the biggest problems we had doing Q & A with our non-technical manager was the fact that we could never reproduce the data he was seeing on our own machine for debugging. We also ran into lots of annoyances with dirty, duplicated data from before a particular bug was fixed. It would be so much easier if we could all be looking at the same clean data.

### NOTE: I assume your database has been configured already. If you are unsure how to set that up, check the documentation here: https://laravel.com/docs/5.8/database. Additionally, if you have set up Homestead, you should ssh into your box to do these artisan commands from there.

## Migrations:
Okay, let's do this! The first thing we will do is create a migration. Let's start with the simplest way of doing this so we can see what's going on. From your command line,

```bash
php artisan make:migration create_dogs_table
```

Look in your '/database/migrations' directory and you'll see three files. `create_users_table` and `create_passwords_table` come with Laravel by default, but let's leave them there. You'll also have a file you just made called `<timestamp>_create_dogs_table`. Open that up and you'll see...very little. Just empty up() and down() functions. Checking your database, also no new `dogs` table. What's going on?

All we have done so far is create a basic scaffolding file to hold the information that will be turned into a `CREATE TABLE...` statement with a separate, similar, command called `artisan migrate`. When people first start working with migrations they aren't always completely clear about the difference, and so end up "fixing" a lot of things manually and just generally making a mess of it all.

This file will hold our table creation information, but until we run the actual `artisan migrate` command, we can do anything we want to it with no consequences - including throwing it away completely. Let's do that - let's delete that file so we can make a better version:

### NOTE! You may sometimes get a "failed to open stream: No such file or directory" error when you try to run this again. If you do, this is a composer issue. Simply run 'composer dump-autoload' and you will be fine.

```bash
php artisan make:migration create_dogs_table --create=dogs
```

Now if you look at the generated file, you'll see some helpful structure has been added to both functions. I'll let you read more about the migrations syntax on your own - for now, let's just add a "name" field after the id:

```php
Schema::create('dogs', function (Blueprint $table) {
    $table->increments('id');

    //new code here
    $table->string('name');

    $table->timestamps();
});
```

and then finally add the table to the database with:

```bash
php artisan migrate
```

When you run that, you'll see that all three migrations are run and the corresponding tables are created.

From this point on you can read the excellent documentation, but let's look at some important takeaways before we move to the next task.

1.  We have a "migrations" table with the name of our file and a batch number. Therefore, it is important that we not simply delete migration files without making sure the corresponding record in this table is also removed. Since that is a bit troublesome to do on staging or production machines, it is almost always better to use the :refresh and :rollback options to clean things up.

2. If we try to :rollback a migration and it fails due to a MySql syntax error, the migration will be removed from `migrations` table, but the table will not be dropped. It is important whenever we run `migrate` during development that we "roll back, roll forward" - run `php artisan migrate:rollback` to check that our down() method is also correct, and then migrate again once we are good to go. This will save us countless headaches!

## Seeders:
Now let's tackle that annoying problem of your manager looking at dirty data all the time. The answer to this is Seeders - a special set of classes that allow us to populate our database over and over with the same exact data.

We're going to learn in the next lesson how to make some really powerful seeders to produce great development and testing data, but today is already getting a bit long, so let us just introduce the idea. A "seeder", of course, is simply a class that populates your database. The nice thing about them is that you can use a simple commandline statement to refresh your data, so after you've been working for a while you can clean it up and start over. It also lets you avoid names like "assdffggggg" and other dirty data that may cause you to miss bugs.

Let's go to the commandline and type:

```bash
php artisan make:seeder DogsTableSeeder
```

If you open that file you'll see a very simple class with a single method called `run()`. There's nothing magical about this class - it is really little more than a wrapper around a Console Command class, made specifically to help with this particular task. Lets prove that to ourselves - add this code:

```php
public function run()
{
    echo 'Seeding!';
}
```

and then back to the commandline:

```bash
php artisan db:seed --class=DogsTableSeeder
```

It's calling from the DB facade, but there's no actual database interaction here. Let's populate the table now with a couple of dogs:

```php
public function run()
{
    \DB::table('dogs')->truncate();
    \DB::table('dogs')->insert(['name' => 'Joe']);
    \DB::table('dogs')->insert(['name' => 'Jock']);
    \DB::table('dogs')->insert(['name' => 'Jackie']);
    \DB::table('dogs')->insert(['name' => 'Jane']);
}
```

and run the `php artisan db:seed --class=DogsTableSeeder` line again. Your dogs table will have four pooches in it.

So we can keep deleting, adding, editing our dogs while we work, and then with a simple command reset them. Wonderful! There are a few improvements we can make, though. For one thing, we have to come up with all the names ourselves. Imagine if we wanted to test against 500 dogs!

Also, what if we have other logic we want to happen at the dog record creation time? Here we are just directly inserting the name into the table, but really we'll want to use the Eloquent ORM that comes with Laravel.

We'll look at that and a few other improvements to our process tomorrow!