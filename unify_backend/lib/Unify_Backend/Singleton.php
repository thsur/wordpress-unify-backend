<?php

namespace Unify_Backend;

trait Singleton
{
    protected static $instance;

    protected function __construct(){}
    protected function __clone(){}

    /**
     * @return self
     */

    public static function instance()
    {
        // Utilize late static binding (>= PHP 5.3)

        $context = get_called_class();

        if(!static::$instance || !(static::$instance instanceof $context))
        {
            static::$instance = new static;
        }

        return static::$instance;
    }
}