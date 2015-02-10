<?php
/**
 * legacyfiles migration settings file
 *
 * @package  
 * @subpackage 
 * @copyright  2014 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	$ADMIN->add('root', new admin_category('toollegacyfilesmigrationmanagment', get_string('pluginname', 'tool_legacyfilesmigration')));
	$ADMIN->add('toollegacyfilesmigrationmanagment', new admin_externalpage('toollegacyfilesmigration', get_string('pluginname', 'tool_legacyfilesmigration'), "$CFG->wwwroot/$CFG->admin/tool/legacyfilesmigration/listnotmigrated.php",'moodle/site:config'));
	$settings = new admin_settingpage('toollegacyfilesmigrationsettings', get_string('toollegacyfilesmigrationsettings','tool_legacyfilesmigration'),
			'moodle/site:config');
	$settings->add(new admin_setting_configtext("legacyfilesmigration/foldername",
			get_string('foldername', 'tool_legacyfilesmigration'),
			get_string('foldername_desc', 'tool_legacyfilesmigration'), '/courses/{$course->shortname}_{$course->id}_{$usercontext->id}'
	));
	$ADMIN->add('toollegacyfilesmigrationmanagment', $settings);
}
