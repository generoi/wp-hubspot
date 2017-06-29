<?php

if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to manage options for this site.', 'wp-hubspot'));
}

$wp_hubspot = WP_Hubspot::get_instance();
$reset = true;
?>
<div id="wp-hubspot-popup">
  <table class="form-table">
    <tr>
      <th scope="row"><label for="wp_hubspot_type"><?php _e('HubSpot tool', 'wp-hubspot'); ?></label></th>
      <td>
        <select id="wp_hubspot_type" name="wp_hubspot_type" class="regular-text">
          <option value=""><?php _e('Choose which HubSpot tool to embed', 'wp-hubspot'); ?></option>
          <option value="cta"><?php _e('Call to Action', 'wp-hubspot'); ?></option>
          <option value="form"><?php _e('Form', 'wp-hubspot'); ?></option>
        </select>
      </td>
    </tr>
    <tr id="wp_hubspot_cta_wrapper" class="hidden wp_hubspot_type_row">
      <th></th>
      <td>
        <p>
          <select id="wp_hubspot_cta" name="wp_hubspot_cta" class="wp_hubspot_object_id regular-text" data-type="cta">
            <option value=""><?php _e('Choose a CTA to embed', 'wp-hubspot'); ?></option>
            <?php foreach ($wp_hubspot->get_hubspot_ctas($reset) as $cta) : ?>
              <option value="<?php echo $cta->portal_id . '::' . $cta->placement_guid ?>"><?php echo $cta->name; ?></option>
            <?php endforeach; ?>
          </select>
        </p>
        <p>
          <input type="checkbox" id="wp_hubspot_cta_css" name="wp_hubspot_cta_css" value="1" checked>
          <label for="wp_hubspot_cta_css"><?php _e('Remove HubSpot default styling', 'wp-hubspot') ?></label>
        </p>
      </td>
    </tr>
    <tr id="wp_hubspot_form_wrapper" class="hidden wp_hubspot_type_row">
      <th></th>
      <td>
        <p>
          <select id="wp_hubspot_form" name="wp_hubspot_form" class="wp_hubspot_object_id regular-text" data-type="form">
            <option value=""><?php _e('Choose a Form to embed', 'wp-hubspot'); ?></option>
            <?php foreach ($wp_hubspot->get_hubspot_forms($reset) as $form) : ?>
              <option value="<?php echo $form->portalId . '::' . $form->guid ?>"><?php echo $form->name; ?></option>
            <?php endforeach; ?>
          </select>
        </p>
        <p>
          <input type="checkbox" id="wp_hubspot_form_css" name="wp_hubspot_form_css" value="1" checked>
          <label for="wp_hubspot_form_css"><?php _e('Remove HubSpot default styling', 'wp-hubspot') ?></label>
        </p>
      </td>
    </tr>
    <tr>
      <th></th>
      <td>
        <input type="hidden" name="hubspot_type" id="wp_hubspot_embed_type">
        <input type="hidden" name="hubspot_name" id="wp_hubspot_embed_name">
        <input type="hidden" name="hubspot_id" id="wp_hubspot_embed_id">
        <input type="button" name="insert" id="wp_hubspot_embed_insert" class="button button-primary" value="<?php _e('Insert', 'wp-hubspot'); ?>">
        <input type="button" name="insert" id="wp_hubspot_embed_cancel" class="button" value="<?php _e('Cancel', 'wp-hubspot'); ?>">
      </td>
    </tr>
  </table>
</div>
