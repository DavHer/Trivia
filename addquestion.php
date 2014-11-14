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
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

if (has_capability('mod/trivia:student', $context))  {
	redirect('studentview.php?id='.$id);
}

$PAGE->set_url('/mod/trivia/addquestion.php', array('id' => $cm->id));
$PAGE->set_title(format_string($trivia->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);


class form_addquestion extends moodleform{

	public function definition(){
		$mform = $this->_form;
		$mform->addElement('hidden', 'id');
		$mform->setType('courseid', PARAM_INT);
		
		$mform->addElement('text', 'name', 'Question', array('size'=>'100'));
		$mform->addElement('text', 'respuesta', 'Respuesta', array('size'=>'100'));
		$mform->addElement('text', 'puntos', 'Value', array('size'=>'10'));
		$mform->addElement('filepicker', 'xml', 'From XML', null, array('maxbytes' => '1000000', 'accepted_types' => '*'));
		
		$buttonarray[] = &$mform->createElement('submit','submitbutton','Add Question');
		$buttonarray[] = &$mform->createElement('reset', 'resetbutton', get_string('revert'));
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

echo $OUTPUT->heading('Edit Trivia');

$mform = new form_addquestion();

if($mform->is_cancelled()) {
	redirect($CFG->wwwroot.'/mod/trivia/teacherview.php?id='.$id);
} else if($data = $mform->get_data(true)) {
		//This is where you can process the $data you get from the form
		echo 'Saving Question';
		
		$xml = $mform->get_file_content('xml');	
		try{
			$xml = new SimpleXMLElement($xml);		
			$x = 0;
			
			for($x = 0 ; $x < $xml->pregunta->count() ; $x++){
				$record = new stdClass();
				$record->name = (string)$xml->pregunta[$x]->texto;
				$record->respuesta = (string)$xml->pregunta[$x]->respuesta;
				$record->puntos = (string)$xml->pregunta[$x]->puntos;
				$record->trivia = $trivia->id;				
				$DB->insert_record('pregunta',$record);				
			}
			redirect('teacherview.php?id='.$id);	
		}catch (Exception $e) {
			//
		}	
		$record = new stdClass();
		$record->name = $data->name;
		$record->respuesta = $data->respuesta;
		$record->puntos = $data->puntos;
		$record->trivia = $trivia->id;
		$DB->insert_record('pregunta',$record);
		redirect('teacherview.php?id='.$id);
		
} else {
	//This is where you should add the commands that display the page when 
	//displaying the form for first time (when page loads)
		
	$formdata = array('id' => $id); // Note this can be an array or an object.
	$mform->set_data($formdata);
	
	//Show Form
	$mform->display();	
	// Finish the page
	echo $OUTPUT->footer();
}


