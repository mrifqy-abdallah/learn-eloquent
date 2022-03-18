# Welcome to Lesson Eleven of Eloquent by Example!

Welcome back! Hope you spent some time working on your relationships!

Last time we looked at some simple relationships and what is really going on in those functions. Today we're going to learn how to handle Many-to-Many tables - when more than one user can be listed as the Hamster's owner - and different ways we can work with that relationship.

## Today we'll learn:
- Many-to-many relationships
- Working with Pivot Tables
- Using dedicated controller and model

## Many-to-many relationships:
If you remember from Lesson Ten, we created Hamster records with a generic user_id field. We just sort of assumed that that User was the owner, but really there are a lot of different relationships a User might have with a Hamster. Child owner, parent, veterinarian, fan club member - the list goes on. Precisely why we don't want to start adding all sorts of `<role>_ids` to the Hamster record; it will be hard to query later, and we really don't know how many possibilities we'll end up with or how many times we'll need to modify the table and model. Many-to-many - or "pivot" - tables are the common solution for this, and Laravel makes these very easy to use.

Let's make the changes we need to set up a pivot table linking Users to Hamsters, and allowing a "role" field so we can describe the relationship. We don't have to go undo all our existing code for this, so it's actually much faster and easier than you might think.

Since we will always be asking for the Hamster's Users or the User's Hamsters, we only need create a join table and the relationships on the User and Hamster models. Of course we can set this up any way we like, but if we follow a standard naming pattern, Laravel will do most of the work for us. We don't need a model this time, so our command to create a migration will be;

```bash
artisan make:migration create_hamster_user_table --create=hamster_user
```

The specific naming pattern is very important to get the full benefits Laravel provides, so take a moment to review the documentation on this. In short, the singular form of each table, in alphabetical order is how the new table should be named with _id for each of the two joined tables, but you can configure all of this.

```php
Schema::create('hamster_user', function (Blueprint $table) {
    $table->unsignedInteger('hamster_id');
    $table->unsignedInteger('user_id');
    $table->string('role');
    $table->timestamps();
});
```

One our User and Hamster models we need the relationship function. We also need to mention that we have a pivot table field, "role", that we'd like to be available.

```php
// User.php
public function hamsters()
{
    return $this->belongsToMany('App\Hamster')->withPivot('role');
}

// Hamsters.php
public function users()
{
    return $this->belongsToMany('App\User')->withPivot('role');
}
```

When we had a simple One-to-Many (one User with many Hamsters) design, we created the instance of the Hamster then asked Eloquent to handle both linking and saving it. With Many-to-Many relationships, things are a little different and might seem a bit confusing at first.

The first difference is that we no longer use that Hamster.user_id, so we can save our Hamster to the database in a normal fashion. What we will work with now is no longer the Hamster instance but rather just its id value. We have two ways of doing this, but I think you'll find the second far more useful.

```php
// an existing Hamster from last lesson
$hamster = \App\Hamster::find(1);

// a simple "attach"
$user->hamsters()->attach($hamster->id, ['role' => 'owner']);

// or instead, a complex "sync"
$user->hamsters()->sync([ $hamster->id => ['role' => 'owner']]);

// view our User's hamsters just like before
dd($user->hamsters);
```

Attach() works very much like a save(), allowing you to add a single Hamster to a User with the option of adding pivot table values at the same time. If you look carefully at sync(), it is the same thing but instead of a single Hamster, you attach an array of them. Look again:

```php
// attach
$hamster->id, ['role' => 'owner']

// sync
[ $hamster->id => ['role' => 'owner']]
```

So why didn't Taylor just make an attachMany() function? Sync() actually does more than that - it clears all the existing ids that are not in the array you pass it, and then adds the links. This is wonderful for when you have a list of checkbox options on, say, a User Profile that might be turned on or off with the update. The other problem this solves is that attach() will keep attaching every time you tell it to, making duplicates that you probably don't want.

Here's a useful tip before we move on. Attach() will always add duplicates, but sync() requires you to give a full list of the ids to keep each time. How can we simply add a few new unique items without losing the ones we already have?

SyncWithoutDetaching() to the rescue! This works just like sync(), but will not detach, only add new uniques. In fact, it is actually only a wrapper around sync() with a second parameter, which means you can set that on the fly:

```php
->sync([1,2,3], false);  // under the hood for syncWithoutDetaching
```

## Using a dedicated controller and model:

Laravel provides some genuinely convenient functionality for dealing with what are really very cumbersome, painful but everyday necessities. The amount of repetitive code you'd have to strap together to duplicate sync() should make you thankful for how easy Taylor has made this all.

There's another line of thought these days stemming from the increased use in apis and how to best "phrase" the url to more complex requests. It seems straightforward to ask for 'users/1/hamsters' or just 'users/hamsters'. What if you want all the Doctors who have Hamsters of status "newborn"? Where should you put a controller function that looks up User #1 and Hamster #3 - on UserController or HamsterController?

Instead of thinking of hamster_user as simply a link, the new thought is to consider it as its own entity, a "HamsterUser". This allows you to set up its own controller and model and stay with standard RESTful requests to interact with it. In fact, why not have it all status-based - have a DoctorController or a NewbornController, rather than trying to jam dozens of functions onto a single class?

There's no "right" answer to these questions. The question you should ask yourself is, "Does this fit as a normal CRUD function?" If it doesn't, perhaps it should be its own entity. Food for thought when designing your application.

In our next lesson we're going to look a little deeper into querying all this related data. See you then!