# Updating from v3 to v4

Release 4.0.0 raises the PHP requirement. No method signature changed; two internal corrections may
matter to subclasses.

## At a glance

| | v3 (3.0.2) | v4 (4.0.0) |
|---|---|---|
| PHP | `^8.1.0` | `^8.3.0` |
| Public API | — | unchanged |
| `NativeStorage::$closed` | present | **removed** |
| `DatabaseHandler::$gcLifetime` default | `null` | `0` |

## Minimum supported PHP version raised

All Framework packages now require **PHP 8.3** or newer.

## `NativeStorage::$closed` was removed

The private flag tracked whether the session had been closed, in parallel with `$active`, and was
never read for anything the class did not already know from `session_status()`. It is gone, and
`isActive()` now assigns and returns in two steps rather than one.

Because it was `private`, this affects nothing outside the class — unless you copied the class or
reached into it through reflection in a test.

## `DatabaseHandler::$gcLifetime` defaults to `0`

Previously initialised to `null`, which reached the query as a bound parameter and produced a
comparison against `NULL`. It is now `0`, so an unconfigured lifetime deletes nothing rather than
behaving unpredictably. `gc()` sets the real value before running, so normal use is unaffected.

## No signature changes

Everything else is identical to 3.0.2.

## Dependency changes

| Package | v3 (3.0.2) | v4 (4.0.0) |
|---|---|---|
| `php` | `^8.1.0` | `^8.3.0` |
| `joomla/event` | `^3.0` | `^4.0` |

The optional packages moved to their 4.x releases: `joomla/database`, `joomla/console`.

## Worth doing while you are here

The package applies no secure defaults of its own — `use_strict_mode`, `cookie_httponly` and
`cookie_secure` all fall back to `php.ini`. If you have not set them explicitly, this is a good
moment. See [the overview](overview.md#hardening-the-session).
