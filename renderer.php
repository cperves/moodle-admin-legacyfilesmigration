<?php
/**
 * Defines the renderer for the legacy files migration helper plugin.
 *
 * @package    tool_legacyfilesmigration
  * @copyright  2014 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


class tool_legacyfilesmigration_renderer extends plugin_renderer_base {

    /**
     * Render the index page.
     * @param string $detected information about what sort of site was detected.
     * @param array $actions list of actions to show on this page.
     * @return string html to output.
     */
    public function index_page($detected, array $actions) {
        $output = '';
        $output .= $this->header();
        $output .= $this->heading(get_string('pluginname', 'tool_legacyfilesmigration'));
        $output .= $this->box($detected);
        $output .= html_writer::start_tag('ul');
        foreach ($actions as $action) {
            $output .= html_writer::tag('li',
                    html_writer::link($action->url, $action->name) . ' - ' .
                    $action->description);
        }
        $output .= html_writer::end_tag('ul');
        $output .= $this->footer();
        return $output;
    }

    /**
     * Render a page that is just a simple message.
     * @param string $message the message to display.
     * @return string html to output.
     */
    public function simple_message_page($message) {
        $output = '';
        $output .= $this->header();
        $output .= $this->heading($message);
        $output .= $this->back_to_index();
        $output .= $this->footer();
        return $output;
    }

    /**
     * Render the confirm batch operation page
     * @param stdClass $data Submitted form data with list of assignments to upgrade
     * @return string html to output.
     */
    public function confirm_batch_operation_page(stdClass $data) {
        $output = '';
        $output .= $this->header();

        $output .= $this->heading(get_string('confirmbatchmigrate', 'tool_legacyfilesmigration'));
        $output .= $this->output->spacer(array(), true);

        $output .= $this->container_start('tool_legacyfilesmigration_confirmbatch');

        $output .= $this->render(new tool_legacyfilesmigration_batchoperationconfirm($data));
        $output .= $this->container_end();

        $output .= $this->back_to_index();
        $output .= $this->footer();
        return $output;
    }

    /**
     * Render the confirm batch continue / cancel links
     * @param tool_assignmentupgrade_batchoperationconfirm $confirm Wrapper class to determine the continue message and url
     * @return string html to output.
     */
    public function render_tool_legacyfilesmigration_batchoperationconfirm(tool_legacyfilesmigration_batchoperationconfirm $confirm) {
        $output = '';

        if ($confirm->continueurl) {
            $output .= $this->output->confirm($confirm->continuemessage, $confirm->continueurl, tool_legacyfilesmigration_url('listnotmigrated'));
        } else {
            $output .= $this->output->box($confirm->continuemessage);
            $output .= $this->output->continue_button(tool_assignmentupgrade_url('listnotmigrated'));
        }
        return $output;
    }

    /**
     * Render the list of course that still have legacy files to migrated.
     * @param tool_legacyfilesmigration_assignments_table $courses of data about courses.
     * @param tool_legacyfilesmigration_batchoperations_form $batchform Submitted form with list of courses to upgrade
     * @param tool_legacyfilesmigration_pagination_form $paginationform Form which contains the preferences for paginating the table
     * @return string html to output.
     */
    public function course_list_page(tool_legacyfilesmigration_courses_table $courses, tool_legacyfilesmigration_batchoperations_form $batchform, tool_legacyfilesmigration_pagination_form $paginationform) {
        $output = '';
        $output .= $this->header();
        $this->page->requires->js_init_call('M.tool_legacyfilesmigration.init_migrate_table', array());
        $this->page->requires->string_for_js('nocoursesselected', 'tool_legacyfilesmigration');
        $this->page->requires->string_for_js('coursidnotcollapse', 'tool_legacyfilesmigration');
        

        $output .= $this->heading(get_string('notmigratedtitle', 'tool_legacyfilesmigration'));
        $output .= $this->box(get_string('notmigratedintro', 'tool_legacyfilesmigration'));
        $output .= $this->output->spacer(array(), true);

        $output .= $this->container_start('tool_legacyfilesmigration_migratetable');

        $output .= $this->container_start('tool_legacyfilesmigration_paginationform');
        $output .= $this->moodleform($paginationform);
        $output .= $this->container_end();

        $output .= $this->flexible_table($courses, $courses->get_rows_per_page(), true);
        $output .= $this->container_end();

        if ($courses->anymigrateablecourses) {
            $output .= $this->container_start('tool_legacyfilesmigration_batchform');
            $output .= $this->moodleform($batchform);
            $output .= $this->container_end();
        }

        $output .= $this->back_to_index();
        $output .= $this->footer();
        return $output;
    }

    /**
     * Render the result of an course legacy files conversion
     * @param stdClass $coursesummary data about the course whom legacy files have to be upgraded.
     * @param bool $success Set to true if the outcome of the conversion was a success
     * @param string $log The log from the conversion
     * @return string html to output.
     */
    public function convert_course_result($coursesummary, $success, $log) {
        $output = '';

        $output .= $this->container_start('tool_legacyfilesmigration_result');
        $output .= $this->container(get_string('migratecoursesummary', 'tool_legacyfilesmigration', $coursesummary));
        if (!$success) {
            $output .= $this->container(get_string('migrationfailed', 'tool_legacyfilesmigration', $log),'legacyfilesmigration_log');
        } else {
            $output .= $this->container(get_string('migratecoursesuccess', 'tool_legacyfilesmigration'));
            if(!empty($log)){
            	$output .= $this->container('logs = '.$log);
            }
            $output .= $this->container(html_writer::link(new moodle_url('/course/view.php', array('id'=>$coursesummary->id)) ,get_string('viewcourse', 'tool_legacyfilesmigration')));
        }
        $output .= $this->container_end();

        return $output;
    }

    /**
     * Render the are-you-sure page to confirm a manual upgrade.
     * @param stdClass $assignmentsummary data about the assignment to upgrade.
     * @return string html to output.
     */
    public function convert_assignment_are_you_sure($assignmentsummary) {
        $output = '';
        $output .= $this->header();
        $output .= $this->heading(get_string('areyousure', 'tool_legacyfilesmigration'));

        $params = array('id' => $assignmentsummary->id, 'confirmed' => 1, 'sesskey' => sesskey());
        $output .= $this->confirm(get_string('areyousuremessage', 'tool_legacyfilesmigration', $assignmentsummary),
                new single_button(tool_legacyfilesmigration_url('migratesingle', $params), get_string('yes')),
                tool_assignmentupgrade_url('listnotmigrated'));

        $output .= $this->footer();
        return $output;
    }

    /**
     * Helper method dealing with the fact we can not just fetch the output of flexible_table
     *
     * @param flexible_table $table
     * @param int $rowsperpage
     * @param bool $displaylinks Show links in the table
     * @return string HTML
     */
    protected function flexible_table(flexible_table $table, $rowsperpage, $displaylinks) {

        $o = '';
        ob_start();
        $table->out($rowsperpage, $displaylinks);
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }

    /**
     * Helper method dealing with the fact we can not just fetch the output of moodleforms
     *
     * @param moodleform $mform
     * @return string HTML
     */
    protected function moodleform(moodleform $mform) {

        $o = '';
        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }


    /**
     * Render a link in a div, such as the 'Back to plugin main page' link.
     * @param string|moodle_url $url the link URL.
     * @param string $text the link text.
     * @return string html to output.
     */
    public function end_of_page_link($url, $text) {
        return html_writer::tag('div', html_writer::link($url, $text), array('class' => 'mdl-align'));
    }

    /**
     * Output a link back to the plugin index page.
     * @return string html to output.
     */
    public function back_to_index() {
        return $this->end_of_page_link(tool_legacyfilesmigration_url('listnotmigrated'), get_string('backtoindex', 'tool_legacyfilesmigration'));
    }
}
