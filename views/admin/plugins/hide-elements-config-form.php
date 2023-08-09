<style type="text/css">
.hide-boxes {
    text-align: center;
}
.input-block ul {
    list-style: none outside none;
}
</style>
<div class="field">
    <div class="two columns alpha">
        <label for="override-restrictions"><?php echo __('Override visibility restrictions by role'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <div class="input-block">
            <ul>
                <?php
                $userRoles = get_user_roles();
                foreach ($userRoles as $role => $label) {
                    echo '<li>';
                    echo $view->formCheckbox('override[]', $role, array(
                        'disableHidden' => true,
                        'checked' => in_array($role, $settings['override']),
                    ));
                    echo ' ' . $label;
                    echo '</li>';
                }
                ?>
            </ul>
        </div>
        <p class="explanation">
            <?php echo __("Note: Full text indexation can't be overridden because the same field is used for all users."); ?>
        </p>
    </div>
</div>
<table id="hide-elements-table">
    <thead>
        <tr>
            <th class="hide-boxes" rowspan="2"><?php echo __('Element'); ?></th>
            <th class="hide-boxes" colspan="4"><?php echo __('Hide on:'); ?></th>
        </tr>
        <tr>
            <th class="hide-boxes"><?php echo __('Form'); ?></th>
            <th class="hide-boxes"><?php echo __('Admin'); ?></th>
            <th class="hide-boxes"><?php echo __('Public'); ?></th>
            <th class="hide-boxes"><?php echo __('Search'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $current_element_set = null;
    foreach ($elements as $element):
        if ($element->set_name != $current_element_set):
            $current_element_set = $element->set_name;
    ?>
        <tr>
            <th colspan="5">
                <strong><?php echo __($current_element_set); ?></strong>
            </th>
        </tr>
        <?php endif; ?>
        <tr>
            <td><?php echo __($element->name); ?></td>
            <td class="hide-boxes">
                <?php echo $view->formCheckbox(
                    "form[{$element->set_name}][{$element->name}]",
                    '1', array(
                        'disableHidden' => true,
                        'checked' => isset($settings['form'][$element->set_name][$element->name])
                    )
                ); ?>
            </td>
            <td class="hide-boxes">
                <?php echo $view->formCheckbox(
                    "admin[{$element->set_name}][{$element->name}]",
                    '1', array(
                        'disableHidden' => true,
                        'checked' => isset($settings['admin'][$element->set_name][$element->name])
                    )
                ); ?>
            </td>
            <td class="hide-boxes">
                <?php echo $view->formCheckbox(
                    "public[{$element->set_name}][{$element->name}]",
                    '1', array(
                        'disableHidden' => true,
                        'checked' => isset($settings['public'][$element->set_name][$element->name])
                    )
                ); ?>
            </td>
            <td class="hide-boxes">
                <?php echo $view->formCheckbox(
                    "search[{$element->set_name}][{$element->id}]",
                    '1', array(
                        'disableHidden' => true,
                        'checked' => isset($settings['search'][$element->set_name][$element->id])
                    )
                ); ?>
            </td>
        </tr>
	<?php endforeach; ?>
    </tbody>
</table>
