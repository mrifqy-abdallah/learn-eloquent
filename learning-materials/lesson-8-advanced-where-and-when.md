# Welcome to Lesson Eight of Eloquent by Example!

In our last lesson we learned more about the way that creating and updating with Eloquent works, and did a lot of examples with adding new records. One of the other important concepts is filtering records, either for results or to update single records or subsets.

There is a whole list of convenient where clause functions in the documentation you can pick up over time. Today, though, our boss wants some reports, and we need to figure out how to deliver.

## Things we'll learn:
- QueryBuilder class
- advanced where clauses
- parameter grouping
- conditional clauses

Before we get started, go back and rerun your DogsTableSeeder so we all start on the same page.

```bash
php artisan db:seed --class=DogsTableSeeder
```
Also - DON'T FORGET TO REMOVE YOUR ACCESSORS AND GLOBAL SCOPE FROM DOGS MODEL! (I should have made that an exercise to debug, but we're friends.)

## Parameter grouping:

The bosses first request is for dogs younger than 6 or older than 8 and also named Jane or Jerry. Meaning, we'll need a parenthesized, two-part where clause that in sql would look like:

```sql
WHERE age < 6 OR (age > 8 AND name IN ('Jane', 'Jerry'))
```

```php
return \App\Dogs::select('name', 'age')
    ->where('age','<', 6)
    ->orWhere(function($q){
        $q->where('age','>', 8);
        $q->whereIn('name', ['Jane', 'Jerry']);
    })
    ->get();
```

Let's tear that apart for a moment so we can study the grouping we are interested in. The first where clause is the most simple form and one we already know. It will concatenate the field name with the next two arguments; you'll find it can do this in a great many ways, such as:

```php
->where('name', 'LIKE', '%an%' );
```

The second part is more interesting; by passing a *closure* we are able to construct groups of clauses. Two important ideas to pick up on. First, Eloquent is constructing all of this on top of an instance of Illuminate\Database\Eloquent\Builder, and the $q being passed into the function is a reference of exactly that (you can verify this by putting a `dd($q)` in your code and checking it out). Therefore, unlike what we did with scopes, we do not want to `return` from here. The other thing is, the function has normal php variable scope, so if we wanted to pass an age or array of names, we would need to use the `use` keyword. For example:

```php
$age = 8;
...

->orWhere(function($q) use ($age){
    $q->where('age','>', $age);
})
```

## Conditional Clauses:

If you simply write:

```php
$dogs = new \App\Dogs;
dd($dogs);
```

you will get back an Eloquent model. This is built atop the Eloquent Builder class, and already linked to the "dogs" table, but there are no results yet because you haven't asked for any. Using `all()`, `find()`, `first()` or `get()` will do this final step for you.

A slight variation on this to look at is:

```php
$dogs = \DB::table('dogs');
dd($dogs);
```

This is only the *underlying Eloquent Builder class*, with none of the Dogs Model functions available to us.

You will find many examples of this second set of code used for creating dynamic queries; $dogs is misnamed here, because it is actually the $q from our example above. As such, we can piece together our sql clauses with normal php flow:

```php
$dogs = \DB::table('dogs');

$ageGroup = 'older';

if ($ageGroup == 'older'){
    $dogs->where('age','>', 8);
} else {
    $dogs->where('age','<', 8);
}

dd($dogs->get());
```

This probably looks familiar to you from things you've done in the past. There are times when you may still have to resort to this, but it's not the best solution because we are losing the power of our ORM. Fortunately, there is function that can work with Eloquent models that addresses this:

```php
->when(condition, true callback, <optional false callback> )
```

Let's rewrite our dynamic query using Eloquent:

```php
$ageGroup = 'older';
$dogs = \App\Dogs::select('name', 'age')
        ->when($ageGroup == 'older',
            function($q){
                return $q->where('age','>', 8);
            },
            function($q){
                return $q->where('age','<', 8);
            }
        );
        
dd($dogs->get());
```

The condition `$ageGroup == 'older'` is checked; if it is 'true' then the first where() is used, or else the second one is added to the query. When() can be used for any part of the query, not just where clauses, making it an incredibly flexible tool.

Where() with callbacks and when() are two very powerful functions you'll find yourself reaching for very often. Take some time and play with these, seeing what sort of complicated dynamic query logic you can make with them. Tomorrow we're going to work with json a bit and then introduce relationships to round out the week.