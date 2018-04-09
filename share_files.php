<?php
// This file is part of the Moodle block "EJSApp file browser system"
// The function for shraing files in the EJSApp  file browser system has been developed by:
// - Arnoldo Fernandez: arnoldofernandez@gmail.com
// - María Masanet: mimasanet@gmail.com
// - Luis de la Torre: ldelatorre@dia.uned.es
//
// at the University National of San Juan (UNSJ, San Juan, Argentina and UNED, Madrid, Spain.

/**
 * Page for sharing files with other users
 *
 * @package    block_ejsapp_file_browser
 * @copyright  2017 Arnoldo Fernandez y María Masanet
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

$title = get_string('sharefiles', 'block_ejsapp_file_browser');

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

$allroles = get_all_roles();
$roles = get_profile_roles($context);
$allrolenames = array();
$rolenames = array(0 => get_string('allparticipants'));

foreach ($allroles as $role) {
    $allrolenames[$role->id] = strip_tags(role_get_name($role, $context));   // Used in menus etc later on.
    if (isset($roles[$role->id])) {
        $rolenames[$role->id] = $allrolenames[$role->id];
    }
}

// Make sure other roles may not be selected by any means.
if (empty($rolenames[$roleid])) {
    print_error('noparticipants');
}

// No roles to display yet?
if (empty($rolenames))  {
    if (has_capability('moodle/role:assign', $context)) {
        redirect($CFG->wwwroot.'/'.$CFG->admin.'/roles/assign.php?contextid='.$context->id);
    } else {
        print_error('noparticipants');
    }
}

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
echo $OUTPUT->heading(get_string('files_users_selection', 'block_ejsapp_file_browser'));


/**
 *
 * Returns the course last access
 *
 * @param string $accesssince
 * @return string
 */
function get_course_lastaccess_sql($accesssince='') {
    if (empty($accesssince)) {
        return '';
    }
    if ($accesssince == -1) { // Never.
        return 'ul.timeaccess = 0';
    } else {
        return 'ul.timeaccess != 0 AND ul.timeaccess < '.$accesssince;
    }
}

echo html_writer::start_tag ('div', array('class' => 'userlist'));

if ($isseparategroups and (!$currentgroup) ) {
    // The user is not in the group so show message and exit.
    echo $OUTPUT->heading(get_string("notingroup"));
    echo $OUTPUT->footer();
    exit;
}

// Should use this variable so that we don't break stuff every time a variable is added or changed.
$baseurl = new moodle_url('/blocks/ejsapp_file_browser/share_files.php', array(
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

// Get the hidden field list.
if (has_capability('moodle/course:viewhiddenuserfields', $context)) {
    $hiddenfields = array();  // Teachers and admins are allowed to see everything.
} else {
    $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
}

if (isset($hiddenfields['lastaccess'])) {
    // Do not allow access since filtering.
    $accesssince = 0;
}

// Print settings and things in a table across the top.
$controlstable = new html_table();
$controlstable->attributes['class'] = 'controls';
$controlstable->data[] = new html_table_row();
$controlstable->data[0]->cells[] = groups_print_course_menu($course, $baseurl->out(), true);

if (!isset($hiddenfields['lastaccess'])) {
    // Get minimum lastaccess for this course and display a dropbox to filter by lastaccess going back this far.
    // We need to make it diferently for normal courses and site course.
    $minlastaccess = $DB->get_field_sql('SELECT min(lastaccess)
                                         FROM {user}
                                         WHERE lastaccess != 0');
    $lastaccess0exists = $DB->record_exists('user', array('lastaccess' => 0));

    $now = usergetmidnight(time());
    $timeaccess = array();
    $baseurl->remove_params('accesssince');
} // End of: if (!isset($hiddenfields['lastaccess'])).

if ($currentgroup and (!$isseparategroups or has_capability('moodle/site:accessallgroups', $context))) {
    // Display info about the group.
    if ($group = groups_get_group($currentgroup)) {
        if (!empty($group->description) or (!empty($group->picture) and empty($group->hidepicture))) {
            $groupinfotable = new html_table();
            $groupinfotable->attributes['class'] = 'groupinfobox';
            $picturecell = new html_table_cell();
            $picturecell->attributes['class'] = 'left side picture';
            $picturecell->text = print_group_picture($group, $course->id, true, true, false);

            $contentcell = new html_table_cell();
            $contentcell->attributes['class'] = 'content';

            $contentheading = $group->name;
            if (has_capability('moodle/course:managegroups', $context)) {
                $aurl = new moodle_url('/group/group.php', array('id' => $group->id, 'courseid' => $group->courseid));
                $contentheading .= '&nbsp;' . $OUTPUT->action_icon($aurl, new pix_icon('t/edit',
                        get_string('editgroupprofile')));
            }

            $group->description = file_rewrite_pluginfile_urls($group->description, 'pluginfile.php',
                $context->id, 'group', 'description', $group->id);
            if (!isset($group->descriptionformat)) {
                $group->descriptionformat = FORMAT_MOODLE;
            }
            $options = array('overflowdiv' => true);
            $contentcell->text = $OUTPUT->heading($contentheading, 3) . format_text($group->description,
                    $group->descriptionformat, $options);
            $groupinfotable->data[] = new html_table_row(array($picturecell, $contentcell));
            echo html_writer::table($groupinfotable);
        }
    }
}

// Define a table showing a list of users in the current role selection.
$tablecolumns = array('userpic', 'fullname');
$tableheaders = array(get_string('userpic'), get_string('fullnameuser'));
if (!isset($hiddenfields['city'])) {
    $tablecolumns[] = 'city';
    $tableheaders[] = get_string('city');
}
if (!isset($hiddenfields['country'])) {
    $tablecolumns[] = 'country';
    $tableheaders[] = get_string('country');
}
if (!isset($hiddenfields['lastaccess'])) {
    $tablecolumns[] = 'lastaccess';
    $tableheaders[] = get_string('lastaccess');
}

$tablecolumns[] = 'select';
$tableheaders[] = get_string('select_share_files', 'block_ejsapp_file_browser');

$table = new flexible_table('user-index-participants-' . $course->id);

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($baseurl->out());

if (!isset($hiddenfields['lastaccess'])) {
    $table->sortable(true, 'lastaccess', SORT_DESC);
}

$table->no_sorting('roles');
$table->no_sorting('groups');
$table->no_sorting('groupings');
$table->no_sorting('select');

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
$contextlist = $context->get_parent_context_ids(true);

list($esql, $params) = get_enrolled_sql($context, null, $currentgroup, true);

$joins = array("FROM {user} u");
$wheres = array();

$select = "SELECT u.id, u.username, u.firstname, u.lastname,
                    u.email, u.city, u.country, u.picture,
                    u.lang, u.timezone, u.maildisplay, u.imagealt,
                    COALESCE(ul.timeaccess, 0) AS lastaccess";
$joins[] = "JOIN ($esql) e ON e.id = u.id"; // Course enrolled users only.
// Not everybody accessed course yet.
$joins[] = "LEFT JOIN {user_lastaccess} ul ON (ul.userid = u.id AND ul.courseid = :courseid)";
$params['courseid'] = $course->id;
if ($accesssince) {
    $wheres[] = get_course_lastaccess_sql($accesssince);
}

$joinon = 'u.id';
$contextlevel = CONTEXT_USER;
$tablealias = 'ctx';
$ccselect = ", " . context_helper::get_preload_record_columns_sql($tablealias);
$ccjoin = "LEFT JOIN {context} $tablealias ON ($tablealias.instanceid = $joinon AND $tablealias.contextlevel = $contextlevel)";
$select .= $ccselect;
$joins[] = $ccjoin;

// Limit list to users with some role only.
if ($roleid) {
    $wheres[] = "u.id IN (SELECT userid FROM {role_assignments} WHERE roleid = :roleid AND contextid IN (" .
        implode(',', $contextlist) . "))";
    $params['roleid'] = $roleid;
}

$from = implode("\n", $joins);
if ($wheres) {
    $where = "WHERE " . implode(" AND ", $wheres);
} else {
    $where = "";
}

$totalcount = $DB->count_records_sql("SELECT COUNT(u.id) $from $where", $params);

if (!empty($search)) {
    $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
    $wheres[] = "(" . $DB->sql_like($fullname, ':search1', false, false) .
                " OR " . $DB->sql_like('email', ':search2', false, false) .
                " OR " . $DB->sql_like('idnumber', ':search3', false, false) . ") ";
    $params['search1'] = "%$search%";
    $params['search2'] = "%$search%";
    $params['search3'] = "%$search%";
}

list($twhere, $tparams) = $table->get_sql_where();
if ($twhere) {
    $wheres[] = $twhere;
    $params = array_merge($params, $tparams);
}

$from = implode("\n", $joins);
if ($wheres) {
    $where = "WHERE " . implode(" AND ", $wheres);
} else {
    $where = "";
}

if ($table->get_sql_sort()) {
    $sort = ' ORDER BY ' . $table->get_sql_sort();
} else {
    $sort = '';
}

$matchcount = $DB->count_records_sql("SELECT COUNT(u.id) $from $where", $params);

$table->initialbars(true);
$table->pagesize($perpage, $matchcount);

// List of users at the current visible page - paging makes it relatively short.
$userlist = $DB->get_recordset_sql("$select $from $where $sort", $params, $table->get_page_start(), $table->get_page_size());

// If there are multiple Roles in the course, then show a drop down menu for switching.
if (count($rolenames) > 1) {
    $label = html_writer::label(get_string('currentrole', 'role'),'rolesform_jump');
    $select = $OUTPUT->single_select($rolenamesurl, 'roleid', $rolenames, $roleid, null, 'rolesform');
    echo html_writer::div($label . $select, 'rolesform');
} else if (count($rolenames) == 1) {
    // When all users with the same role - print its name.
    $text = get_string('role') . get_string('labelsep', 'langconfig');
    $rolename = reset($rolenames);
    echo html_writer::div($label . $rolename, 'rolesform');
}

echo html_writer::start_tag('form', array('action' => "shared_files_usr.php?courseid=$courseid&contextid=$context->id",
        'method' => 'post', 'id' => 'participantsform')) . html_writer::start_tag('div');
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'returnto', 'value' => s(me())));

echo html_writer::empty_tag('br');

// Select files pulldown menu
echo html_writer::tag('p', get_string('files_selection','block_ejsapp_file_browser'));
$tree = new ejsapp_file_browser_tree();
$files = array();

foreach ($tree->dir['files'] as $file){
    $files[$file->get_id()]=$file->get_filename();
}

echo html_writer::select($files, 'files_selected[]', '', false, array('multiple' => true));
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('br');

$countrysort = (strpos($sort, 'country') !== false);
$timeformat = get_string('strftimedate');

if ($userlist) {
    $usersprinted = array();
    foreach ($userlist as $user) {
        if (in_array($user->id, $usersprinted) or ($user->id==$USER->id)) { // Prevent duplicates
            continue;
        }
        $usersprinted[] = $user->id; // Add new user to the array of users printed.

        context_helper::preload_from_record($user);

        if ($user->lastaccess) {
            $lastaccess = format_time(time() - $user->lastaccess, $datestring);
        } else {
            $lastaccess = $strnever;
        }

        if (empty($user->country)) {
            $country = '';
        } else {
            if ($countrysort) {
                $country = '(' . $user->country . ') ' . $countries[$user->country];
            } else {
                $country = $countries[$user->country];
            }
        }

        $usercontext = context_user::instance($user->id);

        if (!isset($user->firstnamephonetic)) {
            $user->firstnamephonetic = $user->firstname;
        }
        if (!isset($user->lastnamephonetic)) {
            $user->lastnamephonetic = $user->lastname;
        }
        if (!isset($user->middlename)) {
            $user->middlename = '';
        }
        if (!isset($user->alternatename)) {
            $user->alternatename = '';
        }

        if ($piclink = ($USER->id == $user->id || has_capability('moodle/user:viewdetails', $context) ||
            has_capability('moodle/user:viewdetails', $usercontext))) {
            $link = html_writer::tag('a', fullname($user), array('href' => $CFG->wwwroot . '/user/view.php?id=' .
                $user->id . '&amp;course=' . $course->id));
            $profilelink = html_writer::tag('strong', $link);
        } else {
            $profilelink = html_writer::tag('strong', fullname($user));
        }

        $data = array ($OUTPUT->user_picture($user, array('size' => 35, 'courseid' => $course->id)), $profilelink);

        if (!isset($hiddenfields['city'])) {
            $data[] = $user->city;
        }
        if (!isset($hiddenfields['country'])) {
            $data[] = $country;
        }
        if (!isset($hiddenfields['lastaccess'])) {
            $data[] = $lastaccess;
        }

        $data[] = html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'user' . $user->id));
        $table->add_data($data);
        $usersids[] = $user->id;
    } // End of: foreach ($userlist as $user).

} // End of: if ($userlist).

$table->finish_html();

echo html_writer::empty_tag('br');
$content = html_writer::empty_tag('input', array('type' => 'button', 'id' => 'checkall', 'class' => 'btn btn-secondary',
        'value' => get_string('selectall'))) .
    html_writer::empty_tag('input', array('type' => 'button', 'id' => 'checknone', 'class' => 'btn btn-secondary',
        'value' => get_string('deselectall'))) .
    html_writer::empty_tag('input', array('type' => 'submit', 'id' => 'share_files', 'class' => 'btn btn-secondary',
        'value' => get_string('share', 'block_ejsapp_file_browser')));
echo html_writer::tag('div', $content, array('class' => 'buttons'));
echo html_writer::end_tag('div') . html_writer::end_tag('div') . html_writer::end_tag('form');

echo $OUTPUT->footer();