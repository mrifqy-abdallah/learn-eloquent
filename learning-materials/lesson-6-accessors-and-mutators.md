# Welcome to Lesson Six of Eloquent by Example!

Welcome back!

One lesson you should know about designing applications in any MVC-like structure is that it is generally considered a bad thing to put too much data formatting in your views. A helper class called a "Presenter" is one preferred way to gather logic together and help keep your blades remain clean html as much as possible.

There are some other small tricks for pulling and pushing data from the database that you should know about - Accessors and Mutators.

## Things we'll learn:
- Accessors
- Mutators
- Carbon Dates

## Accessors:

When you wish to catch hold of the data as it is coming out of the database and passing through your model, you can do this very simply with an accessor. An accessor will allow you to manipulate a data record while it is still part of the model, changing it any way you would like. Let's do an example. On your Dogs model, add:

```php
function getNameAttribute($value){
    return strtoupper($value);
}
```

and then call "Jock" with:

```bash
App\Dogs::find(2)->name;  // result "JOCK"
```

That's a nice way to format database records before they hit the page, although it means that `name` will always be formatted that way (you can use `->getOriginal('name')` if you need to see it without the formatting).

Here's a little secret, though. Add this to your Dogs model:

```php
function getIdNameAttribute(){
    return $this->attributes['id'] . ':' . $this->attributes['name'];
}
```

and access it with:

```bash
App\Dogs::find(2)->idName;  // result "2:Jock"
```

The accessors allow you to use a shortcut of 'getAttribute($value)' to work with a column with less code. However, you can call any attribute - even if it doesn't actually exist in the database - by using that specific format. When we do that, however, we must access the $attributes array. As you can see, we can put any php code we want inside, creating rather complex formatting logic, and still not change our original values.

This makes it very simple to create a handful of different formats to present full name, first name, last name first, etc. in a clear, consistent manner.

## Mutators:

Mutators are just the flip side of accessors. They let you capture the value before it goes into the database, so that you can do things like encrypt passwords or clean your data. So for example:

```php
function setNameAttribute($value){
    return $this->attributes['name'] = strtoupper($value);
}
```

It's because of the shortened form of the code used in Accessors that people sometimes get confused about the syntax, but if you practice it a few times long-hand first you shouldn't have any trouble.

## Carbon Date Mutators:

This was touched on briefly in the last lesson when we learned about SoftDeletes, but Laravel models have an attribute called $dates. If you add any date field to this:

```php
protected $dates = ['birthday'];
```

it will be cast as a Carbon date. This allows you to do simple tricks like:

```php
Carbon::now()->diffInYears( $dog->birthday );
```

to get the dog's age, since birthday is also a Carbon date and so the two instances can be compared programatically with no messy work from you. That, in my humble opinion, is very nice!

Experiment with those a little, especially Carbon dates, and see what other uses you can come up with. In the next lesson we're going to learn some good habits for creating and updating records.