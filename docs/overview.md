## Overview

The Session package provides an interface for managing sessions within an application. The `Session` class is the base object
within the package and serves as the primary API for managing a session.

### Creating your session object
The session constructor takes 3 parameters:

```
	public function __construct(StorageInterface $store = null, DispatcherInterface $dispatcher = null, array $options = array())
```

#### Storage Object
The StorageInterface defines an object which represents a data store for session data. For more about this please read the [StorageInterface documentation](https://github.com/joomla-framework/session/blob/2.0-dev/docs/classes/StorageInterface.md).

#### Dispatcher Interface
The Session object triggers events on session start and session restart. You can inject a Joomla Event Dispatcher object to use these events in your application. For more information on the Joomla Event Dispatcher package please read the [Joomla Event Package Documentation](https://github.com/joomla-framework/event)

#### Array of Options
The session will take an array of options. The following keys are recognised:

* ```name````: Will set the name of the session into the Storage class
* ```id````: Will set the ID of the session into the Storage class
* ```expire``` (default 15 minutes) Will be used to set the expiry time for the session
* ```security``` Security contains an array of which the following values are recognised:
  * ```fix_browser``` (enabled by default) Will check if there are any browsers agents located in the ```session.client.browser``` key of your storage engine. The variable in storage will be used to whitelist browsers. If the variable in storage is not set then this check will allow all browsers.
  * ```fix_address``` (disabled by default) Will check if there are any browsers agents located in the ```session.client.address``` key of your storage engine. The variable in storage will be used to whitelist IP's. If the variable in storage is not set then this check will allow all IP's.

### Starting a session

A session can be started by instantiating the `Session` object and triggering the `start()` method.

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();
```

This method is suitable for starting a new session or resuming a session if a session ID has already been assigned and stored
in a session cookie.

If you injected a event dispatcher when creating your session object then a event with the name ```onAfterSessionStart``` will be triggered.

### Closing a session
An existing session can be closed by triggering the `close()` method. This will write all your session data through your storage handler

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();

// DO THINGS WITH SESSION

$session->close();
```

### The Session State
You can view the status of the session at any time by calling the ```Session::getState``` function. This will return one of the following strings:

* inactive
* active
* expired
* destroyed
* closed
* error

```php
use Joomla\Session\Session;

$session = new Session;
$session->getState();

// RETURNS: inactive

$session->start();
$session->getState()

// RETURNS: active
```

There is a further helper function ```Session::isStarted``` this tells you if the session has started or not by returning a boolean.

### Data in the session
The session package contains several methods to help you manage the data in your session.

### Setting Data
You can set data into the session using the ```Session::set()``` function. This method takes two parameters - the name of the variable you want to store and the value of that variable:

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();

$session->set('foo', 'bar');

echo $_SESSION['foo']

// Assuming we are using the Native Storage Handler: RESULT: BAR
```

### Getting Data
You can retrieve data set into the session using the ```Session::get()``` function. This method also takes two parameters - the name of the variable you want to retrieve and the default value of that variable (null by default)

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();

$session->set('foo', 'bar');
echo $session->get('foo);

// RESULT: bar

echo $session->get('unset_variable')

// RESULT: null;

echo $session->get('unset_variable2', 'default_var')

// RESULT: default_var;
```

### Further methods
To retrieve all the data from the session storage you can call ```Session::all()```

To clear all the data in the session storage you can call ```Session::clear()```

To remove a piece of data in the session storage you can call ```Session::remove()``` with a parmeter of the name of the variable you wish to remove. If that variable is set then it's value will be returned. If the variable is not set then null will be returned.

To check if a piece of data is present in the session storage you can call ```Session::has()``` with a parameter of the variable you wish to check. This returns a boolean depending on if the data is set.

You can iterate over the data in session storage by calling ```Session::getIterator()```. This will create [Native PHP Array iterator](http://php.net/manual/en/class.arrayiterator.php) object containing all the data in the session storage object.

