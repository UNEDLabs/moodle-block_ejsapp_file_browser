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
 * Manage user private area files
 * This file modifies the standard moodle block "private_files" to support
 * the loading of state files for EJSApp simulations
 *     
 * @package    block
 * @subpackage ejsapp_file_browser
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class that defines the EJSApp File Browser block.
 */ 
class block_ejsapp_file_browser extends block_base {

    /**
     * init function for the EJSApp File Browser block
     */
    function init()
    {
        $this->title = get_string('title_of_the_block', 'block_ejsapp_file_browser');
    }

    /**
     * specialization function for the EJSApp File Browser block
     */
    function specialization()
    {
    }

    /**
     * applicable_formats function for the EJSApp File Browser block
     */
    function applicable_formats()
    {
        return array('all' => true);
    }

    /**
     * instance_allow_multiple function for the EJSApp File Browser block
     */
    function instance_allow_multiple()
    {
        return false;
    }

    /**
    * Defines the content of the block.
    *
    * @no params
    * @return array The content of the block
    */
    function get_content()
    {
        global $CFG, $USER, $PAGE, $OUTPUT, $DB;

        if ($this->content !== null) {
            return $this->content;
        }
        if (empty($this->instance)) {
            return null;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        
        if (isloggedin() && !isguestuser()) { // Show the block
            $refresh_button = '<input type="image" id="refreshEJSAppFBBut" align="left" src="' . $CFG->wwwroot . '/blocks/ejsapp_file_browser/pix/refresh.png" name="image" width="25" height="25">';
            $this->content = new stdClass();
            $renderer = $this->page->get_renderer('block_ejsapp_file_browser');
            $this->content->text = $renderer->ejsapp_file_browser_tree(); 
            if (has_capability('moodle/user:manageownfiles', $this->context)) {
            	if ($CFG->version > 2012062500) {  //Moodle 2.3 or higher
                $filespath = '/user/files.php';
              } else {                           //Moodle 2.2 or lower
                $filespath = '/user/filesedit.php';
              }
              $manage_files_button = $OUTPUT->single_button(new moodle_url($filespath, array('returnurl'=>$PAGE->url->out())), get_string('managemyfiles', 'block_ejsapp_file_browser'), 'get');              
           	  $this->content->text .= '<table><tr><td>' . $refresh_button . '</td><td>' . $manage_files_button . '</td></tr></table>';
            } else {
                $this->content->text .= $refresh_button;
            }
        }

        return $this->content;
    }
}

