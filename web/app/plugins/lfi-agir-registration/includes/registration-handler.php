<?php
namespace LFI\WPPlugins\AgirRegistration;


use ElementorPro\Modules\Forms\Classes\Action_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class RegistrationAction extends Action_Base
{
    public function get_name()
    {
        return 'lfi-registration';
    }

    public function get_label()
    {
        return "LFI : Inscription à la plateforme";
    }

    public function run($record, $ajax_handler) {
        $settings = $record->get('form_settings');
        $raw_fields = $record->get('fields');

        if (empty($settings['agir_registration_type']) ||
            !in_array($settings['agir_registration_type'], ['LFI', 'NSP'])) {
            return;
        }

        // Normalize the Form Data
        $fields = [];
        foreach ($raw_fields as $id => $field) {
            $fields[str_replace("agir_", "", $id)]  = $field['value'];
        }

        if (empty($fields['email'])) {
            $ajax_handler->add_error("email", "L'email est obligatoire.");
        }

        if (!empty($fields['email']) && !is_email($fields['email'])) {
            $ajax_handler->add_error("email", "L'e-mail est invalide.");
        }

        if (empty($fields['location_zip'])) {
            $ajax_handler->add_error("location_zip", 'Le code postal est obligatoire.');
        }

        if (!empty($fields['location_zip']) && !preg_match('/^[0-9]{5}$/', $fields['location_zip'])) {
            $ajax_handler->add_error("location_zip", 'Le code postal est invalide.');
        }

        if (count($ajax_handler->errors) > 0) {
            return;
        }

        $data = [];
        $data["email"] = sanitize_email($fields['email']);
        $data["location_zip"] = sanitize_text_field($fields['location_zip']);
        $data["type"] = $settings["agir_registration_type"];

        $api_fields = [
            "first_name",
            "last_name",
            "contact_phone",
            "referer",
            "mandat"
        ];

        foreach ($api_fields as $api_field) {
            if (isset($fields[$api_field]) && $fields[$api_field] !== "") {
                $data[$api_field] = sanitize_text_field($fields[$api_field]);
            }
        }

        $options = get_option('lfi_settings');

        $url = $options['api_server'].'/api/people/subscription/';

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode($options['api_id'].':'.$options['api_key']),
                'X-Wordpress-Client' => $_SERVER['REMOTE_ADDR']
            ],
            'body' => json_encode($data)
        ]);

        if (!is_wp_error($response) && $response['response']['code'] === 400) {
            $errors = json_decode($response["body"]);
            foreach ($errors as $field => $msg) {
                $ajax_handler->add_error($field, $msg);
            }
        }

        if (is_wp_error($response) || $response['response']['code'] !== 201) {
            $ajax_handler->add_error_message('Une erreur est survenue, veuillez réessayer plus tard.');
            return;
        }

        $redirect_to = json_decode($response['body'])->url;

        if (!empty( $redirect_to ) && filter_var($redirect_to, FILTER_VALIDATE_URL)) {
            $ajax_handler->add_response_data( 'redirect_url', $redirect_to );
        }
    }

    public function register_settings_section($widget)
    {
        $widget->start_controls_section('section_agir_registration', [
            'label' => 'Inscription à la plateforme',
            'condition' => [
                'submit_actions' => $this->get_name(),
            ],
        ]);

        $widget->add_control(
            'agir_registration_type',
            [
                'label' => "Type d'inscription",
                'type' => \Elementor\Controls_Manager::SELECT,
                'description' => 'Les champs pris en compte sont first_name, last_name, email, contact_phone, location_zip. L\'URL
            de redirection dépend du type d\'inscription.',
                'options' => [
                    'LFI' => "LFI",
                    'NSP' => "NSP",
                ],
                'default' => 'NSP'
            ]
        );

        $widget->end_controls_section();
    }

    public function on_export($element)
    {
        unset($element['agir_registration_type']);
    }
}
