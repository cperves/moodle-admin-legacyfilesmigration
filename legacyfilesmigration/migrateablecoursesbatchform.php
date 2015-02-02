<?php
/**
 * This file contains the forms to create and edit an instance of this module
 *
 * @package   tool_legacyfilesmigration
 
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


/** Include formslib.php */
require_once ($CFG->libdir.'/formslib.php');


/**
 * legacy files migration batch operations form
 *
 * @package   tool_legacyfilesmigration
 * @copyright  2014 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    http://www.cecill.info/licences/Licence_CeCILL_V2-en.html
 */
class tool_legacyfilesmigration_batchoperations_form extends moodleform {
    /**
     * Define this form - is called from parent constructor
     */
    function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata;

        $mform->addElement('header', 'general', get_string('batchoperations', 'tool_legacyfilesmigration'));
        // visible elements
        $mform->addElement('hidden', 'selectedcourses', '', array('class'=>'selectedcourses'));
        $mform->addElement('hidden', 'selectedowners', '', array('class'=>'selectedowners'));

        $mform->addElement('submit', 'migrateselected', get_string('migrateselected', 'tool_legacyfilesmigration'));
        $mform->addElement('submit', 'migrateall', get_string('migrateall', 'tool_legacyfilesmigration'));
    }

}

