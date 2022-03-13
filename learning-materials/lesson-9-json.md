# Welcome to Lesson Nine of Eloquent by Example!

Json is pretty much the format *de rigueur* these days for passing data around the web, and Postgres and MySql 5.7 and later support a json datatype that can make quick storage and retrieval a breeze. Before this, data could be serialized and stored but to actually search or work with it meant first retrieving and deserializing it; now, you can write where clauses and updates just like any other field type.

## Today we'll learn:
- JSON where clauses
- updating json values

For today's exercises, let's start fresh as a review and then look at the new features. This should just take us a few moments by now. Create a Cats model and migration (I know - I hate cats, too. But we already used Dogs and what else is left?)

```bash
php artisan make:model Cats -m
```

We'll keep this all simple and to the point, so our migration file will only have:

```php
Schema::create('cats', function (Blueprint $table) {
    $table->increments('id');
    $table->json('info');
    $table->timestamps();
});
```

and then run the migration.

I'm also going to let you cheat now - Tinker will get a bit cumbersome to use for some of this, so go ahead and create a callback route to do your work in:

```php
Route::get('cats', function(){ ... });
```

Let's encode and insert a few cats (I'm leaving out a step in this - can you remember what it is? You'll know in a moment):

```php
Route::get('cats', function(){

    \App\Cats::create(
        ['info' => json_encode(['name' => 'Fluffy', 'long-hair' => true])]
    );

    \App\Cats::create(
        ['info' => json_encode(['name' => 'Furball', 'long-hair' => false])]
    );

    \App\Cats::create(
        ['info' => json_encode(['name' => 'Igor', 'long-hair' => true])]
    );
});
```

If that threw a MassAssignmentException, remember to go to your Cats model and add 'info' to the $fillable array at the top.

Now that we have a few cats in the system, here are today's tickets.

Ticket #1 - The name is entered wrong - it is "Firball", not "Furball". Please update.

```php
\App\Cats::where('info->name', 'Furball')->update(['info->name' => 'Firball']);
```

Here's where we can see the `->` notation used in json where and update functions. This corresponds to the dots you use in javascript responses to drill down on your data; obviously that won't work here as the dot is reserved for DB.table_name.field_name.

Notice, also, that we are able to write mass updates in Laravel by using a wider-reaching where constraint. Of course, this means all Furballs are now "Firballs". If wanted to work with only a specific record we would be more likely to do this:

```php
\App\Cats::find(2)->update(['info->name' => 'Firball']);
```

By the way, this is done in MySql with the `json_set` function. I've included a link about it in the "Further Reading" at the end of this email.

Ticket #2 - The new fashion is short hair on cats, so marketing would like a list of all of them.

Much like the first task we did, a where clause and then remembering to return data with get() will fetch these for us.

```php
return \App\Cats::where('info->long-hair', true)->get();
```

At the time of writing this the json functions seem to be limited to simple where clauses and updating the serialized values; there is no way to use them in select(), orderBy() or more complicated filters. To do these you'll need to get the result set and do further processing. That said, these can be incredibly useful and great to keep in mind.