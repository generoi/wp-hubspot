(function ($) {
  var $document = $(document);

  var _script_cache = [];
  var hubspot_cta_script = 'https://js.hscta.net/cta/current.js';
  var hubspot_form_script = 'https://js.hsforms.net/forms/v2.js';

  function getCachedScript(url, options) {
    options = $.extend(options || {}, {
      dataType: 'script',
      cache: true,
      url: url
    });
    return $.ajax(options);
  }

  function getScript(url, cb) {
    if (_script_cache.indexOf(url) !== -1) {
      return cb();
    }
    return getCachedScript(url)
      .done(function() {
        _script_cache.push(url);
        cb();
      })
      .fail(function () {
        console.log('Script loading failed: ' + url);
      });
  }

  var WP_Hubspot = window.WP_Hubspot = window.WP_Hubspot || {};
  WP_Hubspot.form = WP_Hubspot.form || {};
  WP_Hubspot.cta = WP_Hubspot.cta || {};

  WP_Hubspot.init = function () {
    var $ctas = $('.wp-hubspot--cta');
    var $forms = $('.wp-hubspot--form');

    if ($ctas.length) {
      getScript(hubspot_cta_script, function () {
        $ctas.each(WP_Hubspot.cta.init);
      });
    }
    if ($forms.length) {
      getScript(hubspot_form_script, function () {
        $forms.each(WP_Hubspot.form.init);
      });
    }
  };

  WP_Hubspot.cta.defaultOptions = {
    'onCTAReady': function () {
      $document.trigger('wp-hubspot:onCTAReady', $.makeArray(arguments));

      $('.wp-hubspot--cta').each(function () {
        var $this = $(this);
        var params = $this.data('hubspotParams');
        // Only way to remove styling is by dropping the ID attribute.
        if (params.hasOwnProperty('css')) {
          $this.find('a').prop('id', '');
        }
        $this.show();
      });
    }
  };
  WP_Hubspot.cta.init = function() {
    var $this = $(this);
    var portalId = $this.data('hubspotPortalId');
    var ctaId = $this.data('hubspotCtaId');
    var params = $this.data('hubspotParams') || {};

    if (!$this.hasClass('wp-hubspot-init')) {
      var options = $.extend({}, WP_Hubspot.cta.defaultOptions, params)
      window.hbspt.cta.load(portalId, ctaId, options);
      $this.addClass('wp-hubspot-init');
    }
  };

  WP_Hubspot.form.defaultOptions = {
    'locale': WP_Hubspot_Options.locale,
    'onBeforeFormInit': function () {
      $document.trigger('wp-hubspot:onBeforeFormInit', $.makeArray(arguments));
    },
    'onFormReady': function () {
      $document.trigger('wp-hubspot:onFormReady', $.makeArray(arguments));
    },
    'onFormSubmit': function () {
      $document.trigger('wp-hubspot:onFormSubmit', $.makeArray(arguments));
    },
  };

  WP_Hubspot.form.init = function() {
    var $this = $(this);
    var portalId = $this.data('hubspotPortalId');
    var formId = $this.data('hubspotFormId');
    var params = $this.data('hubspotParams') || {};

    if (!$this.hasClass('wp-hubspot-init')) {
      var options = {
        'portalId': portalId,
        'formId': formId,
        'target': '#' + $this.prop('id'),
      };
      options = $.extend(options, WP_Hubspot.form.defaultOptions, params);
      window.hbspt.forms.create(options);
      $this.addClass('wp-hubspot-init');
    }
  }

  /**
   * Initalize.
   */
  WP_Hubspot.init();
}(jQuery));
