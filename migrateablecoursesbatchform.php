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
 * This file contains the forms to create and edit an instance of this module
 * @package    tool_legacyfilesmigration
 * @copyright  2017 unistra  {@link http://unistra.fr}
 * @author Celine Perves <cperves@unistra.fr>
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');


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
    public function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata;
        $mform->addElement('header', 'general', get_string('batchoperations', 'tool_legacyfilesmigration'));
        // Visible elements.
        $mform->addElement('hidden', 'selectedcourses', '', array('class' => 'selectedcourses'));
        $mform->addElement('hidden', 'copycoursefiles', '', array('class' => 'copycoursefiles'));
        $mform->addElement('hidden', 'selectedowners', '', array('class' => 'selectedowners'));
        $mform->setType('selectedcourses', PARAM_RAW);
        $mform->setType('copycoursefiles', PARAM_RAW);
        $mform->setType('selectedowners', PARAM_RAW);
        $mform->addElement('submit', 'migrateselected', get_string('migrateselected', 'tool_legacyfilesmigration'));
        $mform->addElement('checkbox', 'copycoursefilesforall', get_string('copycoursefilesforall', 'tool_legacyfilesmigration'));
        $mform->addElement('submit', 'migrateall', get_string('migrateall', 'tool_legacyfilesmigration'));
    }

}

