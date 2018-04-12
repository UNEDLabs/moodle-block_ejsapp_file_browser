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
 * English labels for the ejsapp_file_browser block
 *
 * @package    block_ejsapp_file_browser
 * @copyright  2012 Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['title_of_the_block'] = 'EJSApp File Browser';
$string['managemyfiles'] = 'Manage my files';
$string['sharefiles'] = 'Share files';
$string['pluginname'] = 'EJSApp "private files" browser';
$string['privatefiles'] = 'Private files';

$string['blockly_legend'] = 'Blockly';
$string['show_blockly_options'] = 'Show options';
$string['hide_blockly_options'] = 'Hide options';
$string['run_code'] = 'Run code';
$string['save_code'] = 'Save code';
$string['load_code'] = 'Load code';

$string['capture_legend'] = 'Recording';
$string['show_capture_options'] = 'Show options';
$string['hide_capture_options'] = 'Hide options';
$string['start_capture'] = 'Start';
$string['stop_capture'] = 'Stop';
$string['reset_capture'] = 'Reset';
$string['play_capture'] = 'Load';
$string['change_speed'] = 'Velocity:';

// Strings in settings.php.
$string['auto_refresh_header_config'] = 'Configure the block\'s auto-refresh property';
$string['auto_refresh'] = 'Auto-refresh frequency';
$string['auto_refresh_description'] = 'Time in miliseconds. Write "0" to disable auto-refresh.';

// Strings for capabilities.
$string['ejsapp_file_browser:addinstance'] = 'Add a new private files block for EJSApp';
$string['ejsapp_file_browser:myaddinstance'] = 'Add a new private files block for EJSApp to My home';

// Strings for shared_files_usr.php and share_files.php.
$string['files_users_selection'] = 'Select the files and users';
$string['files_selection'] = 'Select files for sharing';
$string['select_share_files'] = 'Share file/s';
$string['share'] = 'Share';
$string['shared_files'] = 'Shared files';
$string['continue'] = 'Continue';
$string['you_share_file'] = 'You shared the file';
$string['you_share_files'] = 'You shared the files';
$string['with_participants'] = 'With the participants';
$string['no_file_selected'] = 'No files selected';
$string['message_subject'] = 'File sharing request';
$string['full_message_1'] = ' wants to share some files with you: ' . "\r\n" . "\n";
$string['full_message_2'] = "\r\n" . "\n" . 'You can either accept (';
$string['full_message_3'] = '), reject (';
$string['full_message_4'] = ') or ignore this request.';
$string['full_message_html_1'] = ' wants to share some files with you: ';
$string['full_message_html_2'] = 'You can either ';
$string['full_message_html_3'] = 'accept';
$string['full_message_html_4'] = 'reject';
$string['full_message_html_5'] ='or ignore this request.';
$string['small_message_1'] = 'I want to share these files with you: ';
$string['small_message_2'] = 'Accept';
$string['small_message_3'] = 'Reject';

// Strings for action.php.
$string['message_subject_response'] = 'Response to your file sharing request';
$string['full_message_accepted'] = ' accepted your files.';
$string['full_html_message_accepted'] = ' accepted your files.';
$string['small_message_accepted'] = 'I just accepted your files.';
$string['full_message_rejected'] = ' rejected your files.';
$string['full_html_message_rejected'] =  ' rejected your files.';
$string['small_message_rejected'] = 'I just rejected your files.';
$string['received_files_1'] = 'The files from ';
$string['received_files_2'] = ' have been received.';
$string['no_pending_files'] = 'There are no pending files to be received from ';