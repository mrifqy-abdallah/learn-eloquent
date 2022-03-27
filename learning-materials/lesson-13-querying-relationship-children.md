# Welcome to Lesson Thirteen of Eloquent by Example!

We've focused a lot on the concepts and understanding of how Eloquent works. Today, let's do a few practical exercises that cover things you'll run into every day.

## Things we'll see:
- Querying Relations
- Querying Relationship Existence/Absence
- Counting Related Models

If you still have code from the last few lessons, you'll have both `hamsters()` - with a Many-to-Many relationship still set up - and `gerbils()`, which uses the `hamsters.user_id` foreign key. The tasks for today will use `gerbils()`, but recall that these are merely functions and so there is no need to remove any of the other code.

Task #1 - The boss has asked us to send out an email to any User who has registered their gerbil.

We'll use the Hamster 'gerbils()' for this, but first let's look at a less "eloquent" ways to compare because I think it will help you learn a lot about your various options. If you were to use sql to solve this, you would write something like this:

```php
$owners = \App\User::join('hamsters', 'user_id', '=', 'users.id')->get()->pluck('email');
dd($owners);
```

When we search this way, we cannot use 'gerbils' because that is a Hamster model function. In this case, we are writing sql to join *to the User model*. Remember that Eloquent is built on top of the Query Builder, so all of those functions (such as select() and join()) are available to us. Because the "base" of this query is an Eloquent model the result set will be a Collection - an object array that gives us a great deal of additional functionality. (We'll round out this course with a few words on Collections.)

`pluck('email')` is a Collection function that let's us take a specific key from the results. You'll notice that we get duplicates; we can fix this on the database side or in our Laravel app:

```php
// using DISTINCT
$owners = \App\User::selectRaw('distinct email')->join('hamsters', 'user_id', '=', 'users.id')->get()->pluck('email');

// filtering the Collection
$owners = \App\User::join('hamsters', 'user_id', '=', 'users.id')->get()->pluck('email')->unique();
```

Let's compare this to the Eloquent solution:

```php
$owners = \App\User::has('gerbils')->get()->pluck('email');
dd($owners);
```

This ends up using the following query:

```sql
select * from `users` where exists (select * from `hamsters` where `hamsters`.`user_id` = `users`.`id`);
```

The biggest difference in practical terms? The purely Eloquent method will allow you to create a view such as:

```php
foreach (\App\User::has('gerbils')->get() as $owner){
    echo $owner->email;
    foreach ($owner->gerbils as $gerbil){
        echo $gerbil->name;
    }
}
```

eliminating the need to do any sort of grouping code on the owners. Of course, all of these are variations on a theme, and the most important reason for choosing one over the other will probably be the size of your tables and performance of the queries.

Task #2 - supply a list of all the Users as well as a count of how many gerbils they have registered:

```php
$owners = \App\User::withCount('gerbils')->get();

foreach ($owners as $owner) {
    echo $owner->name . ': ' .$owner->gerbils_count;
}
```

Too easy! In fact, Laravel is absolutely full of helper methods like this that will turn ordinarily cumbersome tasks into a matter of writing a simple function. For this reason it is well worth your time to slowly and methodically read through the documentation. Knowing what methods are available will make an enormous savings in time and effort.

Task #3 - We're having User bring their gerbils in for shots, but want to break them into groups based on first letter of their name. Give me a list of all the Users with a count of gerbils whose name starts with "F".

```php
$owners = \App\User::withCount(['gerbils' => function($q){
    $q->where('name','LIKE', 'F%');
}])->get();

foreach ($owners as $owner) {
    echo $owner->name . ': ' .$owner->gerbils_count;
}
```

The point to see in this task is that nearly all of the Eloquent and Collection functions have a second or even third lesser-known set of arguments; whenever the main function expects multiple records, one of these is almost always a Callback that can filter or otherwise manipulate your results. With Eloquent that means requesting a smaller result set from your database, often speeding up your application.

This lesson could go on for a long time - there's no shortage of different "sql puzzles" to solve. Obviously it's not the point to try to address all of them. There are a couple of takeaways here:

- Every problem has multiple solutions, so use your DB::listen() to see the queries you are generating, and think about what you need the result set to look like
- There are a great many helpers and wrapper functions to speed up common tasks, so get familiar with the documentation
- Most functions allow additional arguments than can include callbacks. Use the docs or check the source code to see what other options you can use

Next lesson we'll round out this course with a look at Collections and working with results.

See you then!