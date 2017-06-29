(function ($) {
  var $document = $(document);

  console.log('hubspot.admin');

  function buildAttributes(attributes) {
    var output = [];
    $.map(attributes, function (value, key) {
      output.push(key + '="' + value + '"');
    });
    return output.join(' ');
  }

  /**
   * Insert shortcode snippet in editor.
   */
  $document.on('click', '#wp_hubspot_embed_insert', function () {
    var $type = $('#wp_hubspot_embed_type');
    var $id = $('#wp_hubspot_embed_id');
    var $name = $('#wp_hubspot_embed_name');

    if ($id.val() === '') {
      alert(WP_Hubspot_l10n.none_selected);
      return;
    }

    var components = $id.val().split('::');
    var options = {
      type: $type.val(),
      name: $name.val(),
      portal_id: components[0],
      id: components[1],
    };

    switch (options.type) {
      case 'form':
        if ($('#wp_hubspot_cta_css').is(':checked')) {
          options.css = '';
        }
        break;
      case 'cta':
        if ($('#wp_hubspot_cta_css').is(':checked')) {
          options.css = '';
        }
        break;
    }

    // Send to editor and close popup
    var shortcode = '[hubspot ' + buildAttributes(options) + ']';
    window.send_to_editor(shortcode);

    // Reset
    $("#TB_closeWindowButton").trigger('click');
    $id.val('');
    $name.val('');
    $type.val('');
  });

  $document.on('click', '#wp_hubspot_embed_cancel', function () {
    $('#TB_closeWindowButton').trigger('click');
  });

  /**
   * Toggle the correct tool dropdown.
   */
  $document.on('change', '#wp_hubspot_type', function () {
    $('.wp_hubspot_type_row').addClass('hidden');

    var selector = '#wp_hubspot_' + this.value + '_wrapper';
    $(selector).removeClass('hidden');
    $('#wp_hubspot_embed_type').val('');
    $('#wp_hubspot_embed_name').val('');
    $('#wp_hubspot_embed_id').val('');
  });

  /**
   * Set the correct hidden fields when an object is selected.
   */
  $document.on('change', '.wp_hubspot_object_id', function () {
    var $this = $(this);
    $('#wp_hubspot_embed_type').val($this.data('type'));
    $('#wp_hubspot_embed_name').val($this.find(':selected').text());
    $('#wp_hubspot_embed_id').val($this.val());
  });

}(jQuery));
