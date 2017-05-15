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
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
//
// EJSApp File Browser has been developed by:
// - Luis de la Torre: ldelatorre@dia.uned.es
// - Ruben Heradio: rheradio@issi.uned.es
//
// at the Computer Science and Automatic Control, Spanish Open University
// (UNED), Madrid, Spain.


/**
 * Prints the private files tree
 *
 * @package    block_ejsapp_file_browser
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Prints the private files tree
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ejsapp_file_browser_renderer extends plugin_renderer_base {

    /**
     * Prints ejsapp file browser tree view
     * @return string Html code that prints the tree view
     */
    public function ejsapp_file_browser_tree() {
        return $this->render(new ejsapp_file_browser_tree());
    }

    /**
     * Prints ejsapp file browser tree view
     * @param ejsapp_file_browser_tree $tree
     * @return string Html code that prints the tree view
     */
    public function render_ejsapp_file_browser_tree(ejsapp_file_browser_tree $tree) {
        global $CFG, $PAGE;
        $htmlid = 'ejsapp_file_browser_tree';
        $url = $CFG->wwwroot . '/blocks/ejsapp_file_browser/refresh_tree.php';
        $this->page->requires->js_init_call('M.block_ejsapp_file_browser.init_reload', array($url, $htmlid));
        if (substr_count($PAGE->url, '/mod/ejsapp/view.php') > 0) {
            $this->page->requires->js_init_call('M.block_ejsapp_file_browser.init_auto_refresh',
                array($url, $htmlid, get_config('block_ejsapp_file_browser', 'Auto_refresh')));
        }
        $this->page->requires->js_init_call('M.block_ejsapp_file_browser.init_tree', array(false, $htmlid));
        $html = '<div id="' . $htmlid . '">';
        $html .= htmllize_tree($tree, $tree->dir);
        $html .= '</div>';

        return $html;
    }

}

/**
 * Auxiliary class to print the private files tree
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ejsapp_file_browser_tree implements renderable {
    /**
     * @var $context
     */
    public $context;
    /**
     * @var $dir
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

/**
 * Public function - creates htmls structure suitable for YUI tree
 *
 * @param ejsapp_file_browser_tree $tree
 * @param array $dir
 * @return string result
 */
function htmllize_tree($tree, $dir) {
    global $CFG, $OUTPUT, $DB, $USER;
    $yuiconfig = array();
    $yuiconfig['type'] = 'html';

    if (empty($dir['subdirs']) and empty($dir['files'])) {
        return '';
    }
    $result = '<ul>';

    foreach ($dir['subdirs'] as $subdir) {
        $image = $OUTPUT->pix_icon(file_folder_icon(), $subdir['dirname'], 'moodle', array('class' => 'icon'));
        $result .= '<li yuiConfig=\'' . json_encode($yuiconfig) . '\'><div>' . $image.s($subdir['dirname']) .
            '</div> ' . htmllize_tree($tree, $subdir) . '</li>';
    }
    foreach ($dir['files'] as $file) {
        $filename = $file->get_filename();
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        // Get $ejsappid.
        $frecord = $DB->get_record('files',
            array('filename' => $filename, 'component' => 'user', 'filearea' => 'private', 'userid' => ($USER->id)));
        if (!$frecord) {
            $source = array();
        } else {
            if ($extension != 'cnt') {
                preg_match('/ejsappid=(\d+)/', $frecord->source, $source);
            }
        }

        if (!empty($source) && ($extension == 'xml' || $extension == 'json' || $extension == 'exp' ||
                $extension == 'rec' || $extension == 'blk')) { // An ejs state, experiment, recording or blockly file.
            $ejsappid = $source[1];
            $ejsapprecord = $DB->get_record('ejsapp', array('id' => $ejsappid));
            if ($ejsapprecord) {
                if ($extension == 'xml' || $extension == 'json') {
                    $image = '<img class="icon" src="' . $CFG->wwwroot .
                        '/blocks/ejsapp_file_browser/pix/icon_state.svg' . '"/>';
                    $url = $CFG->wwwroot . "/mod/ejsapp/view.php?n=" . $ejsappid . "&state_file=" . $frecord->contextid .
                        "/mod_ejsapp/" . $frecord->filearea . "/" . $frecord->itemid . "/" . $frecord->filename;
                } else if ($extension == 'rec') {
                    $image = '<img class="icon" src="' . $CFG->wwwroot .
                        '/blocks/ejsapp_file_browser/pix/icon_recording.svg' . '"/>';
                    $url = $CFG->wwwroot . "/mod/ejsapp/view.php?n=" . $ejsappid . "&rec_file=" . $frecord->contextid .
                        "/mod_ejsapp/" . $frecord->filearea . "/" . $frecord->itemid . "/" . $frecord->filename;
                } else if ($extension == 'blk') {
                    $image = '<img class="icon" src="' . $CFG->wwwroot .
                        '/blocks/ejsapp_file_browser/pix/icon_blockly.svg' . '"/>';
                    $url = $CFG->wwwroot . "/mod/ejsapp/view.php?n=" . $ejsappid . "&blk_file=" . $frecord->contextid .
                        "/mod_ejsapp/" . $frecord->filearea . "/" . $frecord->itemid . "/" . $frecord->filename;
                } else if ($extension == 'exp') {
                    $image = '<img class="icon" src="' . $CFG->wwwroot .
                        '/blocks/ejsapp_file_browser/pix/icon_experiment.svg' . '"/>';
                    $url = $CFG->wwwroot . "/mod/ejsapp/view.php?n=" . $ejsappid . "&exp_file=" . $frecord->contextid .
                        "/mod_ejsapp/" . $frecord->filearea . "/" . $frecord->itemid . "/" . $frecord->filename;
                }
            }
        } else if ($extension == 'cnt') {
            $image = '<img class="icon" src="' . $CFG->wwwroot . '/blocks/ejsapp_file_browser/pix/icon_controller.svg' . '"/>';
            $url = new moodle_url('/pluginfile.php/' . $tree->context->id . '/user/private' .
                $file->get_filepath() . $file->get_filename());
        } else { // A non-state, non-recording, non-controller file.
            $image = $OUTPUT->pix_icon(file_file_icon($file), $filename, 'moodle', array('class' => 'icon'));
            $url = new moodle_url('/pluginfile.php/' . $tree->context->id . '/user/private' .
                $file->get_filepath() . $file->get_filename());
        }
        if (isset($url) && isset($image)) {
            $result .= '<li yuiConfig=\'' . json_encode($yuiconfig) . '\'><div>' .
                html_writer::link($url, $image . $filename) . '</div></li>';
        }
    }
    $result .= '</ul>';
    return $result;
}