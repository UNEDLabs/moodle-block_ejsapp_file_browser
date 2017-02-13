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
        global $CFG, $PAGE, $OUTPUT, $DB, $USER;

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
            $this->content->items[0] = html_writer::tag('input', '', array('type'=>'image', 'id'=>'refreshEJSAppFBBut', 'src'=>$CFG->wwwroot.'/blocks/ejsapp_file_browser/pix/refresh.png', 'width'=>'25', 'height'=>'25'));
            $renderer = $this->page->get_renderer('block_ejsapp_file_browser');
            $this->content->items[1] = $renderer->ejsapp_file_browser_tree();
            if (has_capability('moodle/user:manageownfiles', $this->context)) {
                $content = $OUTPUT->single_button(new moodle_url('/user/files.php', array('returnurl'=>$PAGE->url->out())), get_string('managemyfiles', 'block_ejsapp_file_browser'), 'get');
                $this->content->items[2] =  html_writer::div($content, 'managefiles');
                if (strpos($PAGE->url, 'mod/ejsapp/view.php') !== false) { //inside an ejsapp activity
                    $param_init_buttons_states = array(false);
                    if (isset($_GET['rec_file'])) {
                        $param_init_buttons_states = array(true);
                    }
                    $this->page->requires->js_call_amd('block_ejsapp_file_browser/buttons_states', 'init', $param_init_buttons_states);
                    // <Blockly buttons>
                    $id = optional_param('id', null, PARAM_TEXT);
                    $n = optional_param('n', null, PARAM_INT);
                    $applet = 1;
                    $blockly_conf = false;
                    if ($id) {
                        $cm = get_coursemodule_from_id('ejsapp', $id, 0, false, MUST_EXIST);
                        $ejsapp_id = $cm->instance;
                        $applet = $DB->get_field('ejsapp', 'applet', array('id' => $ejsapp_id));
                        $blockly_conf = $DB->get_field('ejsapp', 'blockly_conf', array('id' => $ejsapp_id));
                    } elseif (isset($n)) {
                        $ejsapp_id = $n;
                        $applet = $DB->get_field('ejsapp', 'applet', array('id' => $ejsapp_id));
                        $blockly_conf = $DB->get_field('ejsapp', 'blockly_conf', array('id' => $ejsapp_id));
                    }
                    if ($blockly_conf != false && $applet == 0) {
                        $blockly_conf = json_decode($blockly_conf);
                        if ($blockly_conf[0] == 1) {
                            // Show buttons
                            $this->content->footer = html_writer::start_tag('fieldset') . html_writer::tag('legend', get_string('blockly_legend', 'block_ejsapp_file_browser'), array('class' => 'legend')) .
                                html_writer::div(html_writer::tag('button', get_string('show_blockly_options', 'block_ejsapp_file_browser'), array('class' =>'show_button', 'id' => 'show_blockly')) . html_writer::tag('button', get_string('hide_blockly_options', 'block_ejsapp_file_browser'), array('class' =>'hide_button', 'id' => 'hide_blockly')), 'optionsBut') .
                                html_writer::end_tag('fieldset');
                            $content1 = html_writer::empty_tag('input', array('class' =>'blockly_button', 'type' => 'submit', 'name' => 'runCode', 'id' => 'runCodeBut', 'value' => get_string('run_code', 'block_ejsapp_file_browser'), 'onclick'=> 'playCode()'));
                            $content = html_writer::div($content1, 'runCode');
                            $context = context_user::instance($USER->id);
                            $saveCodeParams =  $context->id . ',' . $USER->id . ',' . $ejsapp_id;
                            $content2 = html_writer::empty_tag('input', array('class' =>'blockly_button', 'type' => 'submit', 'name' => 'saveCode', 'id' => 'saveCodeBut', 'value' => get_string('save_code', 'block_ejsapp_file_browser'), 'onclick'=> 'saveCode(' . $saveCodeParams . ')')) .
                                html_writer::empty_tag('input', array('class' =>'blockly_button', 'type' => 'submit', 'name' => 'loadCode', 'id' => 'loadCodeBut', 'value' => get_string('load_code', 'block_ejsapp_file_browser'), 'onclick'=> 'loadCode()'));
                            $content .= html_writer::div($content2, 'saveLoadCode');
                            $this->content->footer .= html_writer::div($content, 'blocklyControl', array('id' => 'blocklyControl', 'style' => 'display:none'));
                        }
                    }
                    // </Blockly buttons>
                    // <Recording interaction buttons>
                    $this->content->footer .= html_writer::start_tag('fieldset') . html_writer::tag('legend', get_string('capture_legend', 'block_ejsapp_file_browser'), array('class' => 'legend')) .
                        html_writer::div(html_writer::tag('button', get_string('show_capture_options', 'block_ejsapp_file_browser'), array('class' =>'show_button', 'id' => 'show_interaction')) . html_writer::tag('button', get_string('hide_capture_options', 'block_ejsapp_file_browser'), array('class' =>'hide_button', 'id' => 'hide_interaction')), 'optionsBut') .
                        html_writer::end_tag('fieldset');
                    $content1 = html_writer::empty_tag('input', array('class' =>'recording_button', 'type' => 'submit', 'name' => 'startCapture', 'id' => 'startCaptureBut', 'value' => get_string('start_capture', 'block_ejsapp_file_browser'))) .
                        html_writer::empty_tag('input', array('class' =>'recording_button', 'type' => 'submit', 'name' => 'stopCapture', 'id' => 'stopCaptureBut', 'value' => get_string('stop_capture', 'block_ejsapp_file_browser'))) .
                        html_writer::empty_tag('input', array('class' =>'recording_button', 'type' => 'submit', 'name' => 'resetCapture', 'id' => 'resetCaptureBut', 'value' => get_string('reset_capture', 'block_ejsapp_file_browser')));
                    $content = html_writer::div($content1, 'recordCapture');
                    $content2 = html_writer::empty_tag('input', array('class' =>'recording_button', 'type' => 'submit', 'name' => 'playCapture', 'id' => 'playCaptureBut', 'value' => get_string('play_capture', 'block_ejsapp_file_browser'))) .
                        html_writer::label(get_string('change_speed', 'block_ejsapp_file_browser'), 'stepCapture', true, array('class' => 'velocity')) .
                        html_writer::empty_tag('input', array('type' => 'range', 'class' => 'stepCapture', 'name' => 'stepCapture', 'id' => 'stepCaptureBut', 'value' => '0', 'step' => '0.5', 'min' => '-4', 'max' => '4'));
                    $content .= html_writer::div($content2, 'playCapture');
                    $this->content->footer .=  html_writer::div($content, 'captureInteraction', array('id' => 'captureInteraction', 'style' => 'display:none'));
                    // </Recording interaction buttons>
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