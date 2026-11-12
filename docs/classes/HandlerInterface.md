## Joomla\Session\HandlerInterface

The `HandlerInterface` is an extension of the native [`SessionHandlerInterface`](https://secure.php.net/manual/en/class.sessionhandlerinterface.php) adding additional capabilities to session handlers.

Implementations of the `SessionHandlerInterface` are used by a `Joomla\Session\Storage\NativeStorage` instance for managing session data. Note that the Session API does not require session handlers to implement the `Joomla\Session\HandlerInterface`, all uses typehint against the `SessionHandlerInterface` and check whether the extended interface is used.

### Check if the handler is supported

The `isSupported` method is used to validate whether a handler can be used in the current environment. Handlers implementing this interface should add internal checks to ensure its dependencies are available. For example, the `Joomla\Session\Handler\MemcachedHandler::isSupported()` method ensures that Memcached is available on the server.

```php
/*
 * @return  boolean
 */
public static function isSupported();
```

### Session ID creation and validation

PHP 8.6 deprecates passing a handler without `create_sid()` and `validateId()` to `session_set_save_handler()`, and PHP 9 will require them. The `ApcuHandler`, `DatabaseHandler`, `MemcachedHandler` and `RedisHandler` therefore also implement [`SessionIdInterface`](https://www.php.net/manual/en/class.sessionidinterface.php) and [`SessionUpdateTimestampHandlerInterface`](https://www.php.net/manual/en/class.sessionupdatetimestamphandlerinterface.php). The `FilesystemHandler` and `WincacheHandler` extend PHP's `SessionHandler`, which is not affected.

* `create_sid()` creates a random ID that honours `session.sid_length` and `session.sid_bits_per_character`.
* `validateId()` checks whether a session with that ID exists in the storage. With `session.use_strict_mode` enabled, PHP uses it to reject IDs that the client made up.
* `updateTimestamp()` is called instead of `write()` when `session.lazy_write` is enabled and the data did not change; the bundled handlers write the data again to refresh its lifetime.

Custom handlers can use the `Joomla\Session\Handler\SessionIdTrait`, which implements `create_sid()` and `updateTimestamp()` (the latter by calling `write()`), and only add `validateId()`:

```php
use Joomla\Session\Handler\SessionIdTrait;
use Joomla\Session\HandlerInterface;

class MyHandler implements HandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    use SessionIdTrait;

    public function validateId(string $id): bool
    {
        // Return whether a session with this ID exists in your storage
    }

    // open(), close(), read(), write(), destroy(), gc() and isSupported() as before
}
```
