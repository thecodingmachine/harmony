Utility functions
=================

When you write plugins for Harmony, you will often need
to access your application's context. This is one of the main difficulties when you develop Harmony plugins.
Harmony runs in its own context. It has its own classes, its own autoloader. Still, you often need to trigger a function 
call in your application.

For instance, you might want to modify a session variable in you application (but the session of your application
is not shared with Harmony session).

Hopefully, Harmony comes with a number of tools to help you bridge the gap between Harmony's context and your application's
context.

Running code in the application's context from your Harmony plugin
------------------------------------------------------------------

Harmony comes with a powerful tool named `CodeProxy`. This is a class that will let you run code inside your application's context.
Usage is fairly simple:

```php
// This code is part of an Harmony plugin. Therefore it runs in Harmony's context.

$codeProxy = new CodeProxy();
$result = $codeProxy->execute(function() {
    // Your code here runs in the context of your application!
    
    // The return value is passed back as the result of the "execute" function.
    return "Hello world!";
});

// Prints "Hello world"
echo $result;
```

<div class="alert alert-info">Behind the scene, the CodeProxy is starting additional PHP programs.
This means that the closure you are passing in parameter to "execute" is serialized, and that the return value
is also serialized (thanks to the [fantastic jeremeamia/super-closure library] TODO provide link).
You can therefore pass primitive types easily (strings, arrays...) If you want to pass objects
as parameters or as return values, the class must be available in your application and in Harmony's context.</div>


TODO: check if ClassProxy and InstanceProxy are still relevant?

Performing a static method call from Mouf context in your application context
-----------------------------------------------------------------------------

From a Mouf controller, you can call any static method of in the application side using the `ClassProxy` method.

Using it is simple:

```php
// The ClassProxy instance represents a class (fully qualified name passed in parameter)
$proxy = new ClassProxy("Mouf\\Utils\\Cache\\Service\\PurgeCacheService");
// The static method is called on the proxy instance
$proxy->purgeAll();
```

In the example above, we create a **proxy** to the *PurgeCacheService*. When we call the *purgeAll* method,
the *PurgeCacheService::purgeAll* method is called. Please note this method must be **static**.

You don't want to perform a static function call? You would prefer to call a regular method call? Read below!

Performing a method call from Mouf context in one of your application instances
-------------------------------------------------------------------------------

You can also call directly a method of any instance declared in your application. Use the `InstanceProxy` to do this.

Here is a sample:

```php
// The InstanceProxy instance represents an instance
$proxy = new InstanceProxy("myInstanceName");
// You can call any method on this instance
$result = $proxy->myMethod($myParam);
```

<div class="alert alert-info">Behind the scene, the InstanceProxy and the ClassProxy classes are performing CURL
calls. This means that all the parameters you pass to the functions are serialized, and that the return value
is also serialized. You can therefore pass primitive types easily (strings, arrays...) If you want to pass objects
as parameters or as return values, the class must be available in your application and in Mouf's context.</div>