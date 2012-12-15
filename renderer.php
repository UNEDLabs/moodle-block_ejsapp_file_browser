<?php

// This file is part of the Moodle block "EJSApp File Browser"
//
// EJSApp File Browser is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// EJSApp File Browser is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License is available on <http://www.gnu.org/licenses/>
//
// EJSApp File Browser has been developed by:
//  - Luis de la Torre: ldelatorre@dia.uned.es
//	- Ruben Heradio: rheradio@issi.uned.es
//
//  at the Computer Science and Automatic Control, Spanish Open University
//  (UNED), Madrid, Spain


/**
 * Prints the private files tree
 *     
 * @package    block
 * @subpackage ejsapp_file_browser
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

/**
 * Prints the private files tree
 */
class block_ejsapp_file_browser_renderer extends plugin_renderer_base {

    /**
     * Prints ejsapp file browser tree view
     * @return string Html code that prints the tree view
     */
    public function ejsapp_file_browser_tree() {
        return $this->render(new ejsapp_file_browser_tree);
    }

    /**
     * Prints ejsapp file browser tree view
     * @return string Html code that prints the tree view
     *
     * @param ejsapp_file_browser_tree $tree
     */
    public function render_ejsapp_file_browser_tree(ejsapp_file_browser_tree $tree) {
        $module = array('name'=>'block_ejsapp_file_browser', 'fullpath'=>'/blocks/ejsapp_file_browser/module.js', 'requires'=>array('yui2-treeview'));
        if (empty($tree->dir['subdirs']) && empty($tree->dir['files'])) {
            $html = $this->output->box(get_string('nofilesavailable', 'repository'));
        } else {
            $htmlid = 'ejsapp_file_browser_tree_'.uniqid();
            $this->page->requires->js_init_call('M.block_ejsapp_file_browser.init_tree', array(false, $htmlid));
            $html = '<div id="'.$htmlid.'">';
            $html .= $this->htmllize_tree($tree, $tree->dir);
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Internal function - creates htmls structure suitable for YUI tree
     *
     * @param $tree
     * @param $dir
     */
    protected function htmllize_tree($tree, $dir) {
        global $CFG;
        $yuiconfig = array();
        $yuiconfig['type'] = 'html';

        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }
        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon(file_folder_icon(), $subdir['dirname'], 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.$image.s($subdir['dirname']).'</div> '.$this->htmllize_tree($tree, $subdir).'</li>';
        }
        foreach ($dir['files'] as $file) {
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/'.$tree->context->id.'/user/private'.$file->get_filepath().$file->get_filename(), true);
            $filename = $file->get_filename();
            $image = $this->output->pix_icon(file_file_icon($file), $filename, 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.html_writer::link($url, $image.$filename).'</div></li>';
        }
        $result .= '</ul>';

        return $result;
    }
}

/**
 * Auxilar class to Print the private files tree
 */
class ejsapp_file_browser_tree implements renderable {
    /**
     * context
     */
    public $context;
    /**
     * dir
     */
    public $dir;
    /**
     * __construct
     */
    public function __construct() {
        global $USER;
        $this->context = context_user::instance($USER->id);
        $fs = get_file_storage();
        $this->dir = $fs->get_area_tree($this->context->id, 'user', 'private', 0);
    }
}