Installing Harmony
==================

Requirements:
-------------

To run Harmony, you will need PHP 5.4+.

Your *memory_limit* settings in *php.ini* must be set at least to 256M.

<div class="alert alert-info"><strong>Note:</strong> Although Harmony only requires PHP 5.4+, it is
a good idea to start with the highest possible PHP version.</div>

Download Harmony:
-----------------

Harmony comes as a Composer package (the name of the package is `harmony/harmony`)

### Downloading Composer
Not used to Composer? Composer is a package manager for PHP. You will need it to install Harmony, any Harmony module,
or any recent PHP library.

#### Install Composer on Linux and Mac OS X

This is essentially a two lines process:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### Install Composer on Windows
Windows users can download and run the [Composer Windows installer](https://getcomposer.org/download)

### Using Composer to download Mouf
At this point, you should have a Composer installed.

In your `composer.json`, add the following section: 

**composer.json**
```
{
    "require-dev": {
        "harmony/harmony": "~1.0"
    }
}
```

<div class="alert alert-info"><strong>Heads up!</strong> You will notice the *harmony/harmony* dependency
is added in the *require-dev* section. This is because Harmony is essentially a development tool and is not
needed for your application to run on a production server.</div> 

Finally, from the command line, at the root of your project, run:

```
composer update
```

Install Harmony modules
-----------------------

Out of the box, Harmony is a nice UI interface to help you with your PHP projects. However, it does not bring
many features. In order to be completely useful, it should be able to analyze your project, and even better,
it should be able to detect DI containers (or service locators, ...) and inspect them.

For this to be possible, Harmony relies on modules. You should check that the framework you are using provides
modules for your application.

TODO: link to Mouf and Symfony modules.

If you don't find a module for your framework or if you are using a in-house framework, do not despair. It is
quite easy to write a module for your framework. TODO: link to harmony-module-interface README.


Running Harmony
---------------
Now, you need to start Harmony.

```bash
vendor/bin/harmony
```

This will start the Harmony server on port 8000.
Once the Harmony server is started, open your web browser and browse to: http://localhost:8000.

Please note that depending on your **composer.json** file, the path to Harmony might be slightly different.
For instance, Symfony 2 users will find Harmony in `bin/harmony`.

The `harmony` command comes with a number of options. You can view those using:

```bash
vendor/bin/harmony --help
```


Setup Harmony:
--------------
Once Harmony is started, you still have to do the setup.

You will see the install screen:

<img src="images/user_registration.png" alt="" />

Choose a login and a password to log into Harmony. Then, click the "Install" button. You are done! If the install completed successfully, you should see the Mouf main page:

<img src="images/status_install.png" alt="" />
