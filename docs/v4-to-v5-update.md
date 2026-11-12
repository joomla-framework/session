## Updating from v4 to v5

The following changes were made to the Session package between v4 and v5.

### NativeStorage rejects unsupported session options

`NativeStorage::setOptions()` (and the constructor's `$options`) throws an `InvalidArgumentException` for keys it does not support, instead of ignoring them with an `E_USER_NOTICE` as in 4.x.

The settings deprecated in PHP 8.4 are no longer accepted: `sid_length`, `sid_bits_per_character`, `trans_sid_tags`, `trans_sid_hosts` and `referer_check`, as well as `use_only_cookies` set to `0` and `use_trans_sid` set to `1`. 4.x triggers a deprecation for them. Remove them from your options.
