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
 * Folder plugin version information
 *
 * @package
 * @subpackage
 * @copyright  2014 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Legacy files migration';
$string['usernotfound'] = 'user {$a} not found';
$string['coursenotfound'] = 'course {$a} not found';
$string['unknown'] = 'unknown';
$string['migrateselected'] = 'Migrate legacy files for selected courses';
$string['migrateselectedcount'] = 'Migrate legacy files for the {$a} selected courses?';
$string['nocoursesselected'] = 'No courses selected';
$string['nocoursestoupgrade'] = 'There are no courses that require legacy files migration';
$string['notsupported'] = 'not supported';
$string['listnotmigrated'] = 'List courses that contains legacy files to be migrated';
$string['listnotmigrated_desc'] = 'You can migrate legacy files for individual courses from here';
$string['supported'] = 'Migrate';
$string['notmigratedtitle'] = 'Courses that contains legacy files to migrate';
$string['notmigratedintro'] = 'List of courses that contains legacy files to migrate to private user files';
$string['owner'] = 'Owner';
$string['courseid'] = 'Course id';
$string['select'] = 'Select';
$string['migratetable'] = 'Migrate table';
$string['backtoindex'] = 'Back to index';
$string['batchoperations'] = 'Batch operations';
$string['confirmbatchmigrate'] = 'Confirm batch migrate courses';
$string['batchmigrate'] = 'Migrate legacy files for multiple courses';
$string['unchoosenowner'] = 'Please choose a user for course with id {$a}';
$string['migratecoursesummary'] = 'Migrate legacy files for course with id id {$a->id} and name "{$a->fullname}" : ';
$string['migratecoursesuccess'] = 'Result: Migrate legacy files successful';
$string['viewcourse'] = 'View the course with the migrated legacy files';
$string['migrateprogress'] = 'Migrate legacy files for courses n° {$a->current} of {$a->total}';
$string['migrationfailed'] = 'The legacy files migration was not successful.<br/>The log from the upgrade was: <br />{$a}';
$string['migrateall'] = 'Migrate legacy files for all courses';
$string['migrateallconfirm'] = 'Migrate legacy files for all courses?';
$string['preselect_owner'] = 'Preselect and fill owner';
$string['empty_owner'] = 'Default owner username to replace empty owner';
$string['coursidnotcollapse'] = 'courseid column must be uncollapsed!';
$string['toollegacyfilesmigrationsettings'] = 'Legacy files migration settings';
$string['foldername'] = 'folder name generated in private user repository ';
$string['foldername_desc'] = 'folder name generated in private user repository that will be evaluated with php eval using $course and $usercontext objects';
$string['copyallcoursefiles'] = "copy course files for all rows";
$string['copycoursefilesforall'] = "copy course files form all entries";
//cli traductions
$string['cli_help'] =
        "Execute legacyfiles migration for a course.

sudo -u www-data /usr/bin/php admin/tool/legacyfilesmigration/cli/migrate_legacyfiles.php -c=courseid -u=username [options]
Options:
-h, --help                 Print out this help
-c, --courseid             required, id of course to treat
-u, --username             required, username where to place legacyfiles into private file zone
-p, --copycoursefiles       optional, copycoursefiles into username private files
";
$string['cli_usernotfound']='user not found';
$string['cli_coursenotfound']='course not found';
$string['cli_fail']='legacyfilesmigration failed for course {$a}';
$string['cli_success']='legacyfilesmigration success for course {$a}';
$string['cli2_help'] =
        "return list of legacyfiles migration scripts.

sudo -u www-data /usr/bin/php admin/tool/legacyfilesmigration/cli/generate_clis.php [options]
Options:
-h, --help                 Print out this help
-c, --courseid             required, id of course to treat
-u, --username             optional  username where to place legacyfiles into private file zone, required if copycoursefiles is set to true
-p, --copycoursefiles       optional, copycoursefiles into username private files
";
$string['cli_nocoursetomigrate']='no courses with legacyfiles';
$string['cli2_nolog'] = 'fail with no log, maybe no legacyfiles to migrate';
$string['privacy:metadata'] = 'The legacy files migration tool plugin does not store any personal data.';