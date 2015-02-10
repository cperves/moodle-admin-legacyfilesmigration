<?php
/**
 * legacyfiles migration tool library functions
 *
 * @package    tool_legacyfilesmigration
 * @copyright  2014 unistra  {@link http://unistra.fr}
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
    function __construct($data) {
    	global $CFG;
        if (isset($data->migrateselected)) {
            $this->continuemessage = get_string('migrateselectedcount', 'tool_legacyfilesmigration', count(explode(',', $data->selectedcourses)));
            $this->continueurl = new moodle_url('/'.$CFG->admin.'/tool/legacyfilesmigration/batchmigrate.php', array('upgradeselected'=>'1', 'confirm'=>'1', 'sesskey'=>sesskey(), 'selected'=>$data->selectedcourses, 'selectedowners'=>$data->selectedowners));
        } else if (isset($data->migrateall)) {
            if (!tool_legacyfilesmigration_any_migrateable_courses()) {
                $this->continuemessage = get_string('noacoursestoupgrade', 'tool_legacyfilesmigration');
                $this->continueurl = '';
            } else {
                $this->continuemessage = get_string('migrateallconfirm', 'tool_legacyfilesmigration');
                $this->continueurl = new moodle_url('/'.$CFG->admin.'/tool/legacyfilesmigration/batchmigrate.php', array('migrateall'=>'1', 'confirm'=>'1', 'sesskey'=>sesskey()));
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

/**
 * Determine if there are any courses with legacyfiles that can't be migrated
 * @return boolean - Are there any course with legacy files to migrate
 */
function tool_legacyfilesmigration_any_migrateable_courses() {
    global $DB, $CFG;
    //TODO
    $courses = $DB->get_records_sql('select distinct c.id,c.fullname as name from {course} c inner join {context} cctx on cctx.instanceid=c.id and cctx.contextlevel=:coursecontext where cctx.id in (select distinct contextid from mdl_files where component=:component and filearea=:filearea) order by c.fullname',array('component'=>'course','filearea'=>'legacy','coursecontext'=>CONTEXT_COURSE));
    return $courses === false ? false:count($courses)>0;
}

/**
 * Load a list of all the courseids with legacy files that can be migrated
 * @return array of course ids
 */
function tool_legacyfilesmigration_load_all_migrateable_courseids() {
	global $DB, $CFG;
	$ids = $DB->get_records_sql('select distinct c.id,c.fullname as name from {course} c inner join {context} cctx on cctx.instanceid=c.id and cctx.contextlevel=:coursecontext where cctx.id in (select distinct contextid from mdl_files where component=:component and filearea=:filearea) order by c.fullname',array('component'=>'course','filearea'=>'legacy','coursecontext'=>CONTEXT_COURSE));
	return $ids === false? array():array_keys ($ids);
}

/**
 * Migrate a single course. This is used by both migrate single and migrate batch
 * @param $courseid course id
 * @param $userid user id
 */
function tool_legacyfilesmigration_migrate_course($courseid, $ownerusername) {
    global $CFG,$DB;
    $legacyfiles_migrater = new legacyfiles_migrate_manager();
    $course = $DB->get_record('course', array('id'=>$courseid));
    //TODO implement info
    if ($course) {
        $log = '';
        if(empty($ownerusername)){
        	$success=false;
        	$log .=get_string('unchoosenowner','tool_legacyfilesmigration',$courseid);
        }else{
	        $user = $DB->get_record('user', array('username'=>$ownerusername, 'deleted'=>0));
	        if($user){
	        	$success = $legacyfiles_migrater->migrate_files($course, $user, $log);
	        }else{
	        	$success = false;
	        	$log .= get_string('usernotfound', 'tool_legacyfilesmigration', $ownerusername);
	        	$info = new stdClass();
	        }
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


class legacyfiles_migrate_manager {

	/**
	 */
	private function migrate_activity_file($file,$user,$foldername){
		$usercontext = context_user::instance($user->id);
		$fs = get_file_storage();
		$isalias = 0;
		try {
			$isalias = $file->get_referencefileid();
		} catch (Exception $e) {
			//throw new file_reference_exception($repositoryid, $reference, null, null, $e->getMessage());
		}
		if($isalias == 0){ // not an alias
				
			if($fs->file_exists($usercontext->id, 'user', 'private', 0,
					$foldername . $file->get_filepath(), $file->get_filename())) {
					//file already exists in user private repository
				$thefile = $fs->get_file($usercontext->id, 'user', 'private', 0,
					$foldername . $file->get_filepath(), $file->get_filename());
				return $thefile;
				
			} else {
				//file does not exists in user private repository so create it
				//it means that it was previously a legacy file
				$filerecord = new stdClass();
				$filerecord->contextid = $usercontext->id;
				$filerecord->component = 'user';
				$filerecord->filearea = 'private';
				$filerecord->userid = $user->id;
				$filerecord->filepath =  $foldername. $file->get_filepath();
				$thefile = $fs->create_file_from_storedfile($filerecord, $file);
				return $thefile;
			}
		}
	}
	public function migrate_files($course, $user, & $log) {
		//TODO backup course befoer starting
		global $DB, $CFG, $USER;
		require_once($CFG->libdir.'/filestorage/file_storage.php');
		require_once($CFG->libdir.'/resourcelib.php');

		// is the user the admin? admin check goes here
		if (!is_siteadmin($USER->id)) {
			return false;
		}
		
		// should we use a shutdown handler to rollback on timeout?
		@set_time_limit(ASSIGN_MAX_LEGACYFILES_MIGRATION_TIME_SECS);
		
		//------------------------------
		//Migration Process
		//------------------------------
		$usercontext = context_user::instance($user->id);	
		$coursecontext = context_course::instance($course->id);
		$foldername = get_config('legacyfilesmigration','foldername');
		if($foldername!=null && !empty($foldername)){
			eval('$foldername="'.$foldername.'";');
		}else{
			$foldername = '/courses/'.$course->shortname.'_'.$course->id.'_'.$usercontext->id;	
		}
		
		//normalize name since made while storing filepath
		$foldername = clean_param($foldername, PARAM_PATH);
		
		
		$fs = get_file_storage();
		$count = 0;
		//retrieving legacyfiles and moving theme to user private repository
		
		$oldfiles = $fs->get_area_files($coursecontext->id, 'course', 'legacy', false, 'id', true);//include dirs to delete them
		
		
		
		foreach ($oldfiles as $oldfile) {
			if($oldfile->get_filename()!='.'){
				$filerecord = new stdClass();
				$filerecord->contextid = $usercontext->id;
				$filerecord->component = 'user';
				$filerecord->filearea = 'private';
				$filerecord->userid = $user->id;
				//need to copy all files because of resource usage with folders acting like sites
				//initial script was creating an obsolete folder and only populating a course folder with necessary files
				//but the usage of resource as site hosting were not working anymore
				$filerecord->filepath = $foldername. $oldfile->get_filepath();
				try{
				$fs->create_file_from_storedfile($filerecord, $oldfile);
				}catch(stored_file_creation_exception $sfcex){
					$log.='<div class=legacyfilesmigration_log_minor>file already existing '.$oldfile->get_filename().'</div>';
				}
				$log .= "<br>moving ".$oldfile->get_filename();
			}
			$count += 1;
		}
		
		//course legacy files will be removed after mod_resource treatment
		
		//looping on mod resource files on course
		// this in order to replace files by alias files on user private repository		
		$allactivities = get_array_of_activities($course->id);
		foreach ($allactivities as $activity) {
			//only working on mod resource files
			if($activity->mod == "resource") {
				//retrieve resource infos
				$resource = $DB->get_record_sql('select r.* from {resource} r inner join {course_modules} as cm on cm.instance=r.id where cm.id=:cmid', array('cmid'=>$activity->cm));
				$rescontext = context_module::instance($activity->cm);
				//only work on resource linked to legacy files
				if($resource->legacyfiles == RESOURCELIB_LEGACYFILES_ACTIVE){
					$files = $fs->get_area_files($rescontext->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', true);
					if (count($files) >= 1) {
						$allfile=array();
						foreach ($files as $file) {	
							//exclude filepath with / and filename .
							if($file->get_filepath()=='/' and $file->get_filename()=='.'){
								continue;
							}
						
							if($file->is_directory()){
								//need to recursyvely retrieve all file as resources stored in user private repository
								$includedfiles = $fs->get_directory_files($usercontext->id,'user','private',$file->get_itemid(),$foldername.$file->get_filepath(),true,false);
								foreach($includedfiles as $includedfile ){
									$allfile[] = $this->migrate_activity_file($includedfile , $user,'');
								}
								
							}else{
								$allfile[] = $this->migrate_activity_file($file, $user,$foldername);
							}
						}
						//deleting mod_resource files for the current activity
						try{
							$fs->delete_area_files($rescontext->id, 'mod_resource', 'content', false);
						}catch(file_exception $sfex){
							$log .= '<div class=legacyfilesmigration_log>Error : '.$sfex->getMessage();
							$log .= '<br>'.$sfex->getTraceAsString().'</div>';
						}
						
						//looping on the user private files called by current mod resource
						foreach ($allfile as $newfile) {
			
							$args = array();
							$args['type'] = 'user';
							$repos = repository::get_instances($args);
							$userrepository = reset($repos);
			
							$originalrecord = array(
									'contextid' => $newfile->get_contextid(),
									'component' => $newfile->get_component(),
									'filearea'  => $newfile->get_filearea(),
									'itemid'    => $newfile->get_itemid(),
									'filepath'  => $newfile->get_filepath(),
									'filename'  => $newfile->get_filename(),
							);
							//create an alias file on the existing file on user private repository
							$module_filepath = (0 === strpos($newfile->get_filepath(), $foldername)? substr($newfile->get_filepath(), strlen($foldername)) : $newfile->get_filepath());
							$module_filepath = empty($module_filepath)?'/':$module_filepath;
							//zip file while have sortorder to -1
							$sortorder = $newfile->get_sortorder();
							$sortorder+=1;
							$mimes = get_mimetypes_array();
							//pass sortorder to zip files because of case of site hosting and residual zip file unziped but not deleted
							if($newfile->get_mimetype() == $mimes['zip']['type'] || $newfile->get_mimetype() == $mimes['tgz']['type'] || $newfile->get_mimetype() == $mimes['gtar']['type'] || $newfile->get_mimetype() == $mimes['gz']['type'] || $newfile->get_mimetype() == $mimes['gzip']['type'] || $newfile->get_mimetype() == $mimes['hqx']['type'] || $newfile->get_mimetype() == $mimes['tar']['type']){
								$sortorder-=1;
							}
							//sortorder by incremented to prevent negative values when decrement. Negative values are transformed to positive values when editing and registering resource
							$newfilerecord = array(
									'contextid' => $rescontext->id,
									'component' => 'mod_resource',
									'filearea'  => 'content',
									'itemid'    => 0,
									'filepath'  => $module_filepath,
									'filename'  => $newfile->get_filename(),
									'sortorder' => $sortorder,
							);
							//if site hosting and never full followed, all necessary files are not filled in files tables for resource
							//in legacy files case they will be migrated when consulted threw resourcelib_try_file_migration (resourcelib.php)
							//need to follow folders
							$ref = $fs->pack_reference($originalrecord);
							try{
								if(!$newfile->is_directory()){
									if(!$fs->file_exists($newfilerecord['contextid'], $newfilerecord['component'], $newfilerecord['filearea'], $newfilerecord['itemid'], $newfilerecord['filepath'], $newfilerecord['filename'])){
										$newstoredfile = $fs->create_file_from_reference($newfilerecord, $userrepository->id, $ref);
									}
								}else{
									/*if(!$fs->file_exists($newfilerecord['contextid'], $newfilerecord['component'], $newfilerecord['filearea'], $newfilerecord['itemid'], $newfilerecord['filepath'], '.')){
										$newstoredfile = $fs->create_directory($newfilerecord['contextid'], $newfilerecord['component'], $newfilerecord['filearea'], $newfilerecord['itemid'], $newfilerecord['filepath']);
									}*/
								}
							}catch(stored_file_creation_exception $sfex){
								$log .= '<div class=legacyfilesmigration_log>Error while migrating file mod resource storedfilenotcreated : '.$sfex->getMessage();
								$log .= '<br>'.$sfex->getTraceAsString().'</div>';
							}
						}
						$resource->legacyfiles = RESOURCELIB_LEGACYFILES_NO;
						$DB->update_record('resource', $resource);
					}
				}
			}
		}
		//deleting course legacy area
		//in case of fail, course will have again legecy files
		if ($count) {
			try{
				$fs->delete_area_files($coursecontext->id, 'course', 'legacy', false);
			}catch(file_exception $fex){
				$log .= '<div class=legacyfilesmigration_log>legacy course file aera files failed :'.$fex->getMessage();
				$log.='<br>'.$fex->getTrace().'</div>';
			}
		}
		$log .= "<br>";
		//-----------------------
		// End Migration process
		//----------------------------------------
		//TODO check if rebuild course cache necessary

		return true;
	}


	
}
