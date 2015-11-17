<?php

/**
 * Script to show all the courses where legacy files have not been migrated after the main upgrade.
 *
 * @package    tool_legacyfilesmigration
 *  * @package  
 * @subpackage 
 * @copyright  2014 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/legacyfilesmigration/locallib.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/legacyfilesmigration/migrateablecoursestable.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/legacyfilesmigration/migrateablecoursesbatchform.php');

require_sesskey();

// admin_externalpage_setup calls require_login and checks moodle/site:config
admin_externalpage_setup('toollegacyfilesmigration', '', array(), tool_legacyfilesmigration_url('batchmigrate'));

$PAGE->set_pagelayout('maintenance');
$PAGE->navbar->add(get_string('batchmigrate', 'tool_legacyfilesmigration'));

$renderer = $PAGE->get_renderer('tool_legacyfilesmigration');

$confirm = required_param('confirm', PARAM_BOOL);
if (!$confirm) {
    print_error('invalidrequest');
    die();
}
raise_memory_limit(MEMORY_EXTRA);
$session_instance = new \core\session\manager();
$session_instance->write_close(); // release session

echo $renderer->header();
echo $renderer->heading(get_string('batchmigrate', 'tool_legacyfilesmigration'));

$current = 0;
$migrateallmode = optional_param('migrateall', false, PARAM_BOOL);
if ($migrateallmode) {
	$courseids = tool_legacyfilesmigration_load_all_migrateable_courseids();
} else {
	$courseids = explode(',', optional_param('selected', '', PARAM_TEXT));
	$ownerusernames = optional_param('selectedowners', '', PARAM_TEXT);
	if(! empty($ownerusernames)){
		$ownerusernames = json_decode($ownerusernames);
	}
}
$total = count($courseids);

foreach ($courseids as $courseid) {
	$ownerusername='';
	$coursecontext = context_course::instance($courseid);
	if(!$migrateallmode){
		if(property_exists ( $ownerusernames , $courseid)){
			$ownerusername=$ownerusernames->$courseid;
		}
	}else{
		$owners = get_enrolled_users($coursecontext,'moodle/course:managefiles');
		if($owners !== false && count($owners)==1){
			$owner = array_pop($owners);
			$ownerusername=$owner->username;
		}
	}
    list($summary, $success, $log) = tool_legacyfilesmigration_migrate_course($courseid, $ownerusername);
    $current += 1;
    echo $renderer->heading(get_string('migrateprogress', 'tool_legacyfilesmigration', array('current'=>$current, 'total'=>$total)), 3);
    echo $renderer->convert_course_result($summary, $success, $log);
}

echo $renderer->continue_button(tool_legacyfilesmigration_url('listnotmigrated'));
echo $renderer->footer();
