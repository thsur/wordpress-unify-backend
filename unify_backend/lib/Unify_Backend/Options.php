<?php

/**
 * Options helper
 */

namespace Unify_Backend;

class Options
{
    use Singleton;

    protected $defaults = array();
    protected $slug = null;

    public function has_access()
    {
        return current_user_can('manage_options');
    }

    public function init($slug, Array $defaults = array())
    {
        $this->slug = $slug;
        $this->defaults = $defaults;

        return $this;
    }

    public function trash_all()
    {
        if(!$this->has_access()) return;
        update_option($this->slug, array());
    }

    public function update(Array $options)
    {
        if(!$this->has_access()) return;

        $current = get_option($this->slug);

        foreach($current as $name => $value)
        {
            // Add already present values to the bunch...

            if(!isset($options[$name]))
            {
                $options[$name] = $value;
            }
        }

        // ...so we can write out everything in one go

        return update_option($this->slug, $options);
    }

    public function get($option = null)
    {
        $options = get_option($this->slug);

        if($option)
        {
            if(is_array($option))
            {
                $fetched = $options;
                $cursor  = array();

                foreach($option as $key)
                {
                    if(isset($fetched[$key]))
                    {
                        $fetched  = $fetched[$key];
                        $cursor[] = $key;
                    }
                }

                return $cursor === $option ? $fetched : null;
            }

            return isset($options[$option]) ? $options[$option] : null;
        }

        return $options;
    }
}
