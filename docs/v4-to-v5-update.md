## Updating from v4 to v5

The following changes were made to the Session package between v4 and v5.

### Legacy session events removed

`Session` no longer dispatches the `onAfterSessionStart` and `onAfterSessionRestart` events, deprecated since 2.0. Listen to `SessionEvents::START` and `SessionEvents::RESTART` instead; listeners receive the same `SessionEvent`.

### WincacheHandler removed

`Joomla\Session\Handler\WincacheHandler` has been removed, and `ext-wincache` is no longer suggested. The `wincache` extension is abandoned and has no PHP 8 builds. Use another handler, such as the `FilesystemHandler`, `DatabaseHandler`, `RedisHandler` or `MemcachedHandler`.
