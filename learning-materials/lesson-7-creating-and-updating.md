# Welcome to Lesson Seven of Eloquent by Example!

One of the very first things you learned in Lesson One was the difference between `insert()` and `create()`. Inserting is a simple database action that makes an sql statement and puts data directly into the table. Creating is about instantiating a new Eloquent Model.

At the time that may have seemed a little pedantic, but today we are going to learn just how flexible and easy to use Eloquent's create and update statements are and why we prefer using the ORM for these tasks.

## Things we'll learn:
- simple create and update
- fillable/guarded attributes
- findOrNew
- firstOrNew/firstOrCreate
- updateOrCreate

## Create and Update:

The only actual difficulty here lies in the similarity of the functions that the QueryBuilder and Eloquent use. We've already seen that without Eloquent, to insert a new record into the database we use the DB facade. Let's compare the following, using Tinker:

```php
DB::table('dogs')->insert(['name' => 'Old Yeller', 'age' => 12]);  // result "true"
```

As we've mentioned before, this is simply an sql statement sent to the database, so it tells us that yes, "true", it successfully inserted. It won't add timestamps or do any other work for us. Now try Eloquent, but a long-hand method using save():

```php
$dog = new \App\Dogs();             // result "App\Dogs"
$dog->name = 'Just-right Yeller';   // result "Just-right Yeller"
$dog->age = 6;                      // result 6
$dog->save();                       // result "true"
```

This is a bit of a hybrid approach. We are creating a new model instance, and so we have all the benefits of Eloquent, but then we build it by setting the attributes one-by-one and finally saving.

Compare this now to the third way we have, with `create()`.

```php
\App\Dogs::create(['name' => 'Young Yeller', 'age' => 3]);

// Result: Illuminate\Database\Eloquent\MassAssignmentException with message 'name'
```

Whoops! MassAssignmentException? What is this about?

Perhaps you've read about the $fillable and $guard attributes. These are protective measures that help ensure that only the fields you wish to populate are inserted or updated. $fillable is a whitelist, $guarded the opposite. Since we didn't add `name` or `age` to the $fillable array, an exception is thrown.

Now, here's the funny thing about this that may trip you and waste a lot of time. Remember that we made `age` a nullable field? Let's only add `name` to the whitelist and see what happens. At the top of our `Dogs` model, add:

```php
protected $fillable = ['name'];
```

and now our insert (you'll have to exit and re-enter Tinker):

```php
\App\Dogs::create(['name' => 'Young Yeller', 'age' => 3]);
```

It simply strips out the `age` field and inserts everything else, without any sort of warning! A terrible thing to try to track down in a malfunctioning application.

You might think that most developers would therefore shun the use of create() and update() this way. In fact, rather than avoiding such obviously useful code, we prefer to write unit tests. This issue won't hit you as often as you might be thinking, but when you first start out it can be the cause of a lot of hair-loss.

I want to show you one more variation of those techniques (I told you - it can seem a bit overwhelming at first! Don't worry, this will be second nature very quickly.)

```php
$dog = new \App\Dogs(['name' => 'Young Yeller', 'age' => 3]);  

// Result: App\Dogs {#690 name: "Young Yeller",}
```

Ignoring the fact we still haven't added `age` to our $fillable array, have a look inside your database "dogs" table. No record added.

This "new Dogs" is only creating a php instance, not a database record to go with it. We will need to call `save()` if we want to do that. This leads us nicely into the next set of functions.

## findOrNew, firstOrNew/firstOrCreate:

These three functions are incredibly useful, and yet many Laravel developers shy away from them as something unfamiliar. There's no need to; in fact, you already understand them at this point.

The real difference we want to look at is "New" vs. "Create". As we just saw, whenever we use "new" with an Eloquent model, we are simply creating a new instance. It is no different than saying "$logger = new Monolog" or any other php instantiation. "Create", on the other hand, is a class function that also writes to the database, using save() internally. Its equivalent could be "$logger = (new Monolog)->log(['My message'])".

Therefore,

- `findOrNew` is simply: "Find a record with primary key X. If you can't find one, return a new, empty model instance"
- `firstOrNew`: "Find the first record that meets this where condition. If you can't find one, return a new, populated model instance"
- `firstOrCreate`: "Find the first record that meets this where condition. If you can't find one, make a new, populated model instance and also save it to the database"

Why would you want to make empty instances? There are a few reasons, but one is that when we search for a record, we will always have a model instance as a result class, even if the one we want isn't found.

```php
$dog = \App\Dogs::find(1);
$dog->name;  // "Joe"

$dog = \App\Dogs::find(100);
$dog->name;  // null
```

This means we can write our code more consistently and cleanly, because we don't have to litter it with lots of `if (!empty($dog))` conditionals all over the place. The same code will work even if $dog is empty.

## updateOrCreate:

There's one last function to look at before we stop for today. updateOrCreate() lets us wrap up a multi-part search with either an update or create statement. For example, if we use our "dogs" table again:

```php
\App\Dogs::updateOrCreate(['id' => 1, 'name' => 'Joe'] , ['age' => 15] );
```

will search for a dog named Joe who is at id 1, and since it finds him, will set his age to 15. On the other hand,

```php
\App\Dogs::updateOrCreate(['id' => 2, 'name' => 'Joe'] , ['age' => 15] );
```

will create a whole new dog record with 15 year old Joe and an id of 2. This comes in handy for things like a User profile, where you may not want to ask the user to fill in a lot of information when they register, but will want to be able to show them a profile screen where they can finish updating later.

That was a lot of different techniques and information. I'd really suggest that you take a little time and just experiment with those functions; see what works as expected, what doesn't, and what interesting variations you can come up with. There are more small tricks you can use with these, so have a look at the links I posted below as well.

Tomorrow we're going to do more advanced where clauses than what we've seen, which will also give us a chance to practice our updates more than we had time for.

See you next lesson!