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
 * legacyfiles migration settings file
 *
 * @package
 * @subpackage
 * @copyright  2017 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('root', new admin_category('toollegacyfilesmigrationmanagment',
            get_string('pluginname', 'tool_legacyfilesmigration')));
    $ADMIN->add('toollegacyfilesmigrationmanagment', new admin_externalpage(
            'toollegacyfilesmigration', get_string('pluginname', 'tool_legacyfilesmigration'),
            "$CFG->wwwroot/$CFG->admin/tool/legacyfilesmigration/listnotmigrated.php", 'moodle/site:config'));
    $settings = new admin_settingpage('toollegacyfilesmigrationsettings',
            get_string('toollegacyfilesmigrationsettings', 'tool_legacyfilesmigration'),
            'moodle/site:config');
    $settings->add(new admin_setting_configtext("tool_legacyfilesmigration/foldername",
            get_string('foldername', 'tool_legacyfilesmigration'),
            get_string('foldername_desc', 'tool_legacyfilesmigration'),
                '/courses/{$a->courseshortname}_{$a->courseid}_{$a->username}'
    ));
    $ADMIN->add('toollegacyfilesmigrationmanagment', $settings);
}
