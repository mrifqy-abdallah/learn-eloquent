# Welcome to Lesson Twelve of Eloquent by Example!

Developers like to know what's going on "under the hood". We are perfectly happy allowing clean, tested functions to do our annoying tasks for us, but we want to know what they are up to inside. One of the most common questions you'll read from people starting out with Laravel's Eloquent is "How do I see the actual queries being run?"

One of the most important Laravel tools that you should all learn to install and use is Barry vd. Heuvel's Debugbar (https://github.com/barryvdh/laravel-debugbar). This development tool will give you all sorts of incredibly useful information about your queries, views, memory usage and more.

For today we'll use a very quick and dirty trick to study our queries: DB::listen.

## Today we'll learn:

- How to log or read raw queries
- Relationship Methods Vs. Dynamic Properties

## DB::listen:

Laravel makes using the Illuminate\Database\DatabaseManager easier through the use of the DB facade. This is how we use the Query Builder directly, as we saw earlier in this course, but it also adds in a few handy methods to let us get more granular. One such method is `listen()`, which we will use to look at the raw sql queries and bindings that Eloquent is generating.

The easiest way to do this is in our AppServiceProvider class. In the `boot()` function, add this code:

```php
\DB::listen(function ($event) {
    dump($event->sql);
    dump($event->bindings);
});
```

and then run:

```php
\App\User::find(1);
```

You will see the raw query with "id = ?" and an array listing the value bindings.

Let's use this to study our relationship queries. To do these next exercises, reuse your Hamster model from the last lessons with a few changes. Because we made that a Many-to-Many in the last lesson, check that you have the following or set it up:

- Hamsters table should have a "user_id" field
- Have at least two records in Hamsters table, with user_id = 1 for both of them
- On User model, set up a hasMany relationship called "gerbils"
  ```php
  function gerbils() {
      return $this->hasMany('App\Hamster');
  }
  ```

- On Hamster Model, set up a belongsTo relationship called "user"
  ```php
  public function user(){
      return $this->belongsTo('App\User');
  }
  ```

Remember that the names of the functions can be anything, so `gerbils()` will just work without any additional setup. The exception to this is where we rely on Laravel to build joins based on expected names. For example, calling the Hamster function `user()` means that eloquent knows to join `hamsters.user_id` to `users.id` without being told. If we had called it` Hamster->owner()` instead, we would have had to explicitly set that or else Laravel would look for an `owner_id` foreign key.

## Relationship Methods Vs. Dynamic Properties:

You may have seen the following two syntaxes used, and wondered what the difference is:

```php 
\App\User::find(1)->gerbils()->get();

\App\User::find(1)->gerbils;
```

If you run these and dd() the output of each, you'll see that in this case they are the same. The first case is the normal relationship method we already know about; the second is called a dynamic property and is short-hand for the method syntax - with one important difference.

Run the inverse relationship to see what I mean:

```php
$hamsters = \App\Hamster::get();

foreach ($hamsters as $hamster){
    echo $hamster->user()->first()->name;
}

$hamsters = \App\Hamster::with('user')->get();

foreach ($hamsters as $hamster){
    echo $hamster->user()->first()->name;
}
```

The dynamic property version has a problem known as the "N+1" problem. It makes a new query for each user separately to get the names of the users. The second example makes use of something known as eager loading. You can see how this works with the output query - it collects all the needed user ids, and then makes a single WHERE IN query.

If you only need to work with the property itself, like when we were getting hamsters from the User in the top example, then there is no need to add the small overhead of eager loading. If you do run into an N+1 case this can be a huge performance boost. It's important to understand when you want to add this.

A small note that may save you some frustration. The `with('user')` syntax refers to the name of the function that holds the relationship you want to eager-load. Most of the time you'll follow Laravel's recommended naming patterns, but should you ever need to stray from that, understand that it is the function name and not the table name or class that you are referring to.

There's something else to notice here that we will talk about more in a few lessons. Each $hamster calls user(), but recall that this is just an arbitrary name. We don't know at this point that only a single record will exist, and so a Collection is always returned. Because of this, we will need to "cheat" and use the first() function to get a single user instance instead of `get()` before asking for the "name". Forgetting that simple point about the return type has cost many lost development hours!

This was an important lesson in understanding querying relationships. In the next lesson we'll focus a little more on the actual code.