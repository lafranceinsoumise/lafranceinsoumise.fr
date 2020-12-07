<?php
namespace LFI\WPPlugins\AgirRegistration;

use ElementorPro\Modules\Forms\Classes\Action_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class CheckPersonAction extends Action_Base
{
    public function get_name()
    {
        return 'lfi-check-person';
    }

    public function get_label()
    {
        return "LFI : Identifier la personne";
    }

    public function run($record, $ajax_handler) {
        $settings = $record->get('form_settings');
        $raw_fields = $record->get('fields');

        // Normalize the Form Data
        $fields = [];
        foreach ($raw_fields as $id => $field) {
            $fields[$id]  = $field['value'];
        }

        if (empty($fields['agir_id']) && empty($fields['agir_email'])) {
            $ajax_handler->add_admin_error_message('Pas de champ agir_id ou agir_email.');
            return;
        }

        $options = get_option('lfi_settings');

        // Check required option
        if (empty($settings["agir_check_redirection"])) {
            $ajax_handler->add_admin_error_message("URL de redirection non configurée");
            return;
        }

        // Build request to API
        $url = $options['api_server'].'/api/people/retrieve/';

        if (!empty($fields['agir_id']) && $fields['agir_id'] !== '') {
            $query = ['id' => $fields['agir_id']];
        } elseif (!empty($fields['agir_email']) && $fields['agir_email'] !== '') {
            $query = ['email' => $fields['agir_email']];
        } else {
            $ajax_handler->add_error_message('Vous n\'êtes pas identifé⋅e. Avez-vous bien <a href="/">signé sur ce site</a> ?');
            return;
        }

        $response = wp_remote_get($url.'?'.http_build_query($query), [
            'headers' => [
                'Content-type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode($options['api_id'].':'.$options['api_key']),
                'X-Wordpress-Client' => $_SERVER['REMOTE_ADDR']
            ]
        ]);

        // Handler unexpected errors
        if (is_wp_error($response)) {
            $ajax_handler->add_error_message('Une erreur est survenue, veuillez réessayer plus tard.')->send();
            return;
        }

        if (!in_array($response['response']['code'], [200, 404])) {
            $ajax_handler->add_error_message('Une erreur est survenue, veuillez réessayer plus tard.');
            return;
        }

        // Handle person found
        if ($response['response']['code'] == 200) {
            $person = json_decode($response["body"]);

            if (($settings['agir_registration_check_type'] === 'LFI' && $person->isInsoumise)
                || ($settings['agir_registration_check_type'] === 'NSP' && $person->is2022)) {
                $ajax_handler->add_response_data('redirect_url', $settings["agir_check_redirection"]["url"].'?agir_id='.$id);
                return;
            }
        }

        // Handle person not found and required
        if (isset($settings["agir_registration_check_enforce"]) && $settings["agir_registration_check_enforce"]) {
            $ajax_handler->add_error_message('Vous n\'êtes pas identifé⋅e. Avez-vous bien <a href="/">signé sur ce site</a> ?');
            return;
        }

        // Handle person not found and privacy
        $ajax_handler->add_response_data('redirect_url', $settings["agir_check_redirection"]["url"].'?agir_id='.wp_generate_uuid4());
    }

    public function register_settings_section($widget)
    {
        $widget->start_controls_section('section_agir_check_person', [
            'label' => 'Vérification de la personne',
            'condition' => [
                'submit_actions' => $this->get_name(),
            ],
        ]);

        $widget->add_control(
            'agir_registration_check_type',
            [
                'label' => "Chercher les inscrits",
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'LFI' => "LFI",
                    'NSP' => "NSP",
                ],
                'default' => 'NSP'
            ]
        );

        $widget->add_control(
            'agir_registration_check_enforce',
            [
                'label' => "Ne pas révéler qui est inscrit",
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => true,
            ]
        );

        $widget->add_control(
            'agir_check_redirection',
            [
                'label' => "Rediriger vers",
                'type' => \Elementor\Controls_Manager::URL,
            ]
        );

        $widget->end_controls_section();
    }

    public function on_export($element)
    {
        // TODO: Implement on_export() method.
    }
}
