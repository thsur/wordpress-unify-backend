<?php
/*
	Plugin Name: Unify Backend
	URL: http://github.com/nosurs/wordpress-unify-backend
	Version: 0.2.1
	Author: nosurs
	Description: Customize and globalize backend navigation, dashboard, and screen settings.
	License: GPL2

    Copyright 2013 Robin Niemeyer (email: nosurs@gmx.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html
*/

namespace Unify_Backend;

// Version check

global $wp_version;

if(round($wp_version, 1) < 3.1 || round(phpversion(), 1) < 5.4)
{
    die('Unify Backend requires WordPress >= 3.1 and PHP >= 5.4');
}

// Provide autoloading

function autoload($class)
{
    if(strpos($class, __NAMESPACE__) !== false)
    {
        require_once 'lib/'.str_replace('\\', '/', $class).'.php';
    }
}

spl_autoload_register(__NAMESPACE__ . "\\autoload");

// Dump variable lookup output from dbgx_trace_var() on screen

if(defined('WP_DEBUG') && WP_DEBUG === true)
{
    Debug::instance()->init();
}

// Identify plugin, plugin data & nonces

const SLUG = 'unify-backend';

/**
 * Main
 */

class Main
{
    use Singleton;

    /**
     * Menu id.
     *
     * @var String
     */

    protected $menu_handle;

    /**
     * @var Options
     */

    protected $options;

    /**
     * Setup
     */

    public function init()
    {
        $this->options = Options::instance()->init(SLUG);

        // i18n
        //
        // @see http://www.farinspace.com/wordpress-plugin-i18n/
        // @see http://markjaquith.wordpress.com/2011/10/06/translating-wordpress-plugins-and-themes-dont-get-clever/
        // @see http://ottopress.com/2012/internationalization-youre-probably-doing-it-wrong/

        load_plugin_textdomain(SLUG, false, basename(dirname(__FILE__)).'/languages');

        // Bind to settings menu

        add_action('admin_menu', array($this, 'integrate'));

        // Bind settings actions

        Actions::instance()->register_hooks();
    }

    /**
     * Get initialized options helper.
     *
     * @return Options
     */

    public function options()
    {
        return $this->options;
    }

    /**
     * Integrate options page in settings menu.
     */

    public function integrate()
    {
        $this->menu_handle = add_options_page(

                __('Unify Backend', SLUG),
                __('Unify Backend', SLUG),
                'manage_options',
                SLUG,
                array(OptionsPage::instance(), 'page')
        );

        wp_register_style(SLUG, plugins_url('css/main.css', __FILE__));
        wp_register_script(SLUG, plugins_url('js/main.js', __FILE__));

        // Setup options

        add_action('admin_init', array(OptionsPage::instance(), 'register'));

        // Bind scripts & styles

        add_action('admin_print_styles-'.$this->menu_handle, array($this, 'css'));
        add_action('admin_print_scripts-'.$this->menu_handle, array($this, 'js'));

        // Add help

        add_action('load-'.$this->menu_handle, __NAMESPACE__.'\\contextual_help');
    }

    /**
     * return String
     */

    public function get_screen_handle()
    {
        return $this->menu_handle;
    }

    /**
     * Uninstall hook
     */

    public static function uninstall()
    {
        // Check if the file is the one that was registered with the uninstall hook
        // @see http://wordpress.stackexchange.com/questions/25910/uninstall-activate-deactivate-a-plugin-typical-features-how-to
        if ( __FILE__ != WP_UNINSTALL_PLUGIN ) return;

        delete_option(SLUG);
    }

    /**
     * Bind styles
     */

    public function css()
    {
        wp_enqueue_style(SLUG);
    }

    /**
     * Bind scripts
     */

    public function js()
    {
        wp_enqueue_script(SLUG);
    }
}

add_action('init', array(Main::instance(), 'init'));
register_uninstall_hook( __FILE__, array(__NAMESPACE__.'\\Main', 'uninstall'));

/**
 * Base class for options page & actions class.
 *
 * The options page renders and stores the options to set,
 * the actions class applies them.
 */

class Module
{
    use Singleton;

    protected $slug;

    /**
     * Helper class to set and retrieve options.
     *
     * @var Options
     */

    protected $options;

    /**
     * Set slug & bind a options helper
     */

    protected function __construct()
    {
        $this->slug    = SLUG;
        $this->options = Main::instance()->options();
    }
}

/**
 * Option handling.
 *
 * Controller/model-like class: Handle options & provide options page.
 */

class OptionsPage extends Module
{
    /**
     * @var Array
     */

    public $globals;

    protected function __construct()
    {
        parent::__construct();

        // Some Action methods {@see Action} might change some
        // globals we need in a default state, so cache them.

        global $menu,
               $submenu;

        $this->globals = array(

                'menu' => $menu,
                'submenu' => $submenu,
        );
    }

    /**
     * Helper to add a custom section.
     * Custom means the section itself is just a wrapper for a field
     * with some callback to do the actual rendering of the section's markup.
     *
     * @param String $section - ID _and_ callback method to use
     * @param String $title
     */

    protected function add_section($section, $title)
    {
        add_settings_section($section, null, array($this, 'mock'), $this->slug);
        add_settings_field($section, $title, array($this, $section), $this->slug, $section, array($section));
    }

    /**
     * Helper to get the current section id.
     *
     * @param Array - Supposed to be the result of a call to func_get_args()
     */

    protected function get_section_id(Array $func_get_args)
    {
        return (isset($func_get_args[0]) && isset($func_get_args[0][0])) ? $func_get_args[0][0] : null;
    }

    /**
     * Strip tags & tag content from menu entries, widget titles etc.
     */

    protected function filter_title($title)
    {
        return trim(preg_replace('|<[a-z]+[^<]*>.*</[a-z]+[0-9]*>$|i', '', $title));
    }

    /**
     * Register a base setting to add to & to trigger nonce and hidden fields injection.
     * Needs to be called by admin_init as action hook.
     */

    public function register()
    {
        register_setting($this->slug, $this->slug, array($this, 'validate'));
    }

    /**
     * Render outer template
     */

    public function page()
    {
        // Nav

        $this->add_section('nav_settings', __('Navigation', SLUG));

        // Dashboard

        $this->add_section('dashboard_settings', __('Dashboard Widgets', SLUG));

        // Screen options

        $this->add_section('screen_settings', __('Screen Options', SLUG));

        // Outer template

        require 'templates/options.php';
    }

    /**
     * Render nav options
     */

    public function nav_settings()
    {
        $menu    = $this->globals['menu'];
        $submenu = $this->globals['submenu'];

        // Removing the dashboard triggers an error with wp-admin/includes/menu.php:145ff.,
        // so leave it alone.
        array_shift($menu);

        $section_main = $this->get_section_id(func_get_args());
        $section_sub  = $section_main.'_sub';

        $main     = array();
        $sub      = array();

        foreach($menu as $entry)
        {
            @list($name, $capability, $url, , , $id) = $entry;

            $name    = $this->filter_title($name);
            $options = $this->options->get('nav');

            if($name && $id)
            {
                $main[] = array(

                        'label'   => $name,
                        'id'      => "{$this->slug}:{$section_main}:{$id}",
                        'name'    => "{$this->slug}[{$section_main}][{$url}]",
                        'value'   => $url,
                        'url'     => $url,
                        'checked' => isset($options['main'][$url]) ? true : false,
                );

                $parent       = $url;
                $sub[$parent] = array();
                $children     = isset($submenu[$url]) ? $submenu[$url] : array();

                foreach($children as $key => $child)
                {
                    list($name, , $url) = $child;
                    $name = $this->filter_title($name);

                    $sub[$parent][] = array(

                            'label'   => $name,
                            'id'      => "{$this->slug}:{$section_sub}:{$id}:{$key}",
                            'name'    => "{$this->slug}[{$section_sub}][{$parent}][]",
                            'value'   => $url,
                            'checked' => isset($options['subs'][$parent]) && in_array($url, $options['subs'][$parent]) ? true : false,
                    );
                }
            }
        }

        require "templates/{$section_main}.php";
    }

    /**
     * Render dashboard options
     *
     * @see wp-admin/includes/dashboard.php
     */

    public function dashboard_settings()
    {
        // Init dashboard to trigger widgets array setup

        include(ABSPATH . 'wp-admin/includes/dashboard.php');
        wp_dashboard_setup();

        global $wp_meta_boxes;

        // Prepare form

        $section  = $this->get_section_id(func_get_args());
        $options  = $this->options->get('dashboard');

        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($wp_meta_boxes), \RecursiveIteratorIterator::SELF_FIRST);
        $settings = array();

        foreach($iterator as $set)
        {
            if(is_array($set) && isset($set['id']) && isset($set['title']))
            {
               $settings[] = array(

                       'id'    => "{$this->slug}:{$section}:{$set['id']}",
                       'name'  => "{$this->slug}[{$section}][{$set['id']}]",
                       'value' => $set['id'],
                       'label' => $this->filter_title($set['title']),
                       'checked' => isset($options[$set['id']]) ? true : false,
               );
            }
        }

        require "templates/settings.php";
    }

    /**
     * Render screen options
     */

    public function screen_settings()
    {
        // Prepare form

        $section  = $this->get_section_id(func_get_args());
        $settings = array(

            'screen-options'  => array('label' => __('Screen Options', SLUG)),
            'meta-box-order'  => array('label' => __('Meta Box Order', SLUG)),
            'screen-layouts'  => array('label' => __('Screen Layout', SLUG)),
        );

        $user     = get_current_user_id();
        $options  = $this->options->get('screen');

        foreach($settings as $id => $config)
        {
            $settings[$id] = array_merge($config, array(

                    'id'    => "{$this->slug}:{$section}:{$id}",
                    'name'  => "{$this->slug}[{$section}][{$id}]",
                    'value' =>  $id,
                    'checked' => isset($options[$id]) ? true : false,
            ));
        }

        require "templates/settings.php";
    }

    /**
     * Filter options before they're stored.
     */

    public function validate($input)
    {
        return array(

            'nav' => array(

                'main' => $input['nav_settings'],
                'subs' => $input['nav_settings_sub']
            ),
            'dashboard' => $input['dashboard_settings'],
            'screen'    => array_merge(

                $input['screen_settings'],
                array('template_user' => get_current_user_id())
            ),
        );
    }

    /**
     * add_settings_section() needs a callback, so provide a catch-all.
     */

    public function __call($name, $arguments){}
}

/**
 * Actions
 */

class Actions extends Module
{
    protected function __construct()
    {
        parent::__construct();
        $this->options = $this->options->get(); // Cache current options
    }

    /**
     * Hide menus.
     *
     * @see wp-admin/menu.php
     */

    public function menu()
    {
        if(isset($this->options['nav']) && isset($this->options['nav']['main']))
        {
            foreach($this->options['nav']['main'] as $entry)
            {
                remove_menu_page($entry);
            }
        }

        if(isset($this->options['nav']) && isset($this->options['nav']['subs']))
        {
            foreach($this->options['nav']['subs'] as $menu => $entries)
            {
                foreach($entries as $entry)
                {
                    remove_submenu_page($menu, $entry);
                }
            }
        }
    }

    /**
     * @see http://codex.wordpress.org/Dashboard_Widgets_API#Advanced:_Removing_Dashboard_Widgets
     */

    public function dashboard()
    {
        if(!isset($this->options['dashboard']))
        {
            return;
        }

        $widgets = array_keys($this->options['dashboard']);
        $screen  = get_current_screen();

        if($screen->id == Main::instance()->get_screen_handle())
        {
            return;
        }

        foreach($widgets as $id)
        {
            remove_meta_box($id, $screen, 'normal');
            remove_meta_box($id, $screen, 'side');
        }
    }

    /**
     * Globalize screen options
     *
     * @see wp-admin/includes/screen.php
     * @see wp-includes/user.php
     */

    public function screen()
    {
        if(!isset($this->options['screen']))
        {
            return;
        }

        // Check user

        $options = $this->options['screen'];
        $user    = get_current_user_id();

        if(!isset($options['template_user']) || $user == $options['template_user'])
        {
            return;
        }

        // What options to set

        $lookup   = array();

        foreach($options as $value)
        {
            switch($value)
            {
                case 'screen-options':
                    $lookup[] = '(^manage.*columnshidden$|^metaboxhidden)';
                    break;

                case 'meta-box-order':
                    $lookup[] = '^meta-box-order';
                    break;

                case 'screen-layouts':
                    $lookup[] = '^screen_layout';
                    break;
            }
        }

        $lookup = '/'.implode('|', $lookup).'/';

        // Look up template user settings

        $settings = get_user_meta($options['template_user']);
        $update  = array();

        foreach($settings as $key => $value)
        {
            if(preg_match($lookup, $key))
            {
                if(is_array($value))
                {
                    if(strpos($key, 'screen_layout_') === 0)
                    {
                        $value = array_pop($value);
                    }
                    else
                    {
                        $value = unserialize(array_pop($value));
                    }
                }

                $update[$key] = $value;
            }
        }

        // Update current user settings

        $current = get_user_meta($user);

        foreach($update as $key => $value)
        {
            if(!isset($current[$key]) || $current[$key] !== $value)
            {
                update_user_meta($user, $key, $value);
            }
        }
    }

    /**
     * Register method hooks.
     */

    public function register_hooks()
    {
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_head', array($this, 'screen'));

        add_action('wp_dashboard_setup', array($this, 'dashboard'));
    }
}

/**
 * Contextual help
 *
 * @see http://codex.wordpress.org/Adding_Contextual_Help_to_Administration_Menus
 * @see http://codex.wordpress.org/Function_Reference/add_help_tab
 * @see http://ottopress.com/2011/new-in-wordpress-3-3-more-useful-help-screens
 */

function contextual_help()
{
    $screen = get_current_screen();

    if($screen->id == Main::instance()->get_screen_handle())
    {
        require_once 'lib/help_tabs.php';
    }
}
