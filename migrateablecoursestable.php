<?php

/**
 * This file contains the definition for course table which subclassses easy_table
 *
 * @package   tool_legacyfilesmigration
 * @copyright  2014 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');
require_once ($CFG->dirroot.'/'.$CFG->admin.'/tool/legacyfilesmigration/locallib.php');

/**
 * Extends table_sql to provide a table of assignment submissions
 *
 * @package   tool_assignmentupgrade
 */
class tool_legacyfilesmigration_courses_table extends table_sql implements renderable {
    /** @var int $perpage */
    private $perpage = 10;
    /** @var int $rownum (global index of current row in table) */
    private $rownum = -1;
    /** @var renderer_base for getting output */
    private $output = null;
    /** @var boolean $anymigrateablecourses - True if there is one or more courses with legacy files to migrate */
    public $anymigrateablecourses = false;

    /**
     * This table loads the list of courses containg legacy files 
     *
     * @param int $perpage How many per page
     * @param int $rowoffset The starting row for pagination
     */
    function __construct($perpage, $rowoffset=0) {
        global $PAGE, $CFG;
        parent::__construct('tool_legacyfilesmigration_courses');
        $this->perpage = $perpage;
        $this->output = $PAGE->get_renderer('tool_legacyfilesmigration');

        $this->define_baseurl(new moodle_url('/'.$CFG->admin.'/tool/legacyfilesmigration/listnotmigrated.php'));

        $this->anymigrateablecourses = tool_legacyfilesmigration_any_migrateable_courses();

        // do some business - then set the sql
        if ($rowoffset) {
            $this->rownum = $rowoffset - 1;
        }

        
 		$params = array('component'=>'course','filearea'=>'legacy','coursecontext'=>CONTEXT_COURSE);
 		$fields='distinct c.id as id ,c.fullname as name';
 		$from = '{course} c inner join {context} cctx on cctx.instanceid=c.id and cctx.contextlevel=:coursecontext';
 		$where = 'cctx.id in (select distinct contextid from mdl_files where component=:component and filearea=:filearea)';
        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql('select count(distinct c.id) from {course} c inner join {context} cctx on cctx.instanceid=c.id and cctx.contextlevel=:coursecontext where cctx.id in (select distinct contextid from mdl_files where component=:component and filearea=:filearea)',array('component'=>'course','filearea'=>'legacy','coursecontext'=>CONTEXT_COURSE));

        $columns = array();
        $headers = array();

        $columns[] = 'select';
        $headers[] = get_string('select', 'tool_legacyfilesmigration') . '<div class="selectall"><input type="checkbox" name="selectall" title="' . get_string('selectall') . '"/></div>';
        $columns[] = 'id';
        $headers[] = get_string('courseid', 'tool_legacyfilesmigration');
        
        $columns[] = 'name';
        $headers[] = get_string('course');
        $columns[] = 'owner';
        $headers[] = get_string('owner','tool_legacyfilesmigration').'<div class="preselect_owner"><input type="checkbox" name="preselect_owner" id="preselect_owner" title="' . get_string('preselect_owner','tool_legacyfilesmigration') . '"/></div>'
        				.'<div class="empty_owner"><input type="text" name="empty_owner" id="empty_owner" title="' . get_string('empty_owner','tool_legacyfilesmigration') . '"/></div>';
     
        // set the columns
        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->sortable(true,'course');
        $this->no_sorting('select');
        $this->no_sorting('owner');
        
        
        
    }

    /**
     * Return the number of rows to display on a single page
     *
     * @return int The number of rows per page
     */
    function get_rows_per_page() {
        return $this->perpage;
    }

    /**
     * Format a link to the assignment instance
     *
     * @param stdClass $row
     * @return string
     */
    function col_name(stdClass $row) {
        return html_writer::link(new moodle_url('/course/view.php',
                array('id' => $row->id)), $row->name);
    }


    /**
     * Insert a checkbox for selecting the current row for batch operations
     *
     * @param stdClass $row
     * @return string
     */
    function col_select(stdClass $row) {
        return '<input type="checkbox" name="selectedcourse" value="' . $row->id . '"/>';
       
    }
    
    /**
     * choose a owner
     * @param stdClass $row
     * @return string
     */
    function col_owner(stdClass $row) {
    	global $DB;
    	$coursecontext = context_course::instance($row->id);
    	$owners = get_enrolled_users($coursecontext,'moodle/course:managefiles');
    	$owner_html='';
    	if($owners !== false && count($owners)>0){
    		foreach($owners as $owner){
    			$owner_html .= '<input type="radio" id="selectedowner_'.$row->id.'" value="'.$owner->username.'" '.(count($owners)==1?' checked ':'').' />'.$owner->username.'<br>';
    		}
    	}else{
    		$owner_html='	<label for="selectedownerusername_'.$row->id.'">'.get_string('username').'</label>'.'<br><input type="text" id="selectedownerusername_'.$row->id.'" />';
    	}
    	return $owner_html;
    }
}
