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
 * Ajax update of the ejsapp files browser html structure, suitable for YUI tree
 *
 * @package    block_ejsapp_file_browser
 * @copyright  2013 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once('renderer.php');

require_login(0, false);

global $OUTPUT, $PAGE;

$tree = new ejsapp_file_browser_tree();
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/ejsapp_file_browser/refresh_tree.php');

if (empty($tree->dir['subdirs']) && empty($tree->dir['files'])) {
    $html = $OUTPUT->box(get_string('nofilesavailable', 'repository'));
} else {
    $html = htmllize_tree($tree, $tree->dir);
}

echo $html;