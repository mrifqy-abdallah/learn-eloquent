# Welcome to Lesson Fourteen of Eloquent by Example!

We've reached the final lesson of this course! I hope you feel you have a much stronger sense of "what it all means", and how to work effectively with Laravel's Eloquent ORM. Of course, in some ways we've only scratched the surface, but I think from here on out you will be looking more at the specific functions and techniques available and be in a better position to quickly grasp and put them to use.

We've spent our time learning how to get data out of our database, so let's finish up by learning a few tips about what to do with it once it is in our application.

## Today we'll learn:
- Collections

What is a Collection? A Collection is a Laravel class that implements the native php ArrayAccess interface to create an ArrayObject.

That's a mouthful that may or may not be meaningful to you, depending on your knowledge of native php before you started this course. In simpler terms, it is a class that works like an array - including, very importantly, Countable and Iterator interfaces - to work with either normal arrays or arrays of objects. The objects we are concerned about, of course, are Model instances. The very nice thing about Laravel's Collection class is the dozens of useful functions that have been added to allow you to work with data result sets.

You could write whole books about just this one class (as a matter of fact, I did just that. Link in the "Further Reading" section) so sticking to this lesson's style, let us just focus on a few of the points that people new to Laravel often get confused about.

## Single vs. Multiple Return Instances:

```php
dd(\App\User::find(1));
dd(\App\User::whereId(1)->first());
dd(\App\User::all()->first());
```

Those very simple Eloquent queries that all return the same result for our particular data. When you dump the results, however, you see that what you get back is an instance of a User Model.

```php
dd(\App\User::find([1]));
dd(\App\User::whereId(1)->get());
dd(\App\User::whereEmail('jeff@codebyjeff.com')->get());
```

Do you see the slight differences in the function calls? Our first set was specifically asking for a single Model instance to be returned, but the others assumed that there might be more than a single record (even when logically we knew there couldn't be).

This is one of the small things that trips people up a lot at first. If you are using a singular function call such as `find()` you will have to either wrap the result in a new `collect()` or forgo using loops and other Collection functionality.

For this reason, many developers eschew using the `find()` and `first()` methods and instead always return a consistent object into their functions. That's entirely up to you; just understand the difference.

## New instance vs. Modifying:
```php
$hamsters = \App\Hamster::all();
$results = $hamsters->every(2);
dd($hamsters, $results);
```

When you run that, you see that `$hamsters` remains the same and that the filtered results are put into a new variable, `$results`. Most BUT NOT ALL Collection functions will maintain the original array and pass back a modified copy; be sure to read up on the ones you are using so you know which to expect.

## Chaining and toArray():
```php
$hamsters = \App\Hamster::all()->every(2)->toArray();
dd($hamsters);
```

Since most of the Collection functions return an instance of a Collection, chaining is as easy as...well, chaining. Kill off those temp variables!

The other important piece is `toArray()` - anytime you need to pull your data out of the objects and into an array, this is the way you do it.

So that brings you to the end of this course! I hope you feel this was productive and has given you better insight into Laravel's Eloquent ORM. If you have thoughts or suggestions on how I might improve things, please let me know. Likewise, if you felt it was worthwhile and could help me spread the word by sharing this with friends, it would be greatly appreciated!

A final note - I also put out a weekly Laravel Quick Tips Newsletter that covers all aspects of Laravel, Vue.js and database querying. I hope you'll join me there, as well, and learn more about this wonderful framework!

Hope that helped!