<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once("$CFG->libdir/formslib.php");

Global $USER, $COURSE;  

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
	$preguntas = $DB->get_records('pregunta',array('trivia'=>$trivia->id));
	$respuestas = $DB->get_records('respuesta_estudiante',array('trivia'=>$trivia->id , 'estudiante'=>$USER->id));
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
if (has_capability('mod/trivia:admin', $context))  {
	redirect('teacherview.php?id='.$id);
}

$PAGE->set_url('/mod/trivia/addquestion.php', array('id' => $cm->id));
$PAGE->set_title(format_string($trivia->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

class form_student extends moodleform{
	public function definition(){
		$mform = $this->_form;
		$mform->addElement('hidden', 'id');
		$mform->setType('courseid', PARAM_INT);		
					
		$buttonarray[] = &$mform->createElement('submit','submitbutton','Start Trivia');	
		
		$buttonarray[] = &$mform->createElement('cancel');
		$mform->addGroup($buttonarray,'buttonar','',array(' '),false);
	}
	
	function validation($data,$files){
		return array();
	}
}

class form_student_back extends moodleform{
	public function definition(){
		$mform = $this->_form;
		$mform->addElement('hidden', 'id');
		$mform->setType('courseid', PARAM_INT);
		
		$buttonarray[] = &$mform->createElement('cancel');
		$mform->addGroup($buttonarray,'buttonar','',array(' '),false);
	}
	
	function validation($data,$files){
		return array();
	}
}

echo $OUTPUT->header();

if ($trivia->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('trivia', $trivia, $cm->id), 'generalbox mod_introbox', 'triviaintro');
}

echo $OUTPUT->heading('WEALCOME TO TRIVIA');

date_default_timezone_set('America/Costa_Rica');

$mform = new form_student();

if($mform->is_cancelled()) {
	redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
} else if($data = $mform->get_data(true)) {
		//This is where you can process the $data you get from the form
		//redirect('addquestion.php?id='.$id);
		redirect($CFG->wwwroot.'/mod/trivia/studentquestion.php?id='.$id);
} else {
	//This is where you should add the commands that display the page when 
	//displaying the form for first time (when page loads)
	echo 'Current Time     : '.date("Y-m-d H:i:s",time()).'</br></br>';
	echo 'Trivia starts at : '.date("Y-m-d H:i:s",$trivia->inicio).'</br></br>';
		
	if(date("Y-m-d H:i:s",time())<date("Y-m-d H:i:s",$trivia->inicio)){
		echo $OUTPUT->heading("The Trivia has not started yet...! Please come back later...");
		//$mform = new form_student_back();
	}		
	
	$formdata = array('id' => $id); // Note this can be an array or an object.
	$mform->set_data($formdata);
	
	if(count($respuestas) == count($preguntas)){
		$total = 0;
		$optenidos = 0;
		foreach($respuestas as $respuesta){
			foreach($preguntas as $pregunta){
				if($respuesta->pregunta == $pregunta->id){
					if($respuesta->respuesta == $pregunta->respuesta){
						$obtenidos = $obtenidos + $pregunta->puntos;
					}
					$total = $total + $pregunta->puntos;
				}
			} 
		}
		$nota = $obtenidos * 100;
		$nota = $nota/$total;
		echo "<h2>NOTA: " . number_format($nota, 2)  . " <h2></br>";		
	}	
	
	if(!(date("Y-m-d H:i:s",time())<date("Y-m-d H:i:s",$trivia->inicio)) && (date("Y-m-d H:i:s",time())<date("Y-m-d H:i:s",$trivia->final))){
		if((count($respuestas) != count($preguntas))){
			//Show Form
			$mform->display();	
		}
	}	
	
	// Finish the page
	echo $OUTPUT->footer();
}


