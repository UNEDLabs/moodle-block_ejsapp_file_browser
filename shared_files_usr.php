<?php
// This file is part of the Moodle block "EJSApp file browser system"
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
 */

global $CFG, $DB, $PAGE, $OUTPUT, $USER;
require_once('../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/filestorage/file_storage.php');
require_login();

$courseid = required_param('courseid', PARAM_RAW);
$contextid = required_param('contextid', PARAM_INT);
$files_selected = optional_param('files_selected', '', PARAM_RAW);

$context = context_course::instance($courseid);

$title = get_string('shared_files', 'block_ejsapp_file_browser');

$PAGE->set_url('/block/block_ejsapp_file_browser/share_files_usr.php', array('courseid' => $courseid, 'contextid' => $context->id));

$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);

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

$roleid=0;
// Should use this variable so that we don't break stuff every time a variable is added or changed.
$baseurl = new moodle_url('/blocks/ejsapp_file_browser/share_files.php', array(
    'courseid' => $course->id,
    'contextid' => $context->id,
   // 'roleid' => $roleid,
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

$table = new flexible_table('user-index-participants-' . $course->id);

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($baseurl->out());

if (!isset($hiddenfields['lastaccess'])) {
        $table->sortable(true, 'lastaccess', SORT_DESC);
 }

//$table->no_sorting('roles');
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
                        u.lang, u.timezone, u.maildisplay, u.imagealt";
$joins[] = "JOIN ($esql) e ON e.id = u.id"; // Course enrolled users only.
    
$params['courseid'] = $courseid;

$joinon = 'u.id';
$contextlevel = CONTEXT_USER;
$tablealias = 'ctx';
$ccselect = ", " . context_helper::get_preload_record_columns_sql($tablealias);
$ccjoin = "LEFT JOIN {context} $tablealias ON ($tablealias.instanceid = $joinon AND $tablealias.contextlevel = $contextlevel)";
$select .= $ccselect;
$joins[] = $ccjoin;

$from = implode("\n", $joins);
if ($wheres) {
    $where = "WHERE " . implode(" AND ", $wheres);
} else {
    $where = "";
}

$totalcount = $DB->count_records_sql("SELECT COUNT(u.id) $from $where", $params);

// List of users at the current visible page - paging makes it relatively short.
$userlist = $DB->get_recordset_sql("$select $from $where", $params, $table->get_page_start(), $table->get_page_size());

echo "<form action=\"$courseurl\" method=\"post\" id=\"sharedfiles\">" . '<div>';
echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />     <input type="hidden" name="returnto" value="' . s(me()) . '" />';
   
	$countfiles=0;
	$recordfiles= array();
	$strlist ="";
	//echo get_string('shared_files','block_ejsapp_file_browser').'<br/>';
	
    foreach ( $files_selected as  $file) {			
		if (!empty($file)) {				
			
			$recordfile = $DB->get_record('files', array('id'=>$file));
			$recordfiles[]= $recordfile;
			
			$strlist = $strlist. '<li>'.$recordfile->filename.'</li>';
			$countfiles++;
		}		
    }

	if ($countfiles == 0) {		
		echo '<h2>'.get_string('nonselectfiles','block_ejsapp_file_browser').' </h2>';		
	}
	else {
		if ($countfiles == 1)
			echo '<h2>'.get_string('you_share_file','block_ejsapp_file_browser').' </h2>';		
		else {
			echo '<h2>'.get_string('you_share_files','block_ejsapp_file_browser').' </h2>';	
      	}
		echo '<ul>'.$strlist.'</ul>';
	}

// validate users of course and selected files
    if (($userlist) and ($countfiles > 0) ) {	
		
		echo'<br/>';
	    echo '<h2>'.get_string('with_participants','block_ejsapp_file_browser').' </h2>';	

        $usersprinted = array();
        foreach ($userlist as $user) {
			// duplicate?
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

            $table->add_data($data);
			
			// save record in database 
			foreach ($recordfiles as $file){

				$fs = get_file_storage();
 
				// name of new file
				$namefile= explode('.',$file->filename);
				if ((!empty($namefile[0]) ) and (!empty($namefile[1])))
					$newnamefile= $namefile[0].'_'.time().'.'.$namefile[1];
				else 
					$newnamefile= time().'_'.$file->filename;
				
				// Prepare file record object
				$timecreation= time();
				$fileinfo = array(
					'contextid' => $usercontext->id, // ID of context					
					'component' => $file->component,     // usually = table name
					'filearea' => $file->filearea,     // usually = table name
					'itemid' => $file->itemid,               // usually = ID of row in table
					'filepath' => $file->filepath,           // any path beginning and ending in /
					'filename' => $newnamefile,// any filename
					'timecreated'=>$timecreation,
					'timemodified'=>$timecreation,
					'userid' => $user->id);			
					
 
				// Create file 
				$resultado=$fs->create_file_from_storedfile($fileinfo, $file->id);	
							
				$record = new stdClass();
				$record->fileid         = $resultado->get_id();
				$record->sharedwithuserid = $USER->id;
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