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
class block_ejsapp_file_browser extends block_list {

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
     * Add custom html attributes to aid with theming and styling
     *
     * @return array
     */
    function html_attributes() {
        $attributes = parent::html_attributes();
        $attributes['class'] .= ' block_'. $this->name(); // Append our class to class attribute
        return $attributes;
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
    * @return stdClass The content of the block
    */
    function get_content()
    {
        global $CFG, $PAGE, $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

        if (empty($this->instance)) {
            return null;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        
        if (isloggedin() && !isguestuser()) { // Show the block
            $this->content = new stdClass();
            $this->content->items[0] = html_writer::tag('input', '', array('type'=>'image', 'id'=>'refreshEJSAppFBBut', 'src'=>$CFG->wwwroot.'/blocks/ejsapp_file_browser/pix/refresh.png', 'width'=>'25', 'height'=>'25'));
            $renderer = $this->page->get_renderer('block_ejsapp_file_browser');
            $this->content->items[1] = $renderer->ejsapp_file_browser_tree();
            if (has_capability('moodle/user:manageownfiles', $this->context)) {
                $this->content->items[2] = $OUTPUT->single_button(new moodle_url('/user/files.php', array('returnurl'=>$PAGE->url->out())), get_string('managemyfiles', 'block_ejsapp_file_browser'), 'get');
                if (strpos($PAGE->url, 'mod/ejsapp/view.php') !== false) { //inside an ejsapp activity
                    $param_init_buttons_states = array(false);
                    if (isset($_GET['rec_file'])) {
                        $param_init_buttons_states = array(true);
                    }
                    $this->page->requires->js_call_amd('block_ejsapp_file_browser/buttons_states', 'init', $param_init_buttons_states);
                    $this->content->footer = html_writer::start_tag('fieldset') . html_writer::tag('legend', get_string('capture_legend', 'block_ejsapp_file_browser'), array('class' => 'recording')) .
                        html_writer::div(html_writer::tag('button', get_string('show_capture_options', 'block_ejsapp_file_browser'), array('id' => 'show')) . html_writer::tag('button', get_string('hide_capture_options', 'block_ejsapp_file_browser'), array('id' => 'hide')), 'optionsBut') .
                        html_writer::end_tag('fieldset');
                    $content1 = html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'startCapture', 'id' => 'startCaptureBut', 'value' => get_string('start_capture', 'block_ejsapp_file_browser'))) .
                        html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'stopCapture', 'id' => 'stopCaptureBut', 'value' => get_string('stop_capture', 'block_ejsapp_file_browser'))) .
                        html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'resetCapture', 'id' => 'resetCaptureBut', 'value' => get_string('reset_capture', 'block_ejsapp_file_browser')));
                    $content = html_writer::div($content1, 'recordCapture');
                    $content2 = html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'playCapture', 'id' => 'playCaptureBut', 'value' => get_string('play_capture', 'block_ejsapp_file_browser'))) .
                        html_writer::label(get_string('change_speed', 'block_ejsapp_file_browser'), 'stepCapture', true, array('class' => 'velocity')) .
                        html_writer::empty_tag('input', array('type' => 'range', 'class' => 'stepCapture', 'name' => 'stepCapture', 'id' => 'stepCaptureBut', 'value' => '0', 'step' => '0.5', 'min' => '-4', 'max' => '4'));
                    $content .= html_writer::div($content2, 'playCapture');
                    $content = html_writer::div($content, 'captureInteraction', array('id' => 'captureInteraction', 'style' => 'display:none'));
                    $this->content->footer .= $content;
                }
            }
        }

        return $this->content;
    }

    /**
     * enabling global configuration
     */
    function has_config() {return true;}

}