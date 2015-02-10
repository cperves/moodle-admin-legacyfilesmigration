<?php

/**
 *
 * @package   tool_legacyfilesmigration
 * @copyright  2014 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


/** Include formslib.php */
require_once ($CFG->libdir.'/formslib.php');

/**
 * Legac files migration upgrade table display options
 *
 * @package   tool_legacyfilesmigration
 */
class tool_legacyfilesmigration_pagination_form extends moodleform {
    /**
     * Define this form - called from the parent constructor
     */
    function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata;

        $mform->addElement('header', 'general', get_string('coursesperpage', 'admin'));
        // visible elements
        $options = array(10=>'10', 20=>'20', 50=>'50', 100=>'100');
        $mform->addElement('select', 'perpage', get_string('coursesperpage', 'admin'), $options);

        // hidden params
        $mform->addElement('hidden', 'action', 'saveoptions');
        $mform->setType('action', PARAM_ALPHA);

        // buttons
        $this->add_action_buttons(false, get_string('migratetable', 'tool_legacyfilesmigration'));
    }
}

