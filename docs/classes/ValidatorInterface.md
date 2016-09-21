## ValidatorInterface

The `ValidatorInterface` is designed to allow custom validation of the session to help protect against session attacks.

### Check if the session is valid

The `validate` method is used to check if the session is valid. If the session is invalid it should throw a
`InvalidSessionException`. If the `$restart` flag is set to `true` then any data stored in the session should be
invalidated (normally by setting it to `null`) and new properties retrieved.

```php
/*
 * @return  void
 */
public function validate($restart = false);
```

