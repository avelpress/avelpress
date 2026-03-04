<?php

namespace AvelPress\Facades;

use AvelPress\Http\Response as HttpResponse;

defined('ABSPATH') || exit;

/**
 * @method static \WP_Error|\WP_REST_Response json(array $data = [], int $status = 200, array $headers = [], int $options = 0)
 */

class Response extends Facade
{

    protected static function getFacadeAccessor()
    {
        return HttpResponse::class;
    }
}
