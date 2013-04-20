<?php $view = get_view(); ?>
<style type="text/css">
.hide-boxes {
    text-align: center;
}
</style>
<table id="hide-elements-table">
    <thead>
        <tr>
            <th rowspan="2"><?php echo __('Element'); ?></th>
            <th class="hide-boxes" colspan="3"><?php echo __('Hide on:'); ?></th>
        </tr>
        <tr>
            <th class="hide-boxes"><?php echo __('Form'); ?></th>
            <th class="hide-boxes"><?php echo __('Admin'); ?></th>
            <th class="hide-boxes"><?php echo __('Public'); ?></th>
        </tr>
    <thead>
    <tbody>
    <?php
    $current_element_set = null;
    foreach ($elements as $element):
        if ($element->set_name != $current_element_set):
            $current_element_set = $element->set_name;
    ?>
        <tr>
            <th colspan="4">
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
        </tr>
	<?php endforeach; ?>
    </tbody>
</table>
