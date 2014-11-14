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
 * Prints a particular instance of trivia
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_trivia
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace trivia with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once("$CFG->libdir/formslib.php");

Global $USER, $COURSE;  

	if(isset($_POST["courseid"])){
		echo 'success';
		$id = '2';
	}else{	
		$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
		$n  = optional_param('n', 0, PARAM_INT);  // trivia instance ID - it should be named as the first character of the module	
	}
	
	if ($id) {
		$cm         = get_coursemodule_from_id('trivia', $id, 0, false, MUST_EXIST);
		$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
		$trivia  = $DB->get_record('trivia', array('id' => $cm->instance), '*', MUST_EXIST);
	} elseif ($n) {
		$trivia  = $DB->get_record('trivia', array('id' => $n), '*', MUST_EXIST);
		$course     = $DB->get_record('course', array('id' => $trivia->course), '*', MUST_EXIST);
		$cm         = get_coursemodule_from_instance('trivia', $trivia->id, $course->id, false, MUST_EXIST);
	} else {
		//error('You must specify a course_module ID or an instance ID');
	}
	
	
require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
if (has_capability('mod/trivia:admin', $context))  {
	redirect('teacherview.php?id='.$id);
}
if (has_capability('mod/trivia:student', $context))  {
	redirect('studentview.php?id='.$id);
}

add_to_log($course->id, 'trivia', 'view', "view.php?id={$cm->id}", $trivia->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/trivia/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($trivia->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('trivia-'.$somevar);

// Output starts here
echo $OUTPUT->header();



//redirect('teacherview.php?id='.$id);

echo $OUTPUT->footer();


