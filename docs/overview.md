## Overview

The Session package provides an interface for managing sessions within an application. The `Session` class is the base object
within the package and serves as the primary API for managing a session.

### Starting a session

A session can be started by instantiating the `Session` object and triggering the `start()` method.

```php
use Joomla\Session\Session;

$session = new Session;
$session->start();
```

This method is suitable for starting a new session or resuming a session if a session ID has already been assigned and stored
in a session cookie.
