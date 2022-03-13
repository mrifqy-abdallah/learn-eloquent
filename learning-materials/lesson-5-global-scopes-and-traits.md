# Welcome to Lesson Five of Eloquent by Example!

It's been a busy first week! Learning Eloquent and the whole Laravel Framework is an ongoing process. You'll need to keep reading the docs and blog posts as to keep up on improvements and learn new ways to use the existing code. For that reason, our focus has been on the tools we have available to us to make our work fast and efficient, so that we can spend our time solving the real problems.

Now we've shifted to the code, but we're going to keep the same general idea. We want to learn the important underlying ideas rather than lots of code samples that may only apply in certain circumstances. Yesterday we looked at local scopes not just as a way to add where clauses, but with an idea of how to organize our code. Today we'll expand on that by showing you what Global Scopes are and introducing Traits in Laravel.

> I implement this part on Cat instead of Dog model

## Things we'll learn:
- Global Model scopes
- Soft Deletes
- Traits

## Global Scopes:
Global Scopes are not "global" in the sense of a global variable, but rather applied to all records on that model in addition to any other constraints. These are generally reserved for more system level constraints, and may work in conjunction with a trait such as the SoftDeletes we'll discuss later in this lesson or Multi-Tenancy requirements.

There are two ways to use Global Scopes; by creating a separate class, or simply using the Model itself. A separate class makes the scope reusable, of course, as you can then bind it to any models that are appropriate. For the sake of brevity we'll use the other technique and just put an age scope on our Dogs model.

First, our Dogs.php needs to use the Builder class for this, so at the top add:

```php
use Illuminate\Database\Eloquent\Builder;
```

Most Laravel classes have a "boot" function that is called as part of the class' instantiation. This is where we usually register things like Observables and Events, as well as adding our Global Scope in this example. Be sure to call the parent()!

```php
protected static function boot()
{
    parent::boot();

    static::addGlobalScope('age', function (Builder $builder) {
        $builder->where('age', '>', 8);
    });
}
```

What we've done is added a filter to block all the younger dogs, all the time. Let's fire off a few different queries to see what effect it is having. First, run this without any scope constraint so we can see all four of our dogs:

```bash
App\Dogs::withoutGlobalScope('age')->get();  // Result is four records
```

and then:

```bash
App\Dogs::all(); // Result is one record
```

You see that the second query is running that where() constraint "under the hood". Let's see one more thing:

```bash
App\Dogs::find(2);  // Result is null
```

You see that even though we specifically called for Dog "Jock", the where clause was added and so the actual query that ran was "where name is Jock and age is over 8", which there was none. The same will happen if you try to update the record. Don't let that trip you up!

Because of the way that Global Scopes are implicit rather that explicit, you - or the other devs that look at your code later - can waste a lot of time trying to work out why your records aren't appearing in the results. For that reason, I strongly recommend that you leave nice big notes at the top of any model where you include these. It may seem obvious when you are reading this simple example, but add DocBlocks, annotations and all the other trappings you find in an actual live codebase and you can waste an infuriating amount of time trying to work out what's going on. You've been warned!

## Soft Deletes:

You all know better than to actually delete database records, right? I say that slightly tongue-in-cheek, because we database programmers are anathema to deleting anything, but it is a serious matter. Actually deleting records - especially accidentally - can lead to all sorts of issues such as orphaned records and lack of historical data for analysis. Better is to somehow "flag" them as active or not. However, doing this means adding an "active_flag" record on all your tables and then remembering to put it in all of your where clauses. Not ideal. You've already figured out a better solution, haven't you? Global scopes!

Because this type of global scope is so universally desired, Laravel has done what Laravel does best - they've baked in a simple to use solution, called "Soft Deletes". It works behind the scenes, in combination with some extra functions, to mark records as deleted with a timestamp field named "deleted_at". There's even a migrations function to add this!

Actually using these is slightly different than setting up global scopes, so let's take a quick look. I'm only going to post some generic code here, but it wouldn't be a waste of time to rollback your Dogs migration and try this out for yourself.

In your migration, you can easily add the nullable field by using:

```php
$table->softDeletes();
```

Notice that this is a nullable field; the softDelete works by restricting records that are NOT null. In our model we will call the trait, as:

```php
use SoftDeletes;
```

We also want to cast the field as a Carbon date. This is a really lovely feature of Laravel (you'll hear me say that a lot). Any date field that is added to the $dates attribute at the top of your model like so:

```php
protected $dates = ['deleted_at'];
```

will be cast as a Carbon class instance. This gives you a multitude of easy to work with functions when you need to format, compare or otherwise manipulate your dates. I suggest reading up on their docs if you aren't familiar with them.

That's it! You're done! Using the Eloquent delete() function will automatically timestamp that record as deleted, and it will no longer appear in any results unless you specifically ignore the scope. The functions to do that are a little bit different, as well, as they are marked cleanly on a trait, so look over the docs (link below). Avoid updating that field to a timestamp manually if you can - there's no point writing that ugly code.

## Traits:
We tend to use traits in Laravel to group related functions, even if they are not intended to be reused with other classes. If you pop open SoftDeletes in your IDE you'll see an example where they clearly are being re-used, but the entire Authentication system is built around traits that serve to keep all the password functions, reset functions, etc., together for cleaner workspace.

These are straightforward php traits and so there are no hidden secrets or any abstract class you should inherit from. Create the trait (typically in a new Traits directory), include the namespaced class as well as "use FooTrait" at the top of your model, and you are good to go.

How might you use traits? I often use them on controllers for helper functions, letting my controller simply list the main route functions, and shifting all of "action" functions to a separate service class. This really boils down to your own style and preference, however - whatever makes sense to you and helps you keep your code clean.

So there you've made it through the first week! I hope that these early lessons have been helping in getting you to rely more on the Laravel tools that were put there to help you, and that you are starting to see how much more quickly and surely you'll be able to work if you rely on them.

Next time we're going to look into presenting our data as well as creating and updating, more advanced where() techniques and a first glance at relationships.

See you then!