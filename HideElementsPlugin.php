<?php
/**
 * @package HideElements
 * @copyright Copyright 2013, John Flatness
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPLv3 or any later version
 */

class HideElementsPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array('initialize', 'config', 'config_form',
        'install', 'uninstall', 'upgrade');

    protected $_filters = array('display_elements', 'elements_select_options');

    public function hookInstall()
    {
        $defaults = array(
            'override' => array(),
            'form' => array(),
            'admin' => array(),
            'public' => array(),
            'search' => array(),
        );
        set_option('hide_elements_settings', json_encode($defaults));
    }

    public function hookUninstall()
    {
        delete_option('hide_elements_settings');
    }

    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        $settings = json_decode(get_option('hide_elements_settings'), true);
        if (version_compare($oldVersion, '1.2', '<')) {
            $settings['override'] = array();
            $settings['search'] = array();
        }

        // Convert old-style search hide settings to ID-based storage
        if (version_compare($oldVersion, '1.3', '<')) {
            $newSearch = array();
            $elementTable = $this->_db->getTable('Element');
            foreach ($settings['search'] as $set => $elements) {
                foreach ($elements as $element => $enabled) {
                    $element = $elementTable->findByElementSetNameAndElementName($set, $element);
                    $newSearch[$set][$element->id] = $enabled;
                }
            }
            $settings['search'] = $newSearch;
        }
        set_option('hide_elements_settings', json_encode($settings));
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
            'override' => isset($post['override']) ? $post['override'] : array(),
            'form' => isset($post['form']) ? $post['form'] : array(),
            'admin' => isset($post['admin']) ? $post['admin'] : array(),
            'public' => isset($post['public']) ? $post['public'] : array(),
            'search' => isset($post['search']) ? $post['search'] : array(),
        );
        set_option('hide_elements_settings', json_encode($settings));
    }

    public function filterDisplayElements($elementsBySet)
    {
        if ($this->_overrideFilter()) {
            return $elementsBySet;
        }

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
        if ($this->_overrideFilter()) {
            return $elements;
        }

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

    public function filterElementsSelectOptions($options)
    {
        if ($this->_overrideFilter() || !isset($this->_settings['search'])) {
            return $options;
        }

        $elementSetHeadings = get_option('show_element_set_headings');
        $optgroups = $elementSetHeadings || version_compare(OMEKA_VERSION, '2.2', '<');
        foreach ($this->_settings['search'] as $elementSet => $elements) {
            foreach ($elements as $id => $hidden) {
                if ($optgroups) {
                    unset($options[__($elementSet)][$id]);
                } else {
                    unset($options[$id]);
                }
            }
        }
        return $options;
    }

    /**
     * Override filters for configured user roles.
     */
    protected function _overrideFilter()
    {
        $user = current_user();
        if ($user && in_array($user->role, $this->_settings['override'])) {
            return true;
        }
        return false;
    }
}
