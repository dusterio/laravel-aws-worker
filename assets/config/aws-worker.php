<?php

/**
 * @author      José Lorente <jose.lorente.martin@gmail.com>
 * @license     The MIT License (MIT)
 * @copyright   José Lorente
 * @version     1.0
 */
return [
    /*
      |--------------------------------------------------------------------------
      | Register worker routes
      |--------------------------------------------------------------------------
      |
      | Defines whether to register worker routes or not.
     */
    'register_worker_routes' => function_exists('env') && !env('REGISTER_WORKER_ROUTES', true)
];
