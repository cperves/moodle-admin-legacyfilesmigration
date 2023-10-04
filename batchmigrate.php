<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Script to show all the courses where legacy files have not been migrated after the main upgrade.
 *
 * @package    tool_legacyfilesmigration
 *  * @package
 * @subpackage
 * @copyright  2017 unistra  {@link http://unistra.fr}
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

// The admin_externalpage_setup calls require_login and checks moodle/site:config.
admin_externalpage_setup('toollegacyfilesmigration', '', array(), tool_legacyfilesmigration_url('batchmigrate'));

$PAGE->set_pagelayout('maintenance');
$PAGE->navbar->add(get_string('batchmigrate', 'tool_legacyfilesmigration'));

$renderer = $PAGE->get_renderer('tool_legacyfilesmigration');

$confirm = required_param('confirm', PARAM_BOOL);
if (!$confirm) {
    throw new moodle_exception('invalidrequest');
    die();
}
raise_memory_limit(MEMORY_EXTRA);
$sessioninstance = new \core\session\manager();
$sessioninstance->write_close(); // Release session.

echo $renderer->header();
echo $renderer->heading(get_string('batchmigrate', 'tool_legacyfilesmigration'));

$current = 0;
$migrateallmode = optional_param('migrateall', false, PARAM_BOOL);
$copycoursefilesforall = optional_param('copycoursefilesforall', false, PARAM_BOOL);
$copycoursefiles = array();
$ownerusernames = '';

if ($migrateallmode) {
    $courseids = tool_legacyfilesmigration_tools::tool_legacyfilesmigration_load_all_migrateable_courseids();
} else {
    $courseids = explode(',', optional_param('selected', '', PARAM_TEXT));
    $ownerusernames = optional_param('selectedowners', '', PARAM_TEXT);
    if (! empty($ownerusernames)) {
        $ownerusernames = json_decode($ownerusernames ?? '');
    }
    $copycoursefiles = explode(',', optional_param('copycoursefiles', '', PARAM_TEXT));
}
$total = count($courseids);

foreach ($courseids as $courseid) {
    $ownerusername = '';
    $coursecontext = context_course::instance($courseid);
    if (!$migrateallmode) {
        if (property_exists ( $ownerusernames , $courseid)) {
            $ownerusername = $ownerusernames->$courseid;
        }
    } else {
        $owners = get_enrolled_users($coursecontext, 'moodle/course:managefiles');
        if ($owners !== false && count($owners) == 1) {
            $owner = array_pop($owners);
            $ownerusername = $owner->username;
        }
    }
    $copycoursefilesforcurrent = $migrateallmode ? $copycoursefilesforall : in_array($courseid, $copycoursefiles);
    list($summary, $success, $log) = tool_legacyfilesmigration_tools::tool_legacyfilesmigration_migrate_course(
            $courseid, $copycoursefilesforcurrent, $ownerusername);
    $current += 1;
    echo $renderer->heading(get_string('migrateprogress', 'tool_legacyfilesmigration',
            array('current' => $current, 'total' => $total)), 3);
    echo $renderer->convert_course_result($summary, $success, $log);
}

echo $renderer->continue_button(tool_legacyfilesmigration_url('listnotmigrated'));
echo $renderer->footer();
