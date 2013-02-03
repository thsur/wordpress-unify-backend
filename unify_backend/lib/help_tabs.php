<?php

namespace Unify_Backend;

$screen = get_current_screen();

$screen->add_help_tab(array(

    'id'      => SLUG.'-navigation',
    'title'   => __('Navigation', SLUG),
    'content' => '<p>'.__('Check the items that should be hidden in the backend navigation.', SLUG).'</p>'
                .'<p>'.__('As with the rest of the options below, your settings will affect all backend users.', SLUG).'</p>'
                .'<p>'.__('Be aware that hiding the "Settings" navigation will effectively hide this page, too.', SLUG).' '
                .__('Though you can savely hide subpages to unclutter the "Settings" navigation, it should not be necessary in terms of a more streamlined user experience, since it is always only displayed to admins.', SLUG).'</p>'
                .'<p>'.__('Removing the dashboard is not possible yet, so it is excluded from the navigation settings.', SLUG).'</p>'
));

$screen->add_help_tab(array(

    'id'      => SLUG.'-dashboard',
    'title'   => __('Dashboard', SLUG),
    'content' => '<p>'.__('Check the widgets you do not want to be displayed on the dashboard.', SLUG).'</p>'
                .'<p>'.__('Your users will still be able to uncheck a widget by using the dashboard screen options, though they are limited to the set of widgets you define in here.', SLUG).'</p>',
));

$screen->add_help_tab(array(

    'id'      => SLUG.'-screen_options',
    'title'   => __('Screen Options', SLUG),
    'content' => '<p>'.__('Check the options that should be globalized for each user based on your settings.', SLUG).'</p>'
                .'<p>'.__('Say you want only the categories, comments, and tags meta boxes to show up in the post editing screen.', SLUG).' '
                .__('Just check "Screen Options" (and maybe "Meta Box Order"), save them, and head over to the post editing screen.', SLUG).'</p>'
                .'<p>'.__('Uncheck all boxes you do not want to show up using the post editing screen options.', SLUG).' '
                .__('If you have set "Meta Box Order", feel free to order the remaining boxes the way you want them to be displayed.', SLUG).'</p>'
                .'<p>'.__('All users are now bound to your settings, meaning they only see what you see (or less, depending on their roles).', SLUG).' '
                .__('Unlike the "Dashboard" setting above, your users will not be able to override your settings.', SLUG).'</p>'
                .'<p>'.__('Be aware that each time you change a screen setting, it will change for your users as well.', SLUG).' '
                .__('If this conflicts with your setup, consider to introduce a template user (who needs admin privileges, though).', SLUG).'</p>',
));
