<div class="ac_section">
    <ul>
        <?php foreach($settings as $setting): ?>
        <li class="clear">
            <input type="checkbox" value="<?php echo $setting["value"] ?>" <?php echo ($setting["checked"] ? 'checked' : '') ?>
                   id="<?php echo $setting["id"] ?>" name="<?php echo $setting["name"] ?>" />
            <label for="<?php echo $setting["id"] ?>"><?php echo $setting["label"] ?></label>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
