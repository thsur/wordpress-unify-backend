<?php

/**
 * Debug class.
 *
 * Dump all variables registered by {@see Debug_Bar_Extender::trace_*()}
 * directly on screen.
 *
 * Use/activate:
 * Debug::instance()->init()
 *
 * For debugging WP in general, see:
 * http://codex.wordpress.org/Debugging_in_WordPress
 */

namespace Unify_Backend;

use Debug_Bar_Extender,
    ReflectionClass;

class Debug
{
    use Singleton;

    public function init($hook = 'admin_init', $order = 99)
    {
        if(!has_action($hook, array($this, 'dump')))
        {
            add_action($hook, array($this, 'dump'), $order);
        }
    }

    public function dump()
    {
        if(class_exists('Debug_Bar_Extender'))
        {
            // Debug_Bar_Extender::variable_lookup is private, so get it via reflection

            $reflect = new ReflectionClass('Debug_Bar_Extender');
            $vars    = $reflect->getProperty('variable_lookup');

            // Change Debug_Bar_Extender::variable_lookup's visibility

            $vars->setAccessible(true);

            // Filter trace

            $collect = array();

            foreach($vars->getValue(Debug_Bar_Extender::instance()) as $var => $stack)
            {
                foreach($stack as $num => $trace)
                {
                    // Skip default traced vars & additional info

                    if(is_array($trace) && strpos($trace['function'], 'trace_var_') === false)
                    {
                        // Skip all trace info, keep actual value

                        $collect[$var][$num] = $trace['value'];
                    }
                }
            }

            if(!empty($collect))
            {
                var_dump($collect);
            }
        }
    }
}