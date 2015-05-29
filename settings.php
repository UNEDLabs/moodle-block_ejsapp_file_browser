<?php

// EJSApp File Browser is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either moodle_version 3 of the License, or
// (at your option) any later moodle_version.
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
 * File for configuring the auto-refresh of the block
 *
 * @package    block
 * @subpackage ejsapp_file_browser
 * @copyright  2012 Luis de la Torre, Ruben Heradio and Carlos Jara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$settings->add(new admin_setting_heading(
    'auto_refresh_header_config',
    get_string('auto_refresh_header_config', 'block_ejsapp_file_browser'),
    ''
));

$settings->add(new admin_setting_configtext(
    'ejsapp_file_browser/Auto_refresh',
    get_string('auto_refresh', 'block_ejsapp_file_browser'),
    get_string('auto_refresh_description', 'block_ejsapp_file_browser'),
    '0',
    PARAM_INT,
    '5'
));