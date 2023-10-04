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
 * legacyfiles migration tool library functions
 *
 * @package    tool_legacyfilesmigration
 * @copyright  2017 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
define('ASSIGN_MAX_LEGACYFILES_MIGRATION_TIME_SECS', 0);


/**
 * Get the URL of a script within this plugin.
 * @param string $script the script name, without .php. E.g. 'index'
 * @param array $params URL parameters (optional)
 * @return moodle_url
 */
function tool_legacyfilesmigration_url($script, $params = array()) {
    global $CFG;
    return new moodle_url('/'.$CFG->admin.'/tool/legacyfilesmigration/' . $script . '.php', $params);
}



/**
 * Class to encapsulate the continue / cancel for batch operations
 *
 * @package    tool_legacyfilesmigration
 * @copyright  2012 NetSpot
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_legacyfilesmigration_batchoperationconfirm implements renderable {
    /** @var string $continuemessage The message to show above the continue cancel buttons */
    public $continuemessage = '';
    /** @var string $continueurl The url to load if the user clicks continue */
    public $continueurl;

    /**
     * Constructor for this class
     * @param stdClass $data - The data from the previous batch form
     */
    public function __construct($data) {
        global $CFG;
        if (isset($data->migrateselected)) {
            $this->continuemessage = get_string('migrateselectedcount', 'tool_legacyfilesmigration',
                    count(explode(',', $data->selectedcourses)));
            $this->continueurl = new moodle_url('/'.$CFG->admin.'/tool/legacyfilesmigration/batchmigrate.php',
                    array('upgradeselected' => '1', 'confirm' => '1', 'sesskey' => sesskey(), 'selected' => $data->selectedcourses,
                            'copycoursefiles' => $data->copycoursefiles, 'selectedowners' => $data->selectedowners));
        } else if (isset($data->migrateall)) {
            if (!tool_legacyfilesmigration_tools::tool_legacyfilesmigration_any_migrateable_courses()) {
                $this->continuemessage = get_string('noacoursestoupgrade', 'tool_legacyfilesmigration');
                $this->continueurl = '';
            } else {
                $this->continuemessage = get_string('migrateallconfirm', 'tool_legacyfilesmigration');
                $copycoursefilesforall = property_exists($data, 'copycoursefilesforall') ? $data->copycoursefilesforall : 0;
                $this->continueurl = new moodle_url('/'.$CFG->admin.'/tool/legacyfilesmigration/batchmigrate.php',
                        array('migrateall' => '1', 'copycoursefilesforall' => $copycoursefilesforall,
                                'confirm' => '1', 'sesskey' => sesskey()));
            }
        }
    }
}


/**
 * Class to encapsulate one of the functionalities that this plugin offers.
 *
 * @package    tool_legacyfilesmigration
 */
class tool_legacyfilesmigration_action {
    /** @var string the name of this action. */
    public $name;
    /** @var moodle_url the URL to launch this action. */
    public $url;
    /** @var string a description of this aciton. */
    public $description;

    /**
     * Constructor to set the fields.
     *
     * In order to create a new tool_legacyfilesmigration_action instance you must use the tool_legacyfilesmigration_action::make
     * method.
     *
     * @param string $name the name of this action.
     * @param moodle_url $url the URL to launch this action.
     * @param string $description a description of this aciton.
     */
    protected function __construct($name, moodle_url $url, $description) {
        $this->name = $name;
        $this->url = $url;
        $this->description = $description;
    }

    /**
     * Make an action with standard values.
     * @param string $shortname internal name of the action. Used to get strings and build a URL.
     * @param array $params any URL params required.
     * @return tool_legacyfilesmigration_action
     */
    public static function make($shortname, $params = array()) {
        return new self(
                get_string($shortname, 'tool_legacyfilesmigration'),
                tool_legacyfilesmigration_url($shortname, $params),
                get_string($shortname . '_desc', 'tool_legacyfilesmigration'));
    }
}


class tool_legacyfilesmigration_tools{
    /**
     * Determine if there are any courses with legacyfiles that can't be migrated
     * @return boolean - Are there any course with legacy files to migrate
     */
    public static function tool_legacyfilesmigration_any_migrateable_courses() {
        global $DB, $CFG;
        $courses = $DB->get_records_sql(
            'select distinct c.id,c.fullname as name
             from {course} c inner join {context} cctx on cctx.instanceid=c.id and cctx.contextlevel=:coursecontext
             where cctx.id in (select distinct contextid from {files} where component=:component and filearea=:filearea)
             order by c.fullname',
            array('component' => 'course', 'filearea' => 'legacy', 'coursecontext' => CONTEXT_COURSE));
        return $courses === false ? false : count($courses) > 0;
    }

    /**
     * Load a list of all the courseids with legacy files that can be migrated
     * @return array of course ids
     */
    public static function tool_legacyfilesmigration_load_all_migrateable_courseids() {
        global $DB, $CFG;
        $ids = $DB->get_records_sql(
                'select distinct c.id,c.fullname as name
                 from {course} c inner join {context} cctx on cctx.instanceid=c.id and cctx.contextlevel=:coursecontext
                 where cctx.id in
                 (select distinct contextid from {files}
                 where component=:component and filearea=:filearea) order by c.fullname',
                array('component' => 'course', 'filearea' => 'legacy', 'coursecontext' => CONTEXT_COURSE));
        return $ids === false ? array() : array_keys($ids);
    }

    /**
     * Migrate a single course. This is used by both migrate single and migrate batch
     * @param $courseid course id
     * @param $userid user id
     */
    public static function tool_legacyfilesmigration_migrate_course($courseid, $copycoursefiles = false, $ownerusername) {
        global $CFG, $DB;
        $legacyfilesmigrater = new legacyfiles_migrate_manager();
        $course = $DB->get_record('course', array('id' => $courseid));
        if ($course) {
            $log = '';
            if ($copycoursefiles && empty($ownerusername)) {
                $success = false;
                $log .= get_string('unchoosenowner', 'tool_legacyfilesmigration', $courseid);
            } else if ($copycoursefiles) {
                $user = $DB->get_record('user', array('username' => $ownerusername, 'deleted' => 0));
                if ($user) {
                    $success = $legacyfilesmigrater->migrate_files($course, $user, $copycoursefiles, $log);
                } else {
                    $success = false;
                    $log .= get_string('usernotfound', 'tool_legacyfilesmigration', $ownerusername);
                    $info = new stdClass();
                }
            } else {
                $success = $legacyfilesmigrater->migrate_files($course, null, $copycoursefiles, $log);
            }
        } else {
            $success = false;
            $log .= get_string('coursenotfound', 'tool_legacyfilesmigration', $courseid);
            $course = new stdClass();
            $course->id = get_string('unknown', 'tool_legacyfilesmigration');
            $course->fullname = get_string('unknown', 'tool_legacyfilesmigration');
        }
        return array($course, $success, $log);
    }

    public static function string_format_by_object($string, $a) {
        if ($a !== null) {
            if (is_object($a) or is_array($a)) {
                $a = (array)$a;
                $search = array();
                $replace = array();
                foreach ($a as $key => $value) {
                    if (is_int($key)) {
                        // We do not support numeric keys - sorry!
                        continue;
                    }
                    $search[] = '{$a->' . $key . '}';
                    $replace[] = (string)$value;
                }
                if ($search) {
                    $string = str_replace($search, $replace, $string ?? '');
                }
            } else {
                $string = str_replace('{$a}', (string)$a, $string ?? '');
            }
        }
        return $string;
    }
}

class legacyfiles_migrate_manager {
    public function migrate_files($course, $user = null, $copycoursefiles = 0, & $log) {
        global $DB, $CFG, $USER;
        require_once($CFG->libdir.'/filestorage/file_storage.php');
        require_once($CFG->libdir.'/resourcelib.php');

        // Is the user the admin? admin check goes here.
	/*
        if (!is_siteadmin($USER->id)) {
            return false;
        }*/
        // Should we use a shutdown handler to rollback on timeout?
        @set_time_limit(ASSIGN_MAX_LEGACYFILES_MIGRATION_TIME_SECS);
        // ------------------------------
        // Migration Process.
        // ------------------------------
        $usercontext = null;
        $foldername = null;
        if ($copycoursefiles && isset($user)) {
            $usercontext = context_user::instance($user->id);
            $foldername = get_config('tool_legacyfilesmigration', 'foldername');
            if ($foldername != null && !empty($foldername)) {
                $obj = new stdClass();
                $obj->courseshortname = $course->shortname;
                $obj->courseid = $course->id;
                $obj->usercontextid = $usercontext->id;
                $obj->username = $user->username;
		$obj->userlastname = $user->lastname;
                $obj->userfirstname = $user->firstname;
                $foldername = tool_legacyfilesmigration_tools::string_format_by_object($foldername, $obj);
            } else {
                $foldername = '/courses/'.$course->shortname.'_'.$course->id.'_'.$username->id;
            }
            // Normalize name since made while storing filepath.
            $foldername = clean_param($foldername, PARAM_PATH);
        }
        $coursecontext = context_course::instance($course->id);
        $fs = get_file_storage();
        // Retrieving legacyfiles and copy them as private repository.
        if ($copycoursefiles) {
            // Include dirs to delete them.
            $oldfiles = $fs->get_area_files($coursecontext->id, 'course', 'legacy', false, 'id', true);
            foreach ($oldfiles as $oldfile) {
                if ($oldfile->get_filename() != '.') {
                    $filerecord = new stdClass();
                    $filerecord->contextid = $usercontext->id;
                    $filerecord->component = 'user';
                    $filerecord->filearea = 'private';
                    $filerecord->userid = $user->id;
                    // Need to copy all files because of resource usage with folders acting like sites.
                    $filerecord->filepath = $foldername. $oldfile->get_filepath();
                    try {
                        $fs->create_file_from_storedfile($filerecord, $oldfile);
                    } catch (stored_file_creation_exception $sfcex) {
                        $log .= '<div class=legacyfilesmigration_log_minor>file already existing '
                                .$oldfile->get_filename().'</div>';
                    }
                    $log .= "<br>moving ".$oldfile->get_filename();
                }
            }
        }
        // Need to procedd for each contextid concerned because of delete_area_files.
        $sql = 'select distinct f.contextid as id,f.component,f.filearea from {files} f
                 inner join {files_reference} fr on fr.id=f.referencefileid
                 inner join {repository_instances} ri on ri.id=fr.repositoryid inner join {repository} r on r.id=ri.typeid
                inner join {context} ctx on ctx.id=f.contextid and ctx.contextlevel=:contextmodule
                 inner join {course_modules} cm on cm.id=ctx.instanceid
                where r.type=:repository and cm.course=:course';
        $legacyfilealiasescontexts = $DB->get_records_sql($sql, array('repository' => 'coursefiles',
                'contextmodule' => CONTEXT_MODULE, 'course' => $course->id));
        if ($legacyfilealiasescontexts) {
            foreach ($legacyfilealiasescontexts as $legacyfilealiasescontext) {
                // Alias files of course files.
                   $sql = 'select f.*, fr.reference from {files} f inner join {files_reference} fr on fr.id=f.referencefileid
                     inner join {repository_instances} ri on ri.id=fr.repositoryid inner join {repository} r on r.id=ri.typeid
                     where r.type=:repository and f.contextid=:ctxid';
                $legacyfilealiases = $DB->get_records_sql($sql, array('repository' => 'coursefiles',
                        'ctxid' => $legacyfilealiasescontext->id));
                if ($legacyfilealiases) {
                    // Need to first delete mod_resource file entry with reference.
                    $fs->delete_area_files($legacyfilealiasescontext->id,
                            $legacyfilealiasescontext->component, $legacyfilealiasescontext->filearea, 0);
                    // Need to transform alias into copy.
                    foreach ($legacyfilealiases as $legacyfilealias) {
                        // Retrieve course file reference.
                        if (($legacyfilealias->component == 'mod_resource' || $legacyfilealias->component == 'mod_folder' )
                         && $legacyfilealias->filearea == 'content' ) {
                              $reffile = file_storage::unpack_reference($legacyfilealias->reference);
                              // Make a mod_resource/folder copy of the coursefile reference.
                              // Retrieve coursefiles file.
                              $file = $fs->get_file($reffile['contextid'], $reffile['component'], $reffile['filearea'],
                                      $reffile['itemid'], $reffile['filepath'], $reffile['filename']);
                              $resfile = $fs->get_file($legacyfilealias->contextid, $legacyfilealias->component,
                                      $legacyfilealias->filearea, 0, $file->get_filepath(), $file->get_filename());
                            if (!$resfile || !isset($resfile)
                              || ($resfile->get_reference() != null && $resfile->get_reference() != '')) {
                                $filerecord = new stdClass();
                                $filerecord->contextid = $legacyfilealias->contextid;
                                $filerecord->component = $legacyfilealias->component;
                                $filerecord->filearea = $legacyfilealias->filearea;
                                $filerecord->userid = $file->get_userid();// Admin.
                                $filerecord->filepath = $file->get_filepath();
                                $filerecord->filename = $file->get_filename();
                                $thefile = $fs->create_file_from_storedfile($filerecord, $file);
                            }
                        }
                    }
                }
            }
        }
        try {
            $fs->delete_area_files($coursecontext->id, 'course', 'legacy', false);
        } catch (file_exception $fex) {
            $log .= '<div class=legacyfilesmigration_log>legacy course file aera files failed :'.$fex->getMessage();
            $log .= '<br>'.$fex->getTrace().'</div>';
        }
        $log .= "<br>";
        // -----------------------
        // End Migration process.
        // ----------------------------------------
        return true;
    }
}
