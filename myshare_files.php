<?php
// This file is part of the Moodle block "EJSApp file browser system"
//
// EJSApp file browser system is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// file browser system is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
//
// The function for shared files in the EJSApp  file browser system has been developed by:
// - Arnoldo Fernandez: arnoldofernandez@gmail.com
// - María Masanet: mimasanet@gmail.com
//
// at the University National of San Juan
// (UNSJ), San Juan, Argentina.

/**
 * Page for setting the users shared files with other users
 *
 * @package    block_ejsapp_file_browser
 * @copyright  2017 Arnoldo Fernandez y María Masanet
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $CFG, $PAGE, $DB, $OUTPUT, $USER;

require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot . '/filter/multilang/filter.php');
require_once($CFG->dirroot.'/blocks/ejsapp_file_browser/renderer.php');

define('DEFAULT_PAGE_SIZE', 20);
$courseid = required_param('courseid', PARAM_INT);
$contextid = required_param('contextid', PARAM_INT);

$page = optional_param('page', 0, PARAM_INT);                       // Which page to show.
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$accesssince = optional_param('accesssince', 0, PARAM_INT);         // Filter by last access. -1 = never.
$search = optional_param('search', '', PARAM_RAW); // Make sure it is processed with p() or s() when sending.
$roleid = optional_param('roleid', 0, PARAM_INT);  // 0 means all enrolled users (or all on the frontpage).

$PAGE->set_context(context_course::instance($courseid));

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login();

$context = context_course::instance($courseid);

$title = get_string('mysharefiles', 'block_ejsapp_file_browser');

$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_url('/block/block_ejsapp_file_browser/share_files.php', array('courseid' => $courseid, 'contextid' => $context->id));
$courseurl = new moodle_url("$CFG->wwwroot/course/view.php?id=$courseid");

$PAGE->navbar->add('<a href="'.$courseurl.'">'.$course->shortname.'</a>');
$PAGE->navbar->add($title);
$PAGE->set_pagelayout('incourse');

$rolenamesurl = new moodle_url("$CFG->wwwroot/blocks/ejsapp_file_browser/share_files.php?courseid=$courseid
&contextid=$contextid&sifirst=&silast=");

$countries = get_string_manager()->get_list_of_countries();

$strnever = get_string('never');

$datestring = new stdClass();
$datestring->year  = get_string('year');
$datestring->years = get_string('years');
$datestring->day   = get_string('day');
$datestring->days  = get_string('days');
$datestring->hour  = get_string('hour');
$datestring->hours = get_string('hours');
$datestring->min   = get_string('min');
$datestring->mins  = get_string('mins');
$datestring->sec   = get_string('sec');
$datestring->secs  = get_string('secs');

// Check to see if groups are being used in this course
// and if so, set $currentgroup to reflect the current group.

$groupmode    = groups_get_course_groupmode($course); // Groups are being used.
$currentgroup = groups_get_course_group($course, true);

if (!$currentgroup) { // To make some other functions work better later.
    $currentgroup  = null;
}

$isseparategroups = ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));

if ($course->id === SITEID) {
    $PAGE->navbar->ignore_active();
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('users_shared', 'block_ejsapp_file_browser'));

echo '<div class="userlist">';

if ($isseparategroups and (!$currentgroup) ) {
        // The user is not in the group so show message and exit.
        echo $OUTPUT->heading(get_string("notingroup"));
        echo $OUTPUT->footer();
        exit;
}

    // Should use this variable so that we don't break stuff every time a variable is added or changed.
$baseurl = new moodle_url('/blocks/ejsapp_file_browser/myshare_files.php', array(
          'courseid' => $courseid,
 		  'contextid' => $contextid,
          'roleid' => $roleid,
          'perpage' => $perpage,
          'accesssince' => $accesssince,
          'search' => s($search)));

    // Setting up tags.
if ($course->id == SITEID) {
        $filtertype = 'site';
    } else if ($course->id && !$currentgroup) {
        $filtertype = 'course';
        $filterselect = $course->id;
    } else {
        $filtertype = 'group';
        $filterselect = $currentgroup;
}

    // Print settings and things in a table across the top.
$controlstable = new html_table();
$controlstable->attributes['class'] = 'controls';
$controlstable->data[] = new html_table_row();

if (!isset($hiddenfields['lastaccess'])) {

    $minlastaccess = $DB->get_field_sql('SELECT min(lastaccess)
                                             FROM {user}
                                             WHERE lastaccess != 0');
    $lastaccess0exists = $DB->record_exists('user', array('lastaccess' => 0));

    $now = usergetmidnight(time());
    $timeaccess = array();
    $baseurl->remove_params('accesssince');
} // End of: if (!isset($hiddenfields['lastaccess'])).
 
    // Define a table showing a list of users in the current role selection.
    $tablecolumns = array( 'picture','user', 'file','date');
    $tableheaders = array('',get_string('fullname'),get_string('file'),  get_string('date'));

    $table = new flexible_table('user-index-participants-' . $course->id);

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($baseurl->out());

    if (!isset($hiddenfields['lastaccess'])) {
        $table->sortable(true, 'date', SORT_DESC);
    }
   
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('align', 'center');
    $table->set_attribute('id', 'participants');
    $table->set_attribute('class', 'generaltable generalbox');

    $table->set_control_variables(array(
                TABLE_VAR_SORT      => 'ssort',
                TABLE_VAR_HIDE      => 'shide',
                TABLE_VAR_SHOW      => 'sshow',
                TABLE_VAR_IFIRST    => 'sifirst',
                TABLE_VAR_ILAST     => 'silast',
                TABLE_VAR_PAGE      => 'spage'
    ));
    $table->setup();

    // We are looking for all users with this role assigned in this context or higher.
    
   $dbtable = 'block_ejsapp_shared_files'; ///name of table
   $conditions = array('sharedwithuserid'=>$USER->id); ///the name of the field (key) and the desired value
   $sort = 'timemodified'; //field or fields you want to sort the result by
   $fields = 'fileid, timemodified'; ///list of fields to return

   $result = $DB->get_records_menu($dbtable,$conditions,$sort,$fields);

    if ($table->get_sql_sort()) {
        $sort = ' ORDER BY ' . $table->get_sql_sort();
    } else {
        $sort = '';
    }

    $matchcount = count($result);
    $table->initialbars(true);
    $table->pagesize($perpage, $matchcount);

    $sqljoin= 'SELECT * FROM mdl_block_ejsapp_shared_files as sf inner join moodle.mdl_files as f on sf.fileid= f.id where f.userid=?';
	
    $records = $DB->get_records_sql($sqljoin, array($USER->id));

    echo "<form action=\"$courseurl\" method=\"post\" id=\"participantsform\">" . '<div>';
   
    $timeformat = get_string('strftimedate');
	
    if ($records) {
        foreach ($records as $file) {
			$user= $DB->get_record('user', array('id'=>$file->sharedwithuserid));
            $usercontext = context_user::instance($user->id);
			if (!empty($user)){

            if (!isset($user->firstnamephonetic)) {
                $user->firstnamephonetic = $user->firstname;
            }
            if (!isset($user->lastnamephonetic)) {
                $user->lastnamephonetic = $user->lastname;
            }

            if ($piclink = ($USER->id == $user->id || has_capability('moodle/user:viewdetails', $context) ||
                has_capability('moodle/user:viewdetails', $usercontext))) {
                $profilelink = '<strong><a href="' . $CFG->wwwroot . '/user/view.php?id=' . $user->id . '&amp;course=' .
                    $course->id . '">' . fullname($user) . '</a></strong>';
            } else {
                $profilelink = '<strong>' . fullname($user) . '</strong>';
            }

            $data = array ($OUTPUT->user_picture($user, array('size' => 35, 'courseid' => $course->id)), $profilelink);

			}
			//create link with file name
			$usercontext2 = context_user::instance($file->userid);
			$url = new moodle_url('/pluginfile.php/' . $usercontext2->id . '/user/private'.$file->filepath . $file->filename);	
				
			$data[]= '<a href="'.$url.'">'.$file->filename.'</a>';
	
			$data[] = date('d-m-Y',$file->timemodified);
            $table->add_data($data);

        } // End of: foreach ($userlist as $user).

    } // End if: if ($records).

    $table->finish_html();

	echo '<br /><div class="buttons">
        <input type="submit" id="set_permissions" value="' . get_string('continue', 'block_ejsapp_file_browser') . '" /> </div>';

    echo $OUTPUT->footer();
