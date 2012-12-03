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
 * @copyright  2012 Luis de la Torre, Ruben Heradio and Carlos Jara
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
        if (empty($tree->dir_user['subdirs']) && empty($tree->dir_user['files']) && empty($tree->dir_ejsapp['subdirs']) && empty($tree->dir_ejsapp['files'])) {
            $html = $this->output->box(get_string('nofilesavailable', 'repository'));
        } else {
            $htmlid = 'ejsapp_file_browser_tree_'.uniqid();
            $this->page->requires->js_init_call('M.block_ejsapp_file_browser.init_tree', array(false, $htmlid));
            $html = '<div id="'.$htmlid.'">';
            $html .= $this->htmllize_tree($tree, $tree->dir_user, $tree->dir_ejsapp);
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Internal function - creates htmls structure suitable for YUI tree
     *
     * @param $tree
     * @param $dir_user
     * @param $dir_ejsapp
     */
    protected function htmllize_tree($tree, $dir_user, $dir_ejsapp) {
        global $CFG;
        $yuiconfig = array();
        $yuiconfig['type'] = 'html';

        if (empty($dir_user['subdirs']) and empty($dir_user['files']) and empty($dir_ejsapp['subdirs']) and empty($dir_ejsapp['files'])) {
            return '';
        }
        $result = '<ul>';
        // content = user, filearea = private (directories)
        foreach ($dir_user['subdirs'] as $subdir_user) {
            $image = $this->output->pix_icon("f/folder", $subdir_user['dirname'], 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.$image.' '.s($subdir_user['dirname']).'</div> '.$this->htmllize_tree($tree, $subdir_user).'</li>';
        }
        // content = mod_ejsapp, filearea = private (directories)
        /*foreach ($dir_ejsapp['subdirs'] as $subdir_ejsapp) {
            $image = $this->output->pix_icon("f/folder", $subdir_ejsapp['dirname'], 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.$image.' '.s($subdir_ejsapp['dirname']).'</div> '.$this->htmllize_tree($tree, $subdir_ejsapp).'</li>';
        }*/
        // content = user, filearea = private (files)
        foreach ($dir_user['files'] as $file) {
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/'.$tree->context->id.'/user/private'.$file->get_filepath().$file->get_filename(), true);
            $filename = $file->get_filename();
            $icon = mimeinfo("icon", $filename);
            $image = $this->output->pix_icon("f/$icon", $filename, 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.html_writer::link($url, $image.'&nbsp;'.$filename).'</div></li>';
        }
        // content = mod_ejsapp, filearea = private (files)
        /*foreach ($dir_ejsapp['files'] as $file) {
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/'.$tree->context->id.'/user/private'.$file->get_filepath().$file->get_filename(), true);
            $filename = $file->get_filename();
            $icon = mimeinfo("icon", $filename);
            $image = $this->output->pix_icon("f/$icon", $filename, 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.html_writer::link($url, $image.'&nbsp;'.$filename).'</div></li>';
        }*/
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
        $this->context = get_context_instance(CONTEXT_USER, $USER->id);
        $fs = get_file_storage();
        $this->dir_user = $fs->get_area_tree($this->context->id, 'user', 'private', 0);
        $this->dir_ejsapp = $fs->get_area_tree($this->context->id, 'mod_ejsapp', 'private', 0);
    }
}