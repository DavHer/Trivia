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

if(isset($_POST["preguntaId"])){
	$pregunta = $_POST["preguntaId"];
}

if ($id) {
	$cm         = get_coursemodule_from_id('trivia', $id, 0, false, MUST_EXIST);
	$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
	$trivia  = $DB->get_record('trivia', array('id' => $cm->instance), '*', MUST_EXIST);
	$preguntasx = $DB->get_records('pregunta',array('trivia'=>$trivia->id));
	$respuestasx = $DB->get_records('respuesta_estudiante',array('trivia'=>$trivia->id , 'estudiante'=>$USER->id));
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
if (has_capability('mod/trivia:admin', $context))  {
	redirect('teacherview.php?id='.$id);
}

$PAGE->set_url('/mod/trivia/studentquestion.php', array('id' => $cm->id));
$PAGE->set_title(format_string($trivia->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

if(count($respuestasx) == count($preguntasx)){
	redirect('studentview.php?id='.$id);
}

class form_pregunta extends moodleform{

	public function definition(){
		$mform = $this->_form;
		$mform->addElement('hidden', 'id');		
		$mform->setType('courseid', PARAM_INT);
		
		$mform->addElement('hidden', 'preguntaId');
		$mform->setType('preguntaid', PARAM_INT);
		
		$mform->addElement('text', 'respuesta', 'Respuesta', array('size'=>'100'));
		
		$buttonarray[] = &$mform->createElement('submit','submitbutton','Next Question');
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

echo $OUTPUT->heading('TRIVIA');

$mform = new form_pregunta();

if($mform->is_cancelled()) {
	redirect('studentview.php?id='.$id);
} else if($data = $mform->get_data(true)) {
		//This is where you can process the $data you get from the form
		echo 'Saving Question';
		$record = new stdClass();
		$record->trivia = $trivia->id;
		$record->pregunta = $data->preguntaId;
		$record->respuesta = $data->respuesta;
		$record->estudiante = $USER->id;
		$DB->insert_record('respuesta_estudiante',$record);
		
		redirect($CFG->wwwroot.'/mod/trivia/studentquestion.php?id='.$id);
} else {
	//This is where you should add the commands that display the page when 
	//displaying the form for first time (when page loads)
	$preguntas = array();
	foreach($preguntasx as $pre){
		array_push($preguntas,$pre);
	}
	
	$respuestas = array();
	foreach($respuestasx as $res){
		array_push($respuestas,$res);
	}
	
	echo "<h1>Resolved Questions: " . count($respuestas) . "<h1></br>";
	$preg = 0;
	$x = 0;
	
	while($x == 0){
		$x = 1;
		$preg = rand(0,count($preguntas)-1);
		for($y = 1;$y < count($respuestas)+1;$y++){
			if($respuestas[$y]->pregunta == $preguntas[$preg]->id){
				$x = 0;
			}
		}
	}
	
	echo "<h2>Pregunta: " . $preguntas[$preg]->name . '? ('.  $preguntas[$preg]->puntos ."pts) <h2></br>";
		
	$formdata = array('id' => $id,'preguntaId' => $preguntas[$preg]->id);
	$mform->set_data($formdata);
	
	//Show Form
	$mform->display();	
	// Finish the page
	echo $OUTPUT->footer();
}


