## Joomla\Session\Storage\NativeStorage

The `NativeStorage` class is an implementation of [`Joomla\Session\StorageInterface`](../StorageInterface.md) that interfaces with PHP's [session extension](https://www.php.net/manual/en/book.session.php).

Use of this class requires a [`SessionHandlerInterface`](https://secure.php.net/manual/en/class.sessionhandlerinterface.php) for interacting with a backend storage mechanism.

### Session options

The constructor's `$options` array and `setOptions()` set PHP's `session.*` ini settings; the keys omit the `session.` prefix:

```php
use Joomla\Session\Handler\FilesystemHandler;
use Joomla\Session\Storage\NativeStorage;

$storage = new NativeStorage(new FilesystemHandler(), [
    'use_strict_mode' => 1,
    'cookie_httponly' => 1,
    'cookie_samesite' => 'Lax',
]);
```

Supported keys: `cache_limiter`, `cache_expire`, `cookie_domain`, `cookie_httponly`, `cookie_lifetime`, `cookie_path`, `cookie_samesite`, `cookie_secure`, `gc_divisor`, `gc_maxlifetime`, `gc_probability`, `lazy_write`, `name`, `serialize_handler`, `use_strict_mode`, `use_cookies`, `use_only_cookies`, `use_trans_sid`, `upload_progress.enabled`, `upload_progress.cleanup`, `upload_progress.prefix`, `upload_progress.name`, `upload_progress.freq`, `upload_progress.min-freq`.

An unknown key throws an `InvalidArgumentException`, as do `use_only_cookies` set to `0` and `use_trans_sid` set to `1`: session IDs in URLs are deprecated as of PHP 8.4. A value PHP rejects, and any option passed after headers have been sent or while the session is active, are ignored with an `E_USER_NOTICE`.
