# Drupal 8 Clean REST module

## Purpose:

Proof of concept code to try to simplify output of the Drupal core REST module.
Especially for single-value fields the default output is bloated with array structures, where it shouldn't be.
This module tries a few things to fix that.

## Example use case:

### Default Drupal 8 REST output:

```
...
"nid": [
  {
    "value": "5"
  }
],
"uuid": [
  {
    "value": "5de0ba09-2113-417c-bf61-979db2c1b40a"
  }
],
...
```

### Output with this module enabled:
```
...
"nid": "5",
"uuid": "5de0ba09-2113-417c-bf61-979db2c1b40a",
...
```

## How it works:

Drupal 8 runs all REST output (json, xml, ...) through the serializer service, which (amongst other things)
normalises the output through Normalizer classes.

This module overrides the default Normalizer service for Content Entities to try to simplify things.
With the default settings of the module, cases of fields on an entity which have only a single value, get simplified. 
If a field has multiple values, it stays as-is.

The module also has a few alternative ways to try to accomplish the purpose. 
Change $attribute_mode in src/Normalizer/ContentEntityNormalizer to test those.

## TODO:

* Test with more complex fields, relations, non-node entities, ...
* Expand on concept where a view mode (or other config) would be used to define which fields will be rendered in the output.
  See https://www.drupal.org/node/2339795 for high level discussion.

