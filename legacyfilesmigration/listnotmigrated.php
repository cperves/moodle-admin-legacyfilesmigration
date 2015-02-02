<?php
/**
 * legacyfiles migration tool list of courses
 *
 * @package  
 * @subpackage 
 * @copyright  2014 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/legacyfilesmigration/locallib.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/legacyfilesmigration/migrateablecoursestable.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/legacyfilesmigration/migrateablecoursesbatchform.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/legacyfilesmigration/paginationform.php');

require_once($CFG->libdir . '/adminlib.php');
// admin_externalpage_setup calls require_login and checks moodle/site:config
admin_externalpage_setup('toollegacyfilesmigration', '', array(), tool_legacyfilesmigration_url('listnotmigrated'));
$PAGE->navbar->add(get_string('listnotmigrated', 'tool_legacyfilesmigration'));
$renderer = $PAGE->get_renderer('tool_legacyfilesmigration');
$perpage = optional_param('perpage', 0, PARAM_INT);
if (!$perpage) {
	$perpage = get_user_preferences('tool_legacyfilesmigration_perpage', 100);
} else {
	set_user_preference('tool_legacyfilesmigration_perpage', $perpage);
}
//TODO add number of courses
$courses = new tool_legacyfilesmigration_courses_table($perpage);
$batchform = new tool_legacyfilesmigration_batchoperations_form();
$data = $batchform->get_data();

if ($data && $data->selectedcourses != '' || $data && isset($data->migrateall)) {
	require_sesskey();
	echo $renderer->confirm_batch_operation_page($data);
} else {
	$paginationform = new tool_legacyfilesmigration_pagination_form();
	$pagedata = new stdClass();
	$pagedata->perpage = $perpage;
	$paginationform->set_data($pagedata);
	echo $renderer->course_list_page($courses, $batchform, $paginationform);
}