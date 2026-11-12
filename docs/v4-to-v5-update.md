## Updating from v4 to v5

The following changes were made to the Session package between v4 and v5.

### NativeStorage applies secure session defaults

In 4.x, `NativeStorage` only defaulted `use_cookies` to `1` and `use_trans_sid` to `0`; everything else came from `php.ini`. In 5.0 the constructor also applies these defaults unless you pass the option yourself:

| Option | Default |
|---|---|
| `use_only_cookies` | `1` |
| `use_strict_mode` | `1` |
| `cookie_httponly` | `1` |
| `cookie_samesite` | `Lax` |
| `cookie_secure` | `1`, only for requests made over HTTPS (`$_SERVER['HTTPS']` set and not `off`) |

What to check:

* With `use_strict_mode`, PHP no longer adopts an unknown session ID supplied by the client, or one set with `setId()` that does not exist in the storage, if the save handler implements `validateId()`. Pass `'use_strict_mode' => 0` if you rely on custom IDs.
* Scripts can no longer read the session cookie. Pass `'cookie_httponly' => 0` if your JavaScript needs it.
* With `cookie_samesite = Lax`, the session cookie is not sent with cross-site subrequests (e.g. POST forms submitted from another site, embedded frames). Pass `'cookie_samesite' => 'None'` (together with `cookie_secure`) for cross-site use.
* The defaults override the corresponding `php.ini` settings. Pass the option explicitly to keep a different value.
* Behind a TLS-terminating proxy, `$_SERVER['HTTPS']` is usually not set; pass `'cookie_secure' => 1` yourself.
