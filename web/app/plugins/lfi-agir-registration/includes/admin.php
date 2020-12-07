<?php

namespace LFI\WPPlugins\AgirRegistration;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Admin
{
    private $dummykey = "somethingveryveryverylonglongenoughtofillfield";

    public function __construct()
    {
        // When initialized
        add_action('admin_init', [$this, 'settings_init']);

        // When menu load
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }

    public function add_admin_menu()
    {
        add_options_page(
            'Paramètres de LFI Inscription plateforme',
            'LFI Inscription plateforme',
            'manage_options',
            'lfi-agir-registration',
            [$this, 'options_page']
        );
    }

    public function options_page()
    {
        ?>
        <h1>Paramètres de LFI Inscription plateforme</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('lfi_settings_page');
            do_settings_sections('lfi_settings_page');
            submit_button("Vérifier les identifiants"); ?>
        </form>
        <?php

    }

    public function settings_init()
    {
        register_setting('lfi_settings_page', 'lfi_settings', [$this, 'sanitize']);

        add_settings_section(
            'lfi_credentials_section',
            'Identifiants de l\'API plateforme',
            [$this, 'credentials_section_callback'],
            'lfi_settings_page'
        );
    }

    public function sanitize($data)
    {
        $old = get_option('lfi_settings');

        if ($data['api_key'] == $this->dummykey) {
            $data['api_key'] = $old['api_key'];
        }


        $data['api_success'] = $this->check_credentials($data['api_server'], $data['api_id'], $data['api_key']);

        return $data;
    }

    public function credentials_section_callback()
    {
        add_settings_field(
            'lfi_api_server',
            'Server',
            [$this, 'api_server_render'],
            'lfi_settings_page',
            'lfi_credentials_section'
        );

        add_settings_field(
            'lfi_api_id',
            'ID',
            [$this, 'api_id_render'],
            'lfi_settings_page',
            'lfi_credentials_section'
        );

        add_settings_field(
            'lfi_api_key',
            'Secret',
            [$this, 'api_key_render'],
            'lfi_settings_page',
            'lfi_credentials_section'
        );
    }


    public function api_server_render()
    {
        $options = get_option('lfi_settings'); ?>

        <input type="text"
               name="lfi_settings[api_server]"
               value="<?= isset($options['api_server']) ? esc_attr($options['api_server']) : ''; ?>">

        <?php
    }

    public function api_id_render()
    {
        $options = get_option('lfi_settings'); ?>

        <input type="text"
               name="lfi_settings[api_id]"
               value="<?= isset($options['api_id']) ? esc_attr($options['api_id']) : ''; ?>">

        <?php
    }

    public function api_key_render()
    {
        $options = get_option('lfi_settings'); ?>

        <input type="password"
               name="lfi_settings[api_key]"
               value="<?= empty($options["api_key"]) ? "" : $this->dummykey ?>">

        <?php
         if (isset($options["api_success"]) && $options["api_success"] === true) {
            ?><p style="color: green;">API connectée</p> <?php
        }

        if (isset($options["api_success"]) && $options["api_success"] === false) {
            ?><p style="color: red;">L'authentification a échoué</p><?php
        }
    }

    private function check_credentials($domain, $id, $key)
    {
        try {
            $url = $domain.'/legacy/people/';

            $response = wp_remote_get($url, [
                'timeout' => 300,
                'headers' => [
                    'Authorization' => 'Basic '.base64_encode($id.':'.$key),
                ],
            ]);

            if (isset($error) || is_wp_error($response)) {
                return false;
            }

            if (in_array($response['response']['code'], [401, 403])) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
