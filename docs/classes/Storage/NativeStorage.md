## Joomla\Session\Storage\NativeStorage

The `NativeStorage` class is an implementation of [`Joomla\Session\StorageInterface`](../StorageInterface.md) that interfaces with PHP's [session extension](https://www.php.net/manual/en/book.session.php).

The class uses a [`SessionHandlerInterface`](https://secure.php.net/manual/en/class.sessionhandlerinterface.php) for interacting with a backend storage mechanism. If no handler is given (`new NativeStorage()`), the class does not register one and PHP's configured save handler (`session.save_handler`) is used.
