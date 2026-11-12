## Overview

The Session package provides an interface for managing sessions within an application. The [`Joomla\Session\Session`](classes/Session.md) class is the base object within the package and serves as the primary API for managing a session.

### Creating your Session object
The `Session` class constructor takes 1 compulsory and 3 optional parameters:

```php
/**
 * @param   StorageInterface     $store       A StorageInterface implementation
 * @param   DispatcherInterface  $dispatcher  DispatcherInterface for the session to use.
 * @param   array                $options     Optional parameters
 */
public function __construct(StorageInterface $store = null, DispatcherInterface $dispatcher = null, array $options = [])
```

#### Storage Interface
The `Joomla\Session\StorageInterface` defines an object which represents a data store for session data. For more about this please read the [StorageInterface documentation](classes/StorageInterface.md).

#### Validator Interface
The `Joomla\Session\ValidatorInterface` defines an object which represents a validation check for the active session. For more about this please read the [ValidatorInterface documentation](classes/ValidatorInterface.md).

Use of validators is not required by the `Joomla\Session\SessionInterface`, but is supported in `Joomla\Session\Session` through the `addValidator()` method; therefore you should review your implementation to determine if validators are supported.

#### Dispatcher Interface
The `Joomla\Session\Session` class triggers events on session start and session restart. You can inject a `Joomla\Event\DispatcherInterface` implementation to use these events in your application. For more information on the Event Dispatcher package please read the [Joomla Event Package Documentation](https://github.com/joomla-framework/event)

#### Array of Options
The session will take an array of options. The following keys are recognised:

* ```name```: Will set the name of the session into the StorageInterface object
* ```id```: Will set the ID of the session into the StorageInterface object
* ```expire``` (default 900 seconds) Will be used to set the expiry time for the session

### Starting a session

A session can be started by calling the `start()` method.

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();
```

This method is suitable for starting a new session or resuming a session if a session ID has already been assigned and stored in a session cookie.

If you injected an event dispatcher into the `Session` class then a [`Joomla\Session\SessionEvent`](classes/SessionEvent.md) for the `SessionEvents::START` event will be triggered.

Note that the `Joomla\Session\Session` class supports lazily started sessions and this method does NOT need to be explicitly called in your application, the first attempt to read from or write to the session data store will start the session if necessary.

### Closing a session
An existing session can be closed by calling the `close()` method. This will write all your session data through your storage handler.

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();

// DO THINGS WITH SESSION

$session->close();
```

### The Session State
You can view the status of the session at any time by calling the `getState()` method. This will return one of the following strings:

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

There is a further helper function `isStarted()` that tells you if the session has been started.

### Data in the session
The `Joomla\Session\SessionInterface` contains several methods to help you manage the data in your session.

#### Setting Data
You can set data into the session using the `set()` method. This method takes two parameters - the name of the variable you want to store and the value of that variable:

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();

$session->set('foo', 'bar');

echo $_SESSION['foo']

// Assuming we are using the Native Storage Handler: RESULT: BAR
```

#### Getting Data
You can retrieve data set into the session using the `get()` function. This method also takes two parameters - the name of the variable you want to retrieve and the default value of that variable (null by default)

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();

$session->set('foo', 'bar');
echo $session->get('foo');

// RESULT: bar

echo $session->get('unset_variable')

// RESULT: null;

echo $session->get('unset_variable2', 'default_var')

// RESULT: default_var;
```

#### Additional methods
To retrieve all the data from the session storage you can call `all()`

To clear all the data in the session storage you can call `clear()`

To remove a piece of data in the session storage you can call `remove()` with a parameter of the name of the variable you wish to remove. If that variable is set then it's value will be returned. If the variable is not set then null will be returned.

To check if a piece of data is present in the session storage you can call `has()` with a parameter of the variable you wish to check. This returns a boolean depending on if the data is set.

You can iterate over the data in session storage by calling `getIterator()`. This will create an [ArrayIterator](https://secure.php.net/manual/en/class.arrayiterator.php) object containing all the data in the session storage object.

## Hardening the session

The package passes your options to PHP and adds nothing, so the defaults are whatever `php.ini`
says — and PHP's own defaults are not what you want. Set them explicitly:

```php
use Joomla\Session\Handler\FilesystemHandler;
use Joomla\Session\Session;
use Joomla\Session\Storage\NativeStorage;

// Not in NativeStorage's option allowlist, so it has to be set directly.
ini_set('session.cookie_samesite', 'Lax');

$session = new Session(new NativeStorage(new FilesystemHandler(), [
    'name'             => 'app_session',
    'use_strict_mode'  => 1,   // reject client-supplied session ids
    'use_only_cookies' => 1,
    'cookie_httponly'  => 1,
    'cookie_secure'    => 1,   // only when you serve over HTTPS
    'cookie_path'      => '/',
    'gc_maxlifetime'   => 1440,
]));
```

`use_strict_mode` is the important one: without it PHP adopts a session id supplied by the client,
which is exactly what session fixation needs.

Two properties of `NativeStorage::setOptions()` to keep in mind:

* **Unknown keys are discarded silently.** `cookie_samesite` is not in the allowlist, so passing it
  does nothing — set it with `ini_set()` before the session starts, as above. A typo in any other
  key is equally silent.
* **It returns early once the session is active**, again without saying so. Set options before
  starting.

## Regenerate the session id on login

Nothing in the framework does this for you — `joomla/authentication` does not know about sessions:

```php
if ($authentication->authenticate() !== false) {
    $session->fork(true);              // new id, destroy the old session
    $session->set('user', $user);
}
```

Without it, an attacker who can plant a session cookie before login keeps a valid session
afterwards.

## Things to know about the validators

`AddressValidator` binds the session to the client address, and `ForwardedValidator` — despite its
name and the `ValidatorInterface` it implements — **validates nothing**. It only records
`X-Forwarded-For` in the session, and even that fails whenever the header contains more than one
address, because it is checked with `FILTER_VALIDATE_IP` as a whole.

`AddressValidator` reads the address through `Joomla\Utilities\IpHelper::getIp()`. If IP overrides
are enabled there, the client controls that value and the binding is worthless; if they are
disabled, users on rotating addresses lose their session. Decide deliberately which you want.
