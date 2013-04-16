<?php $view = get_view(); ?>
<table>
    <thead>
        <tr>
            <th rowspan="2"><?php echo __('Element'); ?></th>
            <th colspan="3"><?php echo __('Hide on:'); ?></th>
        </tr>
        <tr>
            <th><?php echo __('Form'); ?></th>
            <th><?php echo __('Admin'); ?></th>
            <th><?php echo __('Public'); ?></th>
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
            <td colspan="4">
                <strong><?php echo __($current_element_set); ?></strong>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <td><?php echo __($element->name); ?></td>
            <td>
                <?php echo $view->formCheckbox(
                    "form[{$element->set_name}][{$element->name}]",
                    '1', array(
                        'disableHidden' => true,
                        'checked' => isset($settings['form'][$element->set_name][$element->name])
                    )
                ); ?>
            </td>
            <td>
                <?php echo $view->formCheckbox(
                    "admin[{$element->set_name}][{$element->name}]",
                    '1', array(
                        'disableHidden' => true,
                        'checked' => isset($settings['admin'][$element->set_name][$element->name])
                    )
                ); ?>
            </td>
            <td>
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
