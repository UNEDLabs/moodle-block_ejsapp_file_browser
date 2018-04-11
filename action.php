<?php
// This file is part of the Moodle block "EJSApp file browser system"
// The function for sharing files in the EJSApp  file browser system has been developed by:
// - Arnoldo Fernandez: arnoldofernandez@gmail.com
// - María Masanet: mimasanet@gmail.com
// - Luis de la Torre: ldelatorre@dia.uned.es
//
// at the University National of San Juan (UNSJ, San Juan, Argentina and UNED, Madrid, Spain.

/**
 * Page for visualizing which files are shared from other users
 *
 * @package    block_ejsapp_file_browser
 * @copyright  2017 Arnoldo Fernandez y María Masanet
 */

global $CFG, $DB, $PAGE, $OUTPUT, $USER;
require_once('../../config.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/filestorage/file_storage.php');
require_login();

$courseid = required_param('courseid', PARAM_RAW);
$contextid = required_param('contextid', PARAM_INT);
$originaluserid = required_param('originaluserid', PARAM_INT);
$action = required_param('action', PARAM_TEXT);

$context = context_course::instance($courseid);

$title = get_string('shared_files', 'block_ejsapp_file_browser');

$PAGE->set_url('/block/block_ejsapp_file_browser/action.php', array('courseid' => $courseid, 'contextid' => $context->id));

$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$courseurl = new moodle_url("$CFG->wwwroot/course/view.php?id=$courseid");
$PAGE->navbar->add(html_writer::tag('a', $course->shortname, array('href' => $courseurl)));
$PAGE->navbar->add($title);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

echo $OUTPUT->header();

// Prepare info for sending a message to the user who sent the sharing request
$user = $DB->get_record('user', array('id' => $originaluserid));
$message = new \core\message\message();
$message->component = 'moodle';
$message->name = 'instantmessage';
$message->userfrom = $USER;
$message->userto = $user;
$message->subject = 'Response to your file sharing request';
$message->fullmessageformat = FORMAT_MARKDOWN;
$message->notification = '0';
$message->contexturl = $CFG->wwwroot;
$message->contexturlname = $COURSE->fullname;
$message->replyto = $USER->email;
$content = array('*' => array('header' => ' test ', 'footer' => ' test '));
$message->set_additional_content('email', $content);
$message->courseid = $course->id;

// Get info of files pending to be shared with this user by the original user who sent the file sharing request
$sharedfiles = $DB->get_records('block_ejsapp_shared_files', array('shareduserid' => $USER->id,
    'originaluserid' => $originaluserid, 'sharedfileid' => 0));

if (count($sharedfiles) > 0) {
    if ($action == 'accept') {
        echo html_writer::tag('p', 'The files from ' . $user->username .' have been received.');
        // Prepare files
        foreach ($sharedfiles as $sharedfile) {
            if ($DB->record_exists('files', array('id' => $sharedfile->originalfileid))) {
                $file = $DB->get_record('files', array('id' => $sharedfile->originalfileid));
                $fs = get_file_storage();
                // Prepare file record object
                $timecreation = time();
                $usercontext = context_user::instance($USER->id);
                $fileinfo = array(
                    'contextid' => $usercontext->id,
                    'component' => $file->component,    // usually = table name
                    'filearea' => $file->filearea,      // usually = table name
                    'itemid' => $file->itemid,          // usually = ID of row in table
                    'filepath' => $file->filepath,      // any path beginning and ending in
                    'filename' => $user->username . '_' . $file->filename,
                    'timecreated' => $timecreation,
                    'timemodified' => $timecreation,
                    'userid' => $USER->id);
                // Check if shared user already has the file
                if ($fs->file_exists($usercontext->id, $file->component, $file->filearea, $file->itemid, $file->filepath,
                    $user->username . '_' . $file->filename)) {
                    // Delete file
                    $oldsharedfile = $fs->get_file($usercontext->id, $file->component, $file->filearea, $file->itemid,
                        $file->filepath,$user->username . '_' . $file->filename);
                    $oldsharedfile->delete();
                }
                // Create file
                $result = $fs->create_file_from_storedfile($fileinfo, $file->id);
                // Update record in block_ejsapp_shared_files table with the file id
                $sharedfile->sharedfileid = $result->get_id();
                $DB->update_record('block_ejsapp_shared_files', $sharedfile);
            }
        }
        // Fill content of the 'accepted' message and send it
        $message->fullmessage = $USER->username . ' accepted your files.';
        $message->fullmessagehtml = '<p>' . $USER->username .  ' accepted your files.</p>';
        $message->smallmessage = 'I just accepted your files.';
        $messageid = message_send($message);
    } else if ($action == 'reject') {
        // Delete records in block_ejsapp_shared_files table
        $DB->delete_records('block_ejsapp_shared_files', array('shareduserid' => $USER->id,
            'originaluserid' => $originaluserid, 'sharedfileid' => 0));
        echo html_writer::tag('p', 'The files from ' . $user->username . ' have been rejected.');
        // Fill content of the 'rejected' message and send it
        $message->fullmessage = $USER->username . ' rejected your files.';
        $message->fullmessagehtml = '<p>' . $USER->username .  ' rejected your files.</p>';
        $message->smallmessage = 'I just rejected your files.';
        $messageid = message_send($message);
    }
} else {
    echo html_writer::tag('p', 'There are no pending files to be received from ' . $user->username . '.');
}

echo html_writer::empty_tag('br');
echo html_writer::start_tag('form', array('action' => $courseurl, 'method' => 'post', 'id' => 'sharedfiles'));
$content = html_writer::empty_tag('input', array('type' => 'submit', 'id' => 'continue', 'class' => 'btn btn-secondary',
    'value' => get_string('continue', 'block_ejsapp_file_browser')));
echo html_writer::tag('div', $content, array('class' => 'buttons'));
echo html_writer::end_tag('div') . html_writer::end_tag('div') . html_writer::end_tag('form');

echo $OUTPUT->footer();