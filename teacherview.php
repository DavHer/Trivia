<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once("$CFG->libdir/formslib.php");

if(isset($_GET["id"])){
	$id = $_GET["id"];
}

if(isset($_POST["id"])){
	$id = $_POST["id"];
}

if ($id) {
	$cm         = get_coursemodule_from_id('trivia', $id, 0, false, MUST_EXIST);
	$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
	$trivia  = $DB->get_record('trivia', array('id' => $cm->instance), '*', MUST_EXIST);
	$preguntas = $DB->get_records('pregunta',array('trivia' => $trivia->id));
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

if (has_capability('mod/trivia:student', $context))  {
	redirect('studentview.php?id='.$id);
}

$PAGE->set_url('/mod/trivia/teacherview.php', array('id' => $cm->id));
$PAGE->set_title(format_string($trivia->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

class form_teacher extends moodleform{

	public function definition(){
		$mform = $this->_form;
		$mform->addElement('hidden', 'id');
		$mform->setType('courseid', PARAM_INT);
		
		$buttonarray[] = &$mform->createElement('submit','submitbutton','Add Question');

		$buttonarray[] = &$mform->createElement('cancel');
		$mform->addGroup($buttonarray,'buttonar','',array(' '),false);
	}
	function validation($data, $files) {
        return array();
    }
}

echo $OUTPUT->header();

if ($trivia->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('trivia', $trivia, $cm->id), 'generalbox mod_introbox', 'triviaintro');
}

echo $OUTPUT->heading('TEACHER');

$mform = new form_teacher();

if($mform->is_cancelled()) {
	redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
} else if($data = $mform->get_data(true)) {
		//This is where you can process the $data you get from the form
		redirect('addquestion.php?id='.$id);
} else {
	//This is where you should add the commands that display the page when 
	//displaying the form for first time (when page loads)
	echo "Questions: (" . count($preguntas) . ")</br></br>";
	
	$x = 1;
	foreach ($preguntas as $pregunta) {
		echo $x . "-) " . $pregunta->name . "? (" . $pregunta->puntos . " points) - Answer: " . $pregunta->respuesta . "</br></br>";
		$x++;
	}

	
		
	$formdata = array('id' => $id); // Note this can be an array or an object.
	$mform->set_data($formdata);
	
	if((date("Y-m-d H:i:s",time())<date("Y-m-d H:i:s",$trivia->inicio))){
		$mform->display();
	}	
	// Finish the page
	echo $OUTPUT->footer();
}


