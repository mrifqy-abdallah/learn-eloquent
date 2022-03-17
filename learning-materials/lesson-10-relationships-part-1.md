# Welcome to Lesson Ten of Eloquent by Example!

We now approach the heart of Eloquent - Relationships!

We've already seen how an ORM, with its use of model classes, is very different conceptually from a simple Query Builder tool. Those same ideas apply here, as well. If you think of Eloquent Relationships as nothing more than syntax sugar to deal with joins, you will really miss out on the strengths of using an ORM. Eloquent is a rich and powerful tool that will not only make your development easier, but keep your database records clean and in sync.

To be honest, I could have made this entire course on nothing more than this particular chapter of the documentation. When you stop and think that Eloquent is on the scale of projects such a Doctrine, that isn't so far-fetched. You are going to need to keep the docs handy while you work, and expect to take some time to master things. For that reason, we aren't going to go over code examples of every type of join, every type of constraint...that's pointless, over-whelming, and goes against the philosophy behind these lessons. Instead, we will look at some of the most useful points and use them to think about how we'd like to set up our applications.

Let's get to it!

## Today we'll learn:
- How to set up relationships in our Models
- Saving child records

To make sure that we start clean, we're going to work with Hamsters today. You should also have a default migration and model for User that came with your Laravel install.

Setup Hamster with the following:

```bash
php artisan make:model Hamster -m
```

```php
$table->unsignedInteger('user_id');
$table->string('name');
```

You'll notice we use an "unsignedInteger" - this is because the default increment type is an unsigned integer, as well. Run your migration, and then create two Users with any technique you like, such as Tinker or a Route::get() you can throw away:

```php
\App\User::create([
    'id' => 1,
    'name' => 'Jeff',
    'email' => 'jeff@codebyjeff.com',
    'password' => \Hash::make('pass123'),
]);

\App\User::create([
    'id' => 2,
    'name' => 'Sam',
    'email' => 'sam@codebyjeff.com',
    'password' => \Hash::make('pass123'),
]);
```

Great! We are ready to go.

## Saving Child records:

The boss has asked us to hook up the backend for a new screen in our application. The logged in user is able to add the names of all their hamsters. Some have quite a few of them.

We'll develop this by just using:

```php
$user = \App\User::find(1);
```

so we can ignore using Auth::login() logic that doesn't really relate to what we are doing. After that, it should just be a matter of saying:

```php
\App\Hamster::create([ 'name' => 'Furry', 'user_id' => $user->id]);
```

Well...no. We *could* do this, and it would work...mostly. The thing is, we aren't allowing our ORM to control and decide things. What happens if some later developer mistakenly adds some other code between finding our User and creating our Hamster? We might end up with a bad relationship.

Better is to tell Eloquent, "This Hamster belongs to that User - link them up, please". The relationship is created programmatically and there are no tears from the hamster owner.

To do this we want to first tell the models about the relationship. A User can have many Hamsters, so we'll put a `hasMany()` link on the User model like so:

```php
function hamsters() {
    return $this->hasMany('App\Hamster');
}
```

There is an inverse of this, as well - belongsTo() - and many people get confused and think that both of these need to be set up initially for the relationship code to work. They do not - only for the direction you plan to use. If we wanted to find a Hamster and then see who its owner was, we would add `belongsTo('App\User')` to the Hamster model, but we are fine for now.

Now let's make some Hamsters! I'd suggest once again working from a Route::get('hamsters', function(){ ... }); as it will be much easier to work with than Tinker for this.

We still have one more thing to consider. We have small paradox at the moment. We need to make a Hamster in order to pass it to the User; however, a Hamster requires a user_id in order to save to the database. What do we do?

Remember the difference between `new App\Hamster([])` and `App\Hamster::create([])`? This is a perfect example of where we want to set up the Hamster instance, but then let Eloquent take over.

```php
$user = \App\User::find(1);

$hamster = new \App\Hamster([ 'name' => 'Furry']);

$user->hamsters()->save($hamster);
```

There is also a `saveMany()` function that might be more useful to us in this case, but all good. Let's check it and make sure Furry found his owner:

```php
dd(\App\User::find(1)->hamsters()->get());
```

will give us a Collection of Hamsters.

Just a couple of important concepts before we stop for today that can help you understand when "things look weird".

1. For our purposes today, `\App\User::find(1)->hamsters` is just short-hand for `\App\User::find(1)->hamsters()->get()`. You'll actually find yourself using the shorter form most of the time, but it was good to see what was being called internally.

2. "hamsters" is just a function name. It can be "rats" and still call the Hamster model inside the function.

3. "hamsters()" is just a function. Anything can go inside it. For example, we can name it something clear and call a scope on the Hamsters model:

   ```php
   function oldHamsters(){
       return $this->hasMany('App\Hamster')->getOldHamsters();
   }
   ```
   
   where `getOldHamsters()` is just a model scope such as we made earlier.

Make sure those last three ideas are clear and play with them a little bit. We're going to look at more complicated relationships in our next few lessons.

See you then!