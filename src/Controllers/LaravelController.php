<?php

namespace Dusterio\AwsWorker\Controllers;

abstract class LaravelController
{
    /**
     * The middleware defined on the controller.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Define a middleware on the controller.
     *
     * @param  string $middleware
     * @param  array $options
     * @return void
     */
    public function middleware($middleware, array $options = [])
    {
        $this->middleware[$middleware] = $options;
    }

    /**
     * Get the middleware assigned to the controller.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Execute an action on the controller.
     *
     * @param  string $method
     * @param  array $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        return call_user_func_array([$this, $method], $parameters);
    }

    /**
     * Get the registered "after" filters.
     *
     * @return array
     *
     * @deprecated since version 5.1.
     */
    public function getAfterFilters()
    {
        return [];
    }

    /**
     * Get the registered "before" filters.
     *
     * @return array
     *
     * @deprecated since version 5.1.
     */
    public function getBeforeFilters()
    {
        return [];
    }

    /**
     * Get the middleware for a given method.
     *
     * @param  string $method
     * @return array
     */
    public function getMiddlewareForMethod($method)
    {
        $middleware = [];

        foreach ($this->middleware as $name => $options) {
            if (isset($options['only']) && !in_array($method, (array)$options['only'])) {
                continue;
            }

            if (isset($options['except']) && in_array($method, (array)$options['except'])) {
                continue;
            }

            $middleware[] = $name;
        }

        return $middleware;
    }
}

