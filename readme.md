
# Unify Backend

A [WordPress](http://wordpress.org/) plugin. 

Unifies parts of the backend for all logged in users, based on the settings of a single user. Currently,
this user has to have admin rights, though (see _Drawbacks_ below). 

Aimed at smaller projects with a couple of users.  

## What it does

Adds itself to WordPress' _Settings_ menu to offer a small options page with three different areas 
(all settings affect all users):

### Navigation
Select what should be hidden in the main backend navigation. Takes into account both main and sub menu
entries, so you can disable _Pages_ and _Links_, but also only _Tags_ from _Posts_.     

### Dashboard
Like above: Select the widgets that shouldn't be displayed on the dashboard.  

### Screen Options
Provides three options: _Screen Options_, _Meta Box Order_, _Screen Layout_. When checked, these define
which settings should be unified for all users. Which basically means: Every time you change a setting
in a screen options dialogue, say in the _Edit Post_ screen, or change the order of some input boxes,
the settings of your users will be updated accordingly. [Switch users](http://wordpress.org/extend/plugins/user-switching/) 
(or log in as one of them) to see the changes take affect. 

For more info, please refer to the provided inline help on the options page itself.      

## Requirements

PHP > 5.4  
WordPress > 3.1 (though probably the most recent version would be best)

## Installation

Copy the main folder ('unify_backend') over to WordPress' plugin directory and switch it on in the backend.

## License

[GPL v2](http://www.opensource.org/licenses/GPL-2.0)  

## Drawbacks

Apart from the PHP requirements, which might or might not be an issue, there are some drawbacks, 
most of them resulting from the fact that development is casual, and was meant to meet some basic needs only:

* It's neither planned nor possible to select a user to derive the _Screen Options_ setting from.
* Instead, it's always the admin user the settings are derived from (which is probably you).
* So it's a one-for-all kind of thing. No possibility to have different views for different users. 
* Technically, the settings of the users aren't touched. Instead, they're overwritten _after_ they're 
read from the database - each time a users requests a backend page. Which also might or might not
be an issue. 
* Code is prepared for localization, but comes with no language files.
* It's currently not possible to remove the dashboard without breaking the backend menu, which is why it can't be hidden 
with _Unify Backend_ (so it might be best to apply some JavaScript/CSS to do it).    

Feel free to grab the code and alter it the way you like.  

