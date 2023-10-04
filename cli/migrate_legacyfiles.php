<?php
define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/admin/tool/legacyfilesmigration/locallib.php');

// li options
list($options, $unrecognized) = cli_get_params(array(
        'help' => false,
        'courseid' => null,
        'username' => null,
        'copycoursefiles' => true,
),
array(
        'h' => 'help',
        'c' => 'courseid',
        'u' => 'username',
        'p' => 'copycoursefiles'
));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}
if ($options['courseid'] === null || ($options['copycoursefiles'] === true && $options['username'] === null) ) {
    cli_writeln(get_string('cliunknowoption', 'admin', $unrecognized));
    cli_error(get_string('cli_help', 'tool_legacyfilesmigration'));
}
$courseid = intval($options['courseid']);
$course = $DB->get_record('course', array('id'=>$courseid));
if(!$course){
    cli_writeln(get_string('cli_coursenotfound', 'tool_legacyfilesmigration'));
    cli_error(get_string('cli_help', 'tool_legacyfilesmigration'));
}
$username = $options['username'];
$user = null;
if(!empty($username)){
    $user = $DB->get_record('user', array('username'=>$username));
    if(!$user){
        cli_writeln(get_string('cli_usernotfound', 'tool_legacyfilesmigration'));
        cli_error(get_string('cli_help', 'tool_legacyfilesmigration'));
    }
}

$copycoursefiles = boolval($options['copycoursefiles']);
$coursecontext = context_course::instance($courseid);

list($summary, $success, $log) = tool_legacyfilesmigration_tools::tool_legacyfilesmigration_migrate_course(
        $courseid, $copycoursefiles, $username);

if($success){
    cli_writeln(get_string('cli_success', 'tool_legacyfilesmigration', $courseid));

}else{
    empty($log)?get_string('cli2_nolog','tool_legacyfilesmigration'):cli_writeln($log);
    cli_error(get_string('cli_fail', 'tool_legacyfilesmigration', $courseid));
}