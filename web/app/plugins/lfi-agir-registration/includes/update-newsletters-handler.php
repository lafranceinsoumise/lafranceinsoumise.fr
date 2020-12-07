<?php
namespace LFI\WPPlugins\AgirRegistration;

use ElementorPro\Modules\Forms\Classes\Action_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class UpdateNewslettersAction extends Action_Base
{
    public function get_name()
    {
        return 'lfi-newsletters';
    }

    public function get_label()
    {
        return "LFI : Abonnements aux newsletters";
    }

    public function run($record, $ajax_handler) {
        $raw_fields = $record->get('fields');

        // Normalize the Form Data
        $fields = [];
        foreach ($raw_fields as $id => $field) {
            $fields[$id]  = $field['value'];
        }

        if (empty($fields['agir_id']) || $fields['agir_id'] === '') {
            $ajax_handler->add_error_message('Vous n\'êtes pas identifé⋅e. Avez-vous bien <a href="/">signé sur ce site</a> ?');
            return;
        }

        $newsletters = [];
        foreach ($fields as $field => $value) {
            if ($field === "agir_id") {
                continue;
            }

            $newsletters[$field] = !!$value;
        }

        $options = get_option('lfi_settings');

        $url = $options['api_server'].'/api/people/newsletters/';

        $body = json_encode([
            "id" => $fields["agir_id"],
            "newsletters" => $newsletters
        ]);

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode($options['api_id'].':'.$options['api_key']),
                'X-Wordpress-Client' => $_SERVER['REMOTE_ADDR']
            ],
            'body' => $body
        ]);

        if (is_wp_error($response)) {
            $ajax_handler->add_error_message('Une erreur est survenue, veuillez réessayer plus tard.')->send();
            return;
        }

        if ($response['response']['code'] === 422) {
            error_log('422 error while POSTing to API : '.$response['body']);
            $ajax_handler->add_error_message('Une erreur est survenue, veuillez réessayer plus tard.');
            return;
        }

        if ($response['response']['code'] === 400) {
            $errors = json_decode($response["body"]);
            foreach ($errors as $field => $msg) {
                $ajax_handler->add_error($field, $msg);
            }
        }

        if ($response['response']['code'] !== 200) {
            $ajax_handler->add_error_message('Une erreur est survenue, veuillez réessayer plus tard.');
            return;
        }

        $ajax_handler->add_success_message('Vos préférences ont bien été enregitrées.');
    }

    public function register_settings_section($widget)
    {
        // TODO: Implement register_settings_section() method.
    }

    public function on_export($element)
    {
        // TODO: Implement on_export() method.
    }
}
