Writing an Harmony validator
============================

What are Harmony validators?
----------------------------

When you connect to Harmony, the welcome page displays the status of your application.

<img src="images/status-screen.png" alt="" />

Each little box validating a part of your application is an "Harmony validator".
To make things simple, a validator is a piece of software that helps the developer to check
for errors in his code or his environment. The nice thing with validators is they are *very* easy to write.

As a package developer, you can provide validators to help diagnose with common problems.
As an application developer, you can write validators to help check for common errors in your application.

But isn't this the purpose of unit tests?
-----------------------------------------

No. Validators and unit tests are different beasts. A validator can be used to check your environment or code
for misconfiguration.

Here are a few samples:

- A validator could check that the memory limit of your environment is at least 512M.
- A validator could check that some extension is available on the host (for instance curl).
- A validator could check that the connection to the database is possible
- A validator could check that a service is correctly configured in the DI container
- ...

Running validators from the command line
----------------------------------------

You can run the validators from the command-line.

From your project root, simply type:

```bash
vendor/bin/harmony-console status
```

Only warning and error messages are displayed. If you want to see success messages too, run the command in verbose mode:

```bash
vendor/bin/harmony-console status -v
```


Things you should know about validators
---------------------------------------

There are 2 kinds of validators:

- Class validators (the validator is triggered by a class) 
- Instance validators (the validator is triggered for each instance of a class declared in the container)

*Note*: to work with instance validators, Harmony needs to be able to access your application's container.
 This is usually done by installing an Harmony module for your framework.

Developing a class validator
----------------------------

In order to add a validator to a class, your class just needs to implement the <code>StaticValidatorInterface</code> interface.

Here is a sample (adapted from the Splash MVC package), that validates there is a `.htaccess` file in the root directory:

```php
use Harmony\Validator\StaticValidatorInterface;
use Harmony\Validator\ValidatorResult;

class HtaccessValidator implements StaticValidatorInterface {
	
	/**
	 * Runs the validation of the class.
	 * Returns a ValidatorResult explaining the result.
	 *
	 * @return ValidatorResultInterface|ValidatorResultInterface[]
	 */
	public static function validateClass() {
		if (!file_exists(__DIR__."/../../../../.htaccess")) {
			return new ValidatorResult(ValidatorResult::WARN, "Unable to find .htaccess file.");
		} else {
			return new ValidatorResult(ValidatorResult::SUCCESS, ".htaccess file found.");
		}
	}

}
```

As you can see, you have a single method to implement: <code>validateClass</code>. This method must be **static**.
<code>validateClass</code> must return a <code>ValidatorResult</code> or an array of <code>ValidatorResult</code>.

The first parameter of the <code>ValidatorResult</code> constructor is the result type.
It can be one amongst:

- ValidatorResult::SUCCESS
- ValidatorResult::WARN
- ValidatorResult::ERROR

The second parameter is the text that will be displayed. It can contain HTML.

A third optional parameter is available. It should contain the text without HTML (for the CLI version of the validators).

<div class="alert alert-info"><strong>Note:</strong> When you create a new validator, for your validator to appear 
in the Harmony status page, you might need to purge Harmony's cache. If your validator is part of a Composer package,
you will need to run `composer dumpautoload` for your validator to be detected.</div>

Developing an instance validator
--------------------------------

Developing an instance validator is quite similar to a class validator: your class just needs to implement the <code>ValidatorInterface</code> interface.

Here is a sample where a validator where a sample Controller checks there is a template associated to it.

```php
use Harmony\Validator\ValidatorInterface;
use Harmony\Validator\ValidatorResult;


class MyController implements ValidatorInterface {
	
	private $template;
	
	// ...
	
	/**
	 * Runs the validation of the instance.
	 * Returns a ValidatorResult explaining the result.
	 *
	 * @param string $identifier The identifier of the instance in the container
	 * @return ValidatorResultInterface|ValidatorResultInterface[]
	 */
	public function validateInstance($identifier) {
		if ($this->template == null) {
			return new ValidatorResult(ValidatorResult::ERROR, "You must associate a template to the controller.");
		} else {
			return new ValidatorResult(ValidatorResult::SUCCESS, "Template found in controller.");
		}
	}
}
```

For each instance of the class declared in your application's controller, the validator will run once. This also means that if you do not
declare an instance of this class in your container, the validator will be ignored.

Including validators in your Composer packages
----------------------------------------------

You might want to include Harmony validators in your Composer packages, but without making your package dependant on Harmony.
For this purpose, the Harmony validator interfaces have been isolated in a very small package:
**harmony/validator-interface**.

To use Harmony validators in your package, you just need to add a dependency:

**composer.json**
```json
{
    "require": {
    	"harmony/validator-interface": "~1.0"
    }
}
```
