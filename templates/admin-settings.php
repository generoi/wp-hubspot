<?php

if (!current_user_can('manage_options')) {
    wp_die( __( 'You do not have sufficient permissions to manage options for this site.' ) );
}
?>

<div class="wrap">
  <h1><?php _e('WP Hubspot', 'wp-hubspot'); ?></h1>

  <p><?php _e('Please note that you have to configure this plugin first before you can start embedding tools into your content.', 'wp-hubspot'); ?></p>

  <form action="options.php" method="post">
    <?php settings_fields('wp_hubspot'); ?>

    <div id="wp-hubspot-forms-settings-tab-contents">
      <div class="content">
        <table class="form-table">
          <tr>
            <th scope="row"><label for="wp_hubspot_apikey"><?php _e('Hubspot Forms API Key', 'wp-hubspot') ?></label></th>
            <td>
              <input name="wp_hubspot_apikey" type="text" id="wp_hubspot_apikey" value="<?php form_option('wp_hubspot_apikey'); ?>" placeholder="demo" class="regular-text" />
              <p class="description"><?php _e('Please use <strong>demo</strong> to load example forms. Generate your own <a href="https://app.hubspot.com/keys/get" target="_blank">new key</a>.', 'wp-hubspot'); ?></p>
            </td>
          </tr>
        </table>
      </div>
    </div>

    <?php do_settings_sections('wp_hubspot'); ?>
    <?php submit_button(); ?>
  </form>
</div>

