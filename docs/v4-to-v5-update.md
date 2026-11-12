## Updating from v4 to v5

The following changes were made to the Session package between v4 and v5.

### Native parameter and return types

The session API declares native types instead of docblock types, and the handlers no longer use `#[\ReturnTypeWillChange]`. Classes that implement the interfaces or extend the classes must use compatible signatures.

`SessionInterface` and `Session`:

| Method | 5.0 signature |
|---|---|
| `getExpire()` | `getExpire(): int` |
| `getName()` | `getName(): string` |
| `setName()` | `setName(string $name): static` |
| `getId()` | `getId(): string` |
| `setId()` | `setId(string $id): static` |
| `isActive()`, `isNew()`, `isStarted()` | `: bool` |
| `getToken()` | `getToken(bool $forceNew = false): string` |
| `hasToken()` | `hasToken(?string $token, bool $forceExpire = true): bool` |
| `get()` | `get(string $name, mixed $default = null): mixed` |
| `set()` | `set(string $name, mixed $value = null): mixed` |
| `has()` | `has(string $name): bool` |
| `remove()` | `remove(string $name): mixed` |
| `clear()`, `start()`, `close()` | `: void` |
| `destroy()`, `restart()` | `: bool` |
| `fork()` | `fork(): bool` (`Session::fork(bool $destroy = false): bool`) |
| `gc()` | `gc(): int\|false` |

`Session` also types `getState(): string`, `getIterator(): \ArrayIterator` and its protected methods (`setCounter(): bool`, `setExpire(int $expire): static`, `setState(string $state): static`, `setTimers(): bool`, `setOptions(array $options): bool`, `validate(bool $restart = false): bool`).

`StorageInterface`, `NativeStorage` and `RuntimeStorage`: `setName()` and `setId()` return `static`, `get()`, `set()` and `remove()` take and return `mixed`, and `gc()` returns `int|false`.

The `ApcuHandler`, `DatabaseHandler`, `MemcachedHandler` and `RedisHandler` use the signatures of `SessionHandlerInterface`: `open(string, string): bool`, `close(): bool`, `read(string): string|false`, `write(string, string): bool` and `gc(int): int|false`. Their `gc()` now returns `0` instead of `true`: the APCu, Memcached and Redis handlers leave expiry to the storage, and the `DatabaseHandler` deletes expired sessions when the session is closed.
