## Joomla\Session\Handler\FilesystemHandler

The `FilesystemHandler` stores sessions as files through PHP's `files` save handler.

```php
use Joomla\Session\Handler\FilesystemHandler;

// Use the path configured in session.save_path
$handler = new FilesystemHandler();

// Use a custom directory, optionally in the "N;/path" or "N;MODE;/path" format of session.save_path
$handler = new FilesystemHandler('/var/lib/myapp/sessions');
```

Without a path, the handler uses `session.save_path` and throws an `InvalidArgumentException` if that is empty as well.

With a custom path, a missing directory is created, including missing parent directories, with mode `0700`. The session file names contain the session IDs, so the directory must not be listable by other users. The `MODE` part of `N;MODE;/path` applies to the session files PHP creates, not to the directory. A directory that cannot be created throws a `RuntimeException`; if the path cannot be applied to `session.save_path` (for example because a session is already active), an `E_USER_WARNING` is raised.
