<?php namespace Unify_Backend; ?>

<div class="ac_section">
    <ul>
        <?php foreach($main as $entry): ?>
        <li class="clear">
            <input type="checkbox" value="<?php echo $entry["value"] ?>" <?php echo ($entry["checked"] ? 'checked' : '') ?>
                   id="<?php echo $entry["id"] ?>" name="<?php echo $entry["name"] ?>" />
            <label for="<?php echo $entry["id"] ?>"><?php echo $entry["label"] ?></label>
            <?php if($sub[$entry["url"]]): ?>
                <ul>
                    <?php foreach($sub[$entry["url"]] as $entry): ?>
                    <li>
                        <input type="checkbox" value="<?php echo $entry["value"] ?>" <?php echo ($entry["checked"] ? 'checked' : '') ?>
                               id="<?php echo $entry["id"] ?>" name="<?php echo $entry["name"] ?>" />
                        <label for="<?php echo $entry["id"] ?>"><?php echo $entry["label"] ?></label>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
