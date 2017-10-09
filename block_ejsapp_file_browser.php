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
 * Manage user private area files with support for files generated in the EJSApp activity.
 *
 * @package    block_ejsapp_file_browser
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class that defines the EJSApp File Browser block.
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ejsapp_file_browser extends block_list {

    /**
     * Init function for the EJSApp File Browser block
     *
     */
    public function init() {
        $this->title = get_string('title_of_the_block', 'block_ejsapp_file_browser');
    }

    /**
     * Specialization function for the EJSApp File Browser block
     *
     */
    public function specialization() {
    }

    /**
     * applicable_formats function for the EJSApp File Browser block
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }

    /**
     * Add custom html attributes to aid with theming and styling
     *
     * @return array The content of the block
     */
    public function html_attributes() {
        $attributes = parent::html_attributes();
        $attributes['class'] .= ' block_'. $this->name(); // Append our class to class attribute.
        return $attributes;
    }

    /**
     * instance_allow_multiple function for the EJSApp File Browser block
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Defines the content of the block.
     *
     * @return null|stdClass|stdObject The content of the block
     */
    public function get_content() {
        global $CFG, $PAGE, $OUTPUT, $DB,$USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            return null;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (isloggedin() && !isguestuser()) { // Show the block.
            $this->content->items[0] = html_writer::tag('input', '',
                array('type' => 'image', 'id' => 'refreshEJSAppFBBut', 'src' => $CFG->wwwroot .
                    '/blocks/ejsapp_file_browser/pix/refresh.png', 'width' => '25', 'height' => '25'));
            $renderer = $this->page->get_renderer('block_ejsapp_file_browser');
            $this->content->items[1] = $renderer->ejsapp_file_browser_tree();
            if (has_capability('moodle/user:manageownfiles', $this->context)) {
                $content = $OUTPUT->single_button(new moodle_url('/user/files.php',
                    array('returnurl' => $PAGE->url->out())), get_string('managemyfiles', 'block_ejsapp_file_browser'), 'get');
                $this->content->items[2] = html_writer::div($content, 'managefiles');

                //Colocar enlace compartir Ficheros
                $contexto=context_course::instance($PAGE->course->id);
                $urlnew = new moodle_url('/blocks/ejsapp_file_browser/share_files.php',
                    array('blockid' => $this->instance->id, 'courseid' => $PAGE->course->id, 'contextid' => $contexto->id, 'sesskey' => sesskey()));
                $this->content->items[6] = html_writer::link($urlnew,
                    '<img src="http://localhost/blocks/ejsapp_file_browser/pix/files.gif" alt="files">'.'&nbsp;'.get_string('sharefiles', 'block_ejsapp_file_browser'));
                //Fin enlace compartir ficheros

                //Colocar enlace mis archivos compartidos
				 //consulta si el ultimo acceso del usuario es anterior a la fecha en que le compartieron el archivo

				$rec_user= $DB->get_record('user', array('id'=>$USER->id)); //obtiene el registro del usuario para conocer el ultimo acceso

				$sql='Select COUNT(id) from {block_ejsapp_shared_files} where sharedwithuserid= ? and timemodified >= ?';

				$lastfileshared= $DB->count_records_sql($sql, array($USER->id,$rec_user->lastlogin));


				//$contexto=context_course::instance($PAGE->course->id);
                $urlnew = new moodle_url('/blocks/ejsapp_file_browser/myshare_files.php',
                    array('blockid' => $this->instance->id, 'courseid' => $PAGE->course->id, 'contextid' => $contexto->id, 'sesskey' => sesskey()));


				if ($lastfileshared > 0){

				//if ($nuevo){
				  $this->content->items[7] = html_writer::link($urlnew,
                    '<img src="http://localhost/blocks/ejsapp_file_browser/pix/files.gif" alt="files">'.'&nbsp;'.get_string('mysharefiles','block_ejsapp_file_browser').'&nbsp;'.'<img src="http://localhost/blocks/ejsapp_file_browser/pix/new.gif" alt="Nuevo">');

				}else
					$this->content->items[7] = html_writer::link($urlnew, '<img src="http://localhost/blocks/ejsapp_file_browser/pix/files.gif" alt="files">'.'&nbsp;'.get_string('mysharefiles', 'block_ejsapp_file_browser'));
                //        enlace mis archivos

                if (strpos($PAGE->url, 'mod/ejsapp/view.php') !== false) { // Inside an ejsapp activity.
                    $butstates = array(false);
                    if (isset($_GET['rec_file'])) {
                        $butstates = array(true);
                    }
                    $this->page->requires->js_call_amd('block_ejsapp_file_browser/buttons_states', 'init',
                        $butstates);
                    // Init of blockly buttons.
                    $id = optional_param('id', null, PARAM_TEXT);
                    $n = optional_param('n', null, PARAM_INT);
                    $blocklyconf = false;
                    if ($id) {
                        $cm = get_coursemodule_from_id('ejsapp', $id, 0, false, MUST_EXIST);
                        $ejsappid = $cm->instance;
                        $blocklyconf = $DB->get_field('ejsapp', 'blockly_conf', array('id' => $ejsappid));
                    } else if (isset($n)) {
                        $ejsappid = $n;
                        $blocklyconf = $DB->get_field('ejsapp', 'blockly_conf', array('id' => $ejsappid));
                    }
                    if ($blocklyconf != false) {
                        $blocklyconf = json_decode($blocklyconf);
                        if ($blocklyconf[0] == 1) {
                            // Show buttons.
                            $this->content->footer = html_writer::start_tag('fieldset') .
                                html_writer::tag('legend',
                                    get_string('blockly_legend', 'block_ejsapp_file_browser'),
                                    array('class' => 'legend')) .
                                html_writer::div(html_writer::tag('button',
                                        get_string('show_blockly_options', 'block_ejsapp_file_browser'),
                                        array('class' => 'show_button', 'id' => 'show_blockly')) .
                                    html_writer::tag('button',
                                        get_string('hide_blockly_options', 'block_ejsapp_file_browser'),
                                        array('class' => 'hide_button', 'id' => 'hide_blockly')), 'optionsBut') .
                                html_writer::end_tag('fieldset');
                            $content1 = html_writer::empty_tag('input',
                                    array('class' => 'blockly_button', 'type' => 'submit',
                                        'name' => 'runCode', 'id' => 'runCodeBut',
                                        'value' => get_string('run_code', 'block_ejsapp_file_browser'),
                                        'onclick' => 'playCode()'));
                            $content = html_writer::div($content1, 'runCode');
                            $content2 = html_writer::empty_tag('input',
                                    array('class' => 'blockly_button', 'type' => 'submit',
                                        'name' => 'saveCode', 'id' => 'saveCodeBut',
                                        'value' => get_string('save_code', 'block_ejsapp_file_browser'),
                                        'onclick' => 'saveCode()')) .
                                html_writer::empty_tag('input',
                                    array('class' => 'blockly_button', 'type' => 'submit',
                                        'name' => 'loadCode', 'id' => 'loadCodeBut',
                                        'value' => get_string('load_code', 'block_ejsapp_file_browser'),
                                        'onclick' => 'loadCode()'));
                            $content .= html_writer::div($content2, 'saveLoadCode');
                            $this->content->footer .= html_writer::div($content, 'blocklyControl',
                                array('id' => 'blocklyControl', 'style' => 'display:none'));
                        }
                    }
                    // End of blockly buttons.
                    // Init of buttons for recording the user interaction.
                    $this->content->footer .= html_writer::start_tag('fieldset') .
                        html_writer::tag('legend', get_string('capture_legend', 'block_ejsapp_file_browser'),
                            array('class' => 'legend')) .
                        html_writer::div(html_writer::tag('button',
                                get_string('show_capture_options', 'block_ejsapp_file_browser'),
                                array('class' => 'show_button', 'id' => 'show_interaction')) .
                            html_writer::tag('button',
                                get_string('hide_capture_options', 'block_ejsapp_file_browser'),
                                array('class' => 'hide_button', 'id' => 'hide_interaction')), 'optionsBut') .
                        html_writer::end_tag('fieldset');
                    $content1 = html_writer::empty_tag('input',
                            array('class' => 'recording_button', 'type' => 'submit',
                                'name' => 'startCapture', 'id' => 'startCaptureBut',
                                'value' => get_string('start_capture', 'block_ejsapp_file_browser'))) .
                        html_writer::empty_tag('input',
                            array('class' => 'recording_button', 'type' => 'submit',
                                'name' => 'stopCapture', 'id' => 'stopCaptureBut',
                                'value' => get_string('stop_capture', 'block_ejsapp_file_browser'))) .
                        html_writer::empty_tag('input',
                            array('class' => 'recording_button', 'type' => 'submit',
                                'name' => 'resetCapture', 'id' => 'resetCaptureBut',
                                'value' => get_string('reset_capture', 'block_ejsapp_file_browser')));
                    $content = html_writer::div($content1, 'recordCapture');
                    $content2 = html_writer::empty_tag('input',
                            array('class' => 'recording_button', 'type' => 'submit',
                                'name' => 'playCapture', 'id' => 'playCaptureBut',
                                'value' => get_string('play_capture', 'block_ejsapp_file_browser'))) .
                        html_writer::label(get_string('change_speed', 'block_ejsapp_file_browser'),
                            'stepCapture', true, array('class' => 'velocity')) .
                        html_writer::empty_tag('input',
                            array('type' => 'range', 'class' => 'stepCapture', 'name' => 'stepCapture', 'id' => 'stepCaptureBut',
                                'value' => '0', 'step' => '0.5', 'min' => '-4', 'max' => '4'));
                    $content .= html_writer::div($content2, 'playCapture');
                    $content3 = html_writer::div($content3, 'recordCapture');
                    $this->content->footer .= html_writer::div($content, 'captureInteraction',
                        array('id' => 'captureInteraction', 'style' => 'display:none'));
                    // End of buttons for recording the user interaction.
                }
            }
        }

        return $this->content;
    }

    /**
     * Enabling global configuration
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

}
