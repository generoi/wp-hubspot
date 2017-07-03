# wp-hubspot

> A wordpress plugin with a few Hubspot integrations.

## Features

- Embed Hubspot Forms in WP Editor
- Embed Hubspot CTA's in WP Editor
- Automatic GA tracking with [wp-genero-analytics](https://github.com/generoi/wp-genero-analytics)
- On demand hubspot script loading

### Filter API

```php
// Call to Action
add_filter('wp-hubspot/embed/cta/wrapper', function ($wrapper, $portal_id, $cta_id, $params) {
  return $wrapper;
}, 10, 4);
add_filter('wp-hubspot/embed/cta/content', function ($content, $portal_id, $cta_id, $params) {
  return $content;
}, 10, 4);

// Forms
add_filter('wp-hubspot/embed/form/wrapper', function ($wrapper, $portal_id, $cta_id, $params) {
  return $wrapper;
}, 10, 4);
add_filter('wp-hubspot/embed/form/content', function ($content, $portal_id, $cta_id, $params) {
  return $content;
}, 10, 4);
```

## Javascript API

```js
// Call to Action
$(document).on('wp-hubspot:onCTAReady', ...);

// Forms
$(document).on('wp-hubspot:onBeforeFormInit', ...);
$(document).on('wp-hubspot:onFormReady', ...);
$(document).on('wp-hubspot:onFormSubmit', ...);

// Re-init if content is loaded through AJAX.
$.ajax(...).done(WP_Hubspot.init);
```
