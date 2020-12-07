<?php
/*
Plugin Name: LFI Inscription plateforme
Description: Gère l'inscription sur la plateforme
Version: 1.0
Author: Jill Maud Royer
License: GPL3
*/

namespace LFI\WPPlugins\AgirRegistration;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Plugin
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('init', [$this, 'admin_init']);
        add_action('elementor_pro/init', [$this, 'register_elementor_addons']);
        add_action('wp_enqueue_scripts', [$this, 'cookie_script']);
        add_shortcode('agir_signatures', [$this, 'signature_shortcode_handler']);
    }

    public function admin_init()
    {
        require_once dirname(__FILE__).'/includes/admin.php';

        new Admin();
    }

    public function register_elementor_addons()
    {
        require_once dirname(__FILE__).'/includes/registration-handler.php';
        require_once dirname(__FILE__).'/includes/update-newsletters-handler.php';
        require_once dirname(__FILE__).'/includes/check_person_handler.php';

        $elementor_registration_action = new RegistrationAction();
        \ElementorPro\Plugin::instance()
            ->modules_manager->get_modules('forms')
            ->add_form_action($elementor_registration_action->get_name(), $elementor_registration_action)
        ;

        $elementor_newsletter_action = new UpdateNewslettersAction();
        \ElementorPro\Plugin::instance()
            ->modules_manager->get_modules('forms')
            ->add_form_action($elementor_newsletter_action->get_name(), $elementor_newsletter_action)
        ;

        $elementor_check_person_action = new CheckPersonAction();
        \ElementorPro\Plugin::instance()
            ->modules_manager->get_modules('forms')
            ->add_form_action($elementor_check_person_action->get_name(), $elementor_check_person_action)
        ;
    }

    function cookie_script()
    {
        wp_enqueue_script('js-cookie', "https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js");
        wp_enqueue_script('lfi-polyfills', plugin_dir_url( __FILE__ ).'/scripts/polyfills.js', [], 1);
        wp_enqueue_script('lfi-agir-cookie', plugin_dir_url( __FILE__ ).'/scripts/cookie.js', ['lfi-polyfills', 'js-cookie'], 1);
    }

    function signature_shortcode_handler($atts, $content, $tag)
    {
        if (!is_array($atts) || !isset($atts["type"]) || !in_array($atts["type"], ["nsp", "lfi"])) {
            return "";
        }

        $transient_key = 'agir_signature_'.$atts["type"];

        $count = get_transient($transient_key);

        if ($count !== false) {
            return $count;
        }

        $options = get_option('lfi_settings');

        $url = $options['api_server'].'/api/people/counter/';
        $query = ['type' => $atts['type']];

        $response = wp_remote_get($url.'?'.http_build_query($query), [
            'headers' => [
                'Content-type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode($options['api_id'].':'.$options['api_key']),
                'X-Wordpress-Client' => $_SERVER['REMOTE_ADDR']
            ]
        ]);

        if (is_wp_error($response) || $response['response']['code'] !== 200) {
            return get_option("lfi_counter_stale");
        }

        $count  = json_decode($response["body"])->value;
        set_transient($transient_key, $count, 30);
        update_option("lfi_counter_stale", $count, false);

        return $count;
    }
}

new Plugin();
