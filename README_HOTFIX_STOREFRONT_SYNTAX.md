# Hotfix — storefront.php syntax

Fixed the PHP fatal error:

`Cannot use empty array elements in arrays in config/storefront.php`

Cause: an accidental extra comma existed between the `ingredients` and `journal` top-level array entries.

No feature or design changes were removed. This checkpoint preserves the polished final frontend.
