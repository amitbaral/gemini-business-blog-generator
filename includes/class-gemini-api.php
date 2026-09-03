<?php
if (!defined('ABSPATH')) {
    exit;
}

class GBBG_Gemini_API {

    private $api_key;
    private $model;
    private $temperature;
    private $max_tokens;
    private $top_p;
    private $top_k;

    public function __construct() {
        $this->api_key     = get_option('gbbg_gemini_api_key', '');
        $this->model       = get_option('gbbg_gemini_model', 'gemini-3.8-flash');
        $this->temperature = (float) get_option('gbbg_gemini_temperature', 0.7);
        $this->max_tokens  = (int) get_option('gbbg_gemini_max_tokens', 8192);
        $this->top_p       = (float) get_option('gbbg_gemini_top_p', 0.95);
        $this->top_k       = (int) get_option('gbbg_gemini_top_k', 40);
    }

    public function set_api_key($key) {
        $this->api_key = trim($key);
    }

    public function set_model($model) {
        $this->model = trim($model);
    }

    public function test_connection() {
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', __('Please enter a valid Google Gemini API key in Settings.', 'gemini-business-blog'));
        }

        $prompt = "Respond with 'CONNECTED_SUCCESSFULLY' if you receive this message.";
        $result = $this->generate_content($prompt);

        if (is_wp_error($result)) {
            return $result;
        }

        return true;
    }

    public function generate_content($prompt, $system_instruction = null) {
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', __('Gemini API key is required.', 'gemini-business-blog'));
        }

        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            urlencode($this->model),
            urlencode($this->api_key)
        );

        $body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $prompt)
                    )
                )
            ),
            'generationConfig' => array(
                'temperature'     => $this->temperature,
                'maxOutputTokens' => $this->max_tokens,
                'topP'            => $this->top_p,
                'topK'            => $this->top_k,
            )
        );

        if (!empty($system_instruction)) {
            $body['systemInstruction'] = array(
                'parts' => array(
                    array('text' => $system_instruction)
                )
            );
        }

        $response = wp_remote_post($endpoint, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body'    => wp_json_encode($body),
            'timeout' => 90,
        ));

        if (is_wp_error($response)) {
            // Try fallback model if primary model failed
            if ($this->model !== 'gemini-2.5-flash') {
                $this->model = 'gemini-2.5-flash';
                return $this->generate_content($prompt, $system_instruction);
            }
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($code !== 200) {
            $error_msg = isset($data['error']['message']) ? $data['error']['message'] : ('API Error HTTP Code ' . $code);
            return new WP_Error('gemini_api_error', $error_msg);
        }

        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        return new WP_Error('empty_response', __('Empty response received from Gemini API.', 'gemini-business-blog'));
    }
}