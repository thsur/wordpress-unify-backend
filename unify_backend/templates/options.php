<?php namespace Unify_Backend; ?>

<div class="wrap" id="admin_customize">

    <?php screen_icon(); ?>
    <h2><?php _e('Unify Backend', SLUG); ?></h2>

    <form action="options.php" method="post">
        <?php settings_fields(SLUG); ?>
        <?php do_settings_sections(SLUG); ?>
        <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes', SLUG); ?>" />
    </form>

</div>