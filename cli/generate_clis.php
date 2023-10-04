<?php
define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/admin/tool/legacyfilesmigration/locallib.php');

list($options, $unrecognized) = cli_get_params(array(
        'help' => false,
        'username' => null,
        'copycoursefiles' => true,
        'prefix' => '',
        'cleanprivatefiles' => true
),
        array(
                'h' => 'help',
                'u' => 'username',
                'p' => 'copycoursefiles',
                'b' => 'prefixefix',
                'x' => 'cleanprivatefiles'

        ));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}
if (($options['copycoursefiles'] === true && $options['username'] === null) ) {
    cli_writeln(get_string('cliunknowoption', 'admin', $unrecognized));
    cli_error(get_string('cli2_help', 'tool_legacyfilesmigration'));
}
$username = $options['username'];
$copycoursefiles = boolval($options['copycoursefiles']);
$cleanprivatefiles = boolval($options['cleanprivatefiles']);
if(!tool_legacyfilesmigration_tools::tool_legacyfilesmigration_any_migrateable_courses()){
    cli_error('cli_nocoursetomigrate', 'tool_legacyfilesmigration');
}
$anymigrateablecourses = tool_legacyfilesmigration_tools::tool_legacyfilesmigration_load_all_migrateable_courseids();
foreach($anymigrateablecourses as $migrateablecourse){
    cli_writeln($options['prefix'].'php '.$CFG->dirroot.'/admin/tool/legacyfilesmigration/cli/migrate_legacyfiles.php -c='.$migrateablecourse.(is_null($username)? '':' -u='.$username).' -p='.($copycoursefiles?'true':'false'));
    if($copycoursefiles && $cleanprivatefiles){
        cli_writeln($options['prefix'].'php '.$CFG->dirroot.'/admin/tool/cmdlinetools/cli/cmdline_manager.php get_user_private_files '.$username.' -d -s='.$migrateablecourse);
    }
}

