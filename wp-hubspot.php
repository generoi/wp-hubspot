<?php
/*
Plugin Name:        WP Hubspot
Plugin URI:         http://genero.fi
Description:        Some Hubspot integrations for WP.
Version:            0.0.4
Author:             Genero
Author URI:         http://genero.fi/

License:            MIT License
License URI:        http://opensource.org/licenses/MIT
*/

if (!defined('ABSPATH')) {
  exit;
}

class WP_Hubspot
{
    private static $instance = null;
    public $version = '1.0.0';
    public $plugin_name = 'wp-hubspot';
    public $github_url = 'https://github.com/generoi/wp-hubspot';
    protected $hubspot_api_limit = 100;
    public $forms;
    public $ctas;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);
        register_setting('wp_hubspot', 'wp_hubspot_apikey');
        Puc_v4_Factory::buildUpdateChecker($this->github_url, __FILE__, $this->plugin_name);
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init()
    {
        add_shortcode('hubspot', [$this, 'shortcode_output']);
        add_action('admin_menu', [$this, 'admin_menu']);

        add_action('media_buttons', [$this, 'media_buttons']);
        add_action('wp_ajax_wp_hubspot_embed', [$this, 'embed_popup_content']);

        add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'register_scripts']);
        add_action('tailor_enqueue_sidebar_scripts', [$this, 'register_scripts']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('tailor_enqueue_sidebar_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('tailor_canvas_enqueue_scripts', [$this, 'enqueue_tailor_canvas_scripts']);
    }

    /**
     * Register all plugin scripts and styles.
     */
    public function register_scripts()
    {
        $path = plugin_dir_url(__FILE__);
        wp_register_style('wp-hubspot/admin/css', $path . 'css/wp-hubspot.admin.css', [], $this->version);
        wp_register_script('wp-hubspot/admin/js', $path . 'js/wp-hubspot.admin.js', ['jquery'], $this->version);
        wp_localize_script('wp-hubspot/admin/js', 'WP_Hubspot_l10n', [
            'none_selected' => __('Please choose a Hubspot objet to insert', 'wp-hubspot'),
        ]);
        wp_register_script('wp-hubspot/frontend/js', $path . 'js/wp-hubspot.frontend.js', ['jquery'], $this->version, true);
        wp_localize_script('wp-hubspot/frontend/js', 'WP_Hubspot_Options', [
            'locale' => $this->get_locale(),
        ]);
        wp_register_script('wp-hubspot/tailor/js', $path . 'js/wp-hubspot.tailor.js', ['wp-hubspot/frontend/js', 'tailor-canvas'], $this->version, true);
    }

    /**
     * Enqueue admin scripts.
     */
    public function enqueue_admin_scripts()
    {
        wp_enqueue_style('wp-hubspot/admin/css');
        wp_enqueue_script('wp-hubspot/admin/js');
    }

    /**
     * Enqueue frontend scripts.
     */
    public function enqueue_frontend_scripts()
    {
        wp_enqueue_script('wp-hubspot/frontend/js');
    }

    /**
     * Tailor integration.
     */
    public function enqueue_tailor_canvas_scripts() {
        wp_enqueue_script('wp-hubspot/tailor/js');
    }

    /**
     * Return shortode output.
     */
    public function shortcode_output($atts)
    {
        $defaults = [
            'type' => '',
            'portal_id' => '',
            'id' => '',
            'name' => '',
        ];
        $params = array_diff_key($atts, $defaults);
        $atts = shortcode_atts($defaults, $atts);

        switch ($atts['type']) {
            case 'form':
                return $this->get_form_embed_code($atts['portal_id'], $atts['id'], $atts['name'], $params);
            case 'cta':
                return $this->get_cta_embed_code($atts['portal_id'], $atts['id'], $atts['name'], $params);
            default:
                return __('Unknown hubspot shortcode type.', 'wp-hubspot');
        }
    }

    /**
     * Get embed Form HTML code.
     */
    public function get_form_embed_code($portal_id, $form_id, $name, $params = [])
    {
        $params_attr = json_encode($params);
        $wrapper = '<div class="wp-hubspot--form"'
            . ' id="wp-hubspot-form--' . $form_id . '"'
            . ' data-hubspot-portal-id="' . $portal_id . '"'
            . ' data-hubspot-form-id="' . $form_id . '"'
            . ' data-hubspot-name="' . esc_attr($name) . '"'
            . ' data-hubspot-params=\'' . $params_attr . '\'>%s</div>';
        $content = '';

        $wrapper = apply_filters('wp-hubspot/embed/form/wrapper', $wrapper, $portal_id, $form_id, $params);
        $content = apply_filters('wp-hubspot/embed/form/content', $content, $portal_id, $form_id, $params);
        return sprintf($wrapper, $content);
    }

    /**
     * Get embed CTA HTML code.
     */
    public function get_cta_embed_code($portal_id, $cta_id, $name, $params = [])
    {
        $params_attr = json_encode($params);
        $wrapper = '<span class="wp-hubspot--cta hs-cta-wrapper"'
            . ' style="display: none;"' // hide initally.
            . ' id="hs-cta-wrapper-' . $cta_id . '"'
            . ' data-hubspot-portal-id="' . $portal_id . '"'
            . ' data-hubspot-cta-id="' . $cta_id . '"'
            . ' data-hubspot-name="' . esc_attr($name) . '"'
            . ' data-hubspot-params=\'' . $params_attr . '\'>%s</span>';
        $content = '
            <span class="hs-cta-node hs-cta-' . $cta_id . '" id="hs-cta-' . $cta_id . '">
                <a href="https://cta-redirect.hubspot.com/cta/redirect/' . $portal_id . '/' . $cta_id . '" >
                    <img class="hs-cta-img" id="hs-cta-img-' . $cta_id . '" style="border-width:0px;" src="https://no-cache.hubspot.com/cta/default/' . $portal_id . '/' . $cta_id . '.png" />
                </a>
            </span>
        ';
        $wrapper = apply_filters('wp-hubspot/embed/cta/wrapper', $wrapper, $portal_id, $cta_id, $params);
        $content = apply_filters('wp-hubspot/embed/cta/content', $content, $portal_id, $cta_id, $params);
        return sprintf($wrapper, $content);
    }

    /**
     * Print embed popup content.
     */
    public function embed_popup_content()
    {
        include __DIR__ . '/templates/popup.php';
        die();
    }

    /**
     * Retrieve a property from a specific CTA object.
     */
    public function get_hubspot_cta_property($cta_id, $property, $reset = false) {
        $ctas = $this->get_hubspot_ctas($reset);
        $ctas = wp_list_filter($forms, ['placement_guid' => $cta_id]);
        if (!empty($ctas)) {
            $cta = reset($ctas);
            return isset($cta->$property) ? $cta->$property : null;
        }
        return null;
    }

    /**
     * Retrieve a property from a specific Form object.
     */
    public function get_hubspot_form_property($form_id, $property, $reset = false) {
        $forms = $this->get_hubspot_forms($reset);
        $forms = wp_list_filter($forms, ['guid' => $form_id]);
        if (!empty($forms)) {
            $form = reset($forms);
            return isset($form->$property) ? $form->$property : null;
        }
        return null;
    }

    /**
     * Get the Form objects from Hubspot API.
     */
    public function get_hubspot_forms($reset = false)
    {
        $forms = get_transient(__FUNCTION__);
        if ($forms === false || $reset) {
            $forms = $this->fetch_hubspot_api('https://api.hubapi.com/forms/v2/forms');
            set_transient(__FUNCTION__, $forms, HOUR_IN_SECONDS);
        }
        return $forms;
    }

    /**
     * Get the CTA objects from Hubspot API.
     */
    public function get_hubspot_ctas($reset = false)
    {
        $ctas = get_transient(__FUNCTION__);
        if ($ctas === false || $reset) {
            $ctas = $this->fetch_hubspot_api('https://api.hubapi.com/ctas/v2/ctas');
            set_transient(__FUNCTION__, $ctas, HOUR_IN_SECONDS);
        }
        return $ctas;
    }

    /**
     * Fetch API result from Hubspot endpoint.
     */
    protected function fetch_hubspot_api($endpoint, $args = [])
    {
        $hapikey = get_option('wp_hubspot_apikey');
        $objects = [];
        $offset = 0;
        do {
            $has_more = false;
            $query = array_merge([
                'hapikey' => $hapikey,
                'limit' => $this->hubspot_api_limit,
                'offset' => $offset,
            ], $args);
            $url = add_query_arg($query, $endpoint);

            $response = wp_remote_get($url);
            $response = json_decode($response['body']);
            if (is_array($response)) {
                $objects = $response;
            }
            elseif (isset($response->objects)) {
                $has_more = $response->meta->total_count > ($response->meta->offset + $response->meta->limit);
                if ($has_more) {
                    $offset = $offset + $response->meta->limit;
                }
                $objects = array_merge($objects, $response->objects);
            }
        } while ($has_more);

        return $objects;
    }

    /**
     * Add a button which opens the Embed Hubspot tool popup.
     */
    public function media_buttons()
    {
        add_thickbox();

        $button = __('Embed Hubspot tool', 'wp-hubspot');
        $url = add_query_arg([
            'action' => 'wp_hubspot_embed',
            'width' => 600,
            'height' => 350,
        ], admin_url('admin-ajax.php'));

        echo sprintf(
            '<a href="%s" title="%s" class="button thickbox">%s</a>',
            $url, $button, $button
        );
    }

    /**
     * Get the site locale usable with Hubspot form localization.
     */
    public function get_locale() {
        list($locale, ) = explode('_', get_locale());
        return $locale;
    }

    /**
     * Add admin menu item.
     */
    public function admin_menu()
    {
        $settings_page = add_options_page(
            __('WP Hubspot Settings', 'wp-hubspot'),
            __('WP Hubspot Settings', 'wp-hubspot'),
            'manage_options',
            'wp_hubspot',
            [$this, 'admin_settings_page']
        );
    }

    /**
     * Admin Settings page.
     */
    public function admin_settings_page()
    {
        require_once __DIR__ . '/templates/admin-settings.php';
    }

    public static function activate()
    {
    }

    public static function deactivate()
    {
        delete_option('wp_hubspot_apikey');
    }
}

if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    require_once $composer;
}

WP_Hubspot::get_instance();
