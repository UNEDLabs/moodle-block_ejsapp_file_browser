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
 * Ajax update of the ejsapp files browser html structure, suitable for YUI tree
 *  
 * @package    block
 * @subpackage ejsapp_file_browser
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */

//defined('MOODLE_INTERNAL') || die();

require_once('../../config.php'); 

require_login(0, false);

$htmlid = required_param('htmlid', PARAM_TEXT);

//$context = get_context_instance(CONTEXT_USER, $USER->id);
$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);
$content = new stdClass();
$renderer = $PAGE->get_renderer('block_ejsapp_file_browser'); 
$content->text = $renderer->ejsapp_file_browser_tree($htmlid);

//Delete the repeated <div id=> and last </div>
$content->text = str_replace('<div id="'.$htmlid.'">', '', $content->text);
$content->text = substr_replace($content->text, '', -6);

include('process_state_files.php');
$content->text = process_state_files($content->text);

echo $content->text; 

?>