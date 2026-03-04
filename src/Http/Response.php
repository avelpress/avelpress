<?php

namespace AvelPress\Http;

defined('ABSPATH') || exit;

class Response
{
    public function json($data = [], $status = 200, $headers = [], $options = 0)
    {
        if ($status >= 400) {
            return new \WP_Error(
                $status,
                isset($data['message']) ? $data['message'] : (isset($data['error']) ? $data['error'] : 'Error'),
                ['status' => $status]
            );
        }

        $response = rest_ensure_response($data);

        if (is_wp_error($response)) {
            return $response;
        }

        $response->set_status($status);

        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}
