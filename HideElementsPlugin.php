<?php
/**
 * @package HideElements
 * @copyright Copyright 2013, John Flatness
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPLv3 or any later version
 */

class HideElementsPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array('initialize', 'config', 'config_form',
        'install', 'uninstall');

    protected $_filters = array('display_elements');

    protected $_settings;

    public function hookInstall()
    {
        $defaults = array(
            'form' => array(), 'admin' => array(), 'public' => array()
        );
        set_option('hide_elements_settings', json_encode($defaults));
    }

    public function hookUninstall()
    {
        delete_option('hide_elements_settings');
    }

    public function hookInitialize()
    {
        $this->_settings = json_decode(get_option('hide_elements_settings'), true);

        $elementUsers = array('Item', 'File', 'Collection');
        foreach (array_keys($this->_settings['form']) as $elementSet) {
            foreach ($elementUsers as $record) {
                add_filter(array('ElementSetForm', $record, $elementSet), array($this, 'filterElementSetForm'));
            }
        }
    }

    public function hookConfigForm()
    {
        $settings = $this->_settings;

        $table = get_db()->getTable('Element');
        $select = $table->getSelect()
            ->order('elements.element_set_id')
            ->order('ISNULL(elements.order)')
            ->order('elements.order');

        $elements = $table->fetchObjects($select);
        include 'config-form.php';
    }

    public function hookConfig($args)
    {
        $post = $args['post'];
        $settings = array(
            'form' => isset($post['form']) ? $post['form'] : array(),
            'admin' => isset($post['admin']) ? $post['admin'] : array(),
            'public' => isset($post['public']) ? $post['public'] : array()
        );
        set_option('hide_elements_settings', json_encode($settings));
    }

    public function filterDisplayElements($elementsBySet)
    {
        $key = is_admin_theme() ? 'admin' : 'public';
        $itemTypeSetName = ElementSet::ITEM_TYPE_NAME;

        // Account for the renamed Item Type Metadata set.
        foreach ($this->_settings[$key] as $elementSet => $elements) {
            if ($elementSet == $itemTypeSetName) {
                foreach (array_keys($elementsBySet) as $currentSet) {
                    if (substr_compare($currentSet, $itemTypeSetName,
                        -strlen($itemTypeSetName), strlen($itemTypeSetName))
                        === 0
                    ) {
                        $elementSet = $currentSet;
                        break;
                    }
                }
            }

            foreach (array_keys($elements) as $element) {
                unset($elementsBySet[$elementSet][$element]);
            }
        }
        return $elementsBySet;
    }

    public function filterElementSetForm($elements, $args)
    {
        $set = $args['element_set_name'];
        if (isset($this->_settings['form'][$set])) {
            $hideElements = array_keys($this->_settings['form'][$set]);
            foreach ($elements as $key => $element) {
                if (in_array($element->name, $hideElements)) {
                    unset($elements[$key]);
                }
            }
        }
        return $elements;
    }
}
