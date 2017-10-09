<?php
/**
 * Created Arnoldo Fernandez y María Masanet
 * User: usuario
 * Date: 29/8/2017
 * Time: 6:11 PM
 */

global $CFG, $DB, $PAGE, $OUTPUT, $USER;
require_once('../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/filelib.php');
require_login();


$courseid = required_param('courseid', PARAM_RAW);
$contextid = required_param('contextid', PARAM_INT);
$files_selected = optional_param('files_selected', '', PARAM_RAW);

$context = context_course::instance($courseid);

//require_login($courseid);

$title = get_string('shared_files', 'block_ejsapp_file_browser');
//$title ="Compartir";

$PAGE->set_url('/block/block_ejsapp_file_browser/share_files_usr.php', array('courseid' => $courseid, 'contextid' => $context->id));

//$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
//$PAGE->set_pagelayout('incourse');
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$courseurl=new moodle_url("$CFG->wwwroot/course/view.php?id=$courseid");
$PAGE->navbar->add('<a href="'.$courseurl.'">'. $course->shortname.'</a>');
$PAGE->navbar->add($title);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

$count = 0;
$userlist = array();

echo $OUTPUT->header();

$isseparategroups = ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));

echo '<div class="userlist">';
$currentgroup = groups_get_course_group($course, true);

if (!$currentgroup) { // To make some other functions work better later.
    $currentgroup  = null;
}

if ($isseparategroups and (!$currentgroup) ) {
    // The user is not in the group so show message and exit.
    echo $OUTPUT->heading(get_string("notingroup"));
    echo $OUTPUT->footer();
    exit;
}
//revisar
$roleid=0;
// Should use this variable so that we don't break stuff every time a variable is added or changed.
$baseurl = new moodle_url('/blocks/ejsapp_file_browser/share_files.php', array(
    'courseid' => $course->id,
    'contextid' => $context->id,
    'roleid' => $roleid,
//    'perpage' => $perpage,
   // 'accesssince' => $accesssince,
  //  'search' => s($search)
));

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


// Print settings and things in a table across the top.
$controlstable = new html_table();
$controlstable->attributes['class'] = 'controls';
$controlstable->data[] = new html_table_row();

$controlstable->data[0]->cells[] = groups_print_course_menu($course, $baseurl->out(), true);

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
/*
if (!isset($hiddenfields['city'])) {
    $tablecolumns[] = 'city';
    $tableheaders[] = get_string('city');
}
if (!isset($hiddenfields['country'])) {
    $tablecolumns[] = 'country';
    $tableheaders[] = get_string('country');
}


$tablecolumns[] = 'select';
$tableheaders[] = get_string('users_shared_files', 'block_ejsapp_file_browser');
*/

 $table = new flexible_table('user-index-participants-' . $course->id);

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($baseurl->out());

    if (!isset($hiddenfields['lastaccess'])) {
        $table->sortable(true, 'lastaccess', SORT_DESC);
    }
    //$table->sortable(true, 'fullname', SORT_DESC);

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
// revisar
/*
$isfrontpage=true;
if ($isfrontpage) {
    $select = "SELECT u.id, u.username, u.firstname, u.lastname,
                        u.email, u.city, u.country, u.picture,
                        u.lang, u.timezone, u.maildisplay, u.imagealt";
    $joins[] = "JOIN ($esql) e ON e.id = u.id"; // Everybody on the frontpage usually.

} else {*/
    $select = "SELECT u.id, u.username, u.firstname, u.lastname,
                        u.email, u.city, u.country, u.picture,
                        u.lang, u.timezone, u.maildisplay, u.imagealt";
    $joins[] = "JOIN ($esql) e ON e.id = u.id"; // Course enrolled users only.
    // Not everybody accessed course yet.
    //$joins[] = "LEFT JOIN {user_lastaccess} ul ON (ul.userid = u.id AND ul.courseid = :courseid)";
    $params['courseid'] = $courseid;
 //   }

$joinon = 'u.id';
$contextlevel = CONTEXT_USER;
$tablealias = 'ctx';
$ccselect = ", " . context_helper::get_preload_record_columns_sql($tablealias);
$ccjoin = "LEFT JOIN {context} $tablealias ON ($tablealias.instanceid = $joinon AND $tablealias.contextlevel = $contextlevel)";
$select .= $ccselect;
$joins[] = $ccjoin;

// Limit list to users with some role only.
/*
if ($roleid) {
    $wheres[] = "u.id IN (SELECT userid FROM {role_assignments} WHERE roleid = :roleid AND contextid IN (" .
        implode(',', $contextlist) . "))";
    $params['roleid'] = $roleid;
}
*/
$from = implode("\n", $joins);
if ($wheres) {
    $where = "WHERE " . implode(" AND ", $wheres);
} else {
    $where = "";
}

$totalcount = $DB->count_records_sql("SELECT COUNT(u.id) $from $where", $params);
/*
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
*/
//$table->initialbars(true);
//$table->pagesize($perpage, $matchcount);

// List of users at the current visible page - paging makes it relatively short.
$userlist = $DB->get_recordset_sql("$select $from $where", $params, $table->get_page_start(), $table->get_page_size());

echo "<form action=\"$courseurl\" method=\"post\" id=\"sharedfiles\">" . '<div>';

    echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />     <input type="hidden" name="returnto" value="' . s(me()) . '" />';

	$countfiles=0;
	$strlist='';
	$recordfiles= array();
   foreach ($files_selected as $key => $file){
		if (!empty($file)) {
			$strlist = $strlist. '<li>'.$file.'</li>';
		    $countfiles++;
			$recordfile = $DB->get_record('files', array('userid' => $USER->id, 'filearea'=> 'private','filename'=>$file));
			$recordfiles[]= $recordfile;
		}
   }

	if ($countfiles == 0) {
		echo '<h2>No ha seleccionado archivo para compartir </h2>';
	}
	else {
		if ($countfiles == 1)
			echo '<h2>Usted ha compartido el archivo </h2>';
		else {
			echo '<h2>Usted ha compartido los archivos </h2>';
      	}
		echo '<ul>'.$strlist.'</ul>';
	}

// verifica que el curso tenga usuarios y que  hayan archivos seleccionados
    if (($userlist) and ($countfiles > 0)) {
		echo'<br/>';
	    echo '<h2>Con los participantes </h2>';

        $usersprinted = array();
        foreach ($userlist as $user) {
			// evitar duplicados y viene seleccionado
            if (in_array($user->id, $usersprinted) or (! isset($_POST['user'.$user->id]))) {
                continue;
            }
            $usersprinted[] = $user->id; // Add new user to the array of users printed.

			$usercontext = context_user::instance($user->id);

            if (has_capability('moodle/user:viewdetails', $context) ||  has_capability('moodle/user:viewdetails', $usercontext)) {
                $profilelink = '<strong><a href="' . $CFG->wwwroot . '/user/view.php?id=' . $user->id . '&amp;course=' .
                    $course->id . '">' . fullname($user) . '</a></strong>';
            } else {
                $profilelink = '<strong>' . fullname($user) . '</strong>';
            }

            $data = array ($OUTPUT->user_picture($user, array('size' => 35, 'courseid' => $course->id)), $profilelink);

            if (isset($userlistextra) && isset($userlistextra[$user->id])) {
                $ras = $userlistextra[$user->id]['ra'];
                $rastring = '';
                foreach ($ras as $key => $ra) {
                    $rolename = $allrolenames[$ra['roleid']];
                    if ($ra['ctxlevel'] == CONTEXT_COURSECAT) {
                        $rastring .= $rolename . ' @ ' . '<a href="' . $CFG->wwwroot . '/course/category.php?id=' .
                            $ra['ctxinstanceid'] . '">' . s($ra['ccname']) . '</a>';
                    } else if ($ra['ctxlevel'] == CONTEXT_SYSTEM) {
                        $rastring .= $rolename . ' - ' . get_string('globalrole', 'role');
                    } else {
                        $rastring .= $rolename;
                    }
                }
                $data[] = $rastring;
                if ($groupmode != 0) {
                    // Use htmlescape with s() and implode the array.
                    $data[] = implode(', ', array_map('s', $userlistextra[$user->id]['group']));
                    $data[] = implode(', ', array_map('s', $userlistextra[$user->id]['gping']));
                }
            }

            $table->add_data($data);
            $usersids[] = $user->id;
			// guarda el registro en la base de datos
			foreach ($recordfiles as $r){
				$record = new stdClass();
				$record->fileid         = $r->id;
				$record->sharedwithuserid = $user->id;
				$record->timemodified  =time();
				$lastinsertid = $DB->insert_record('block_ejsapp_shared_files', $record, false);
			}
        } // End of: foreach ($userlist as $user).

    } // End if: if ($userlist).
    $table->finish_html();
  echo '<br /><div class="buttons">
<input type="submit" id="continuar" value="' . get_string('continue', 'block_ejsapp_file_browser') . '" />
</div> </form>';
echo $OUTPUT->footer();