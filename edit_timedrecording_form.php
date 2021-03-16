<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.s
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines the editing form for the timedrecording question type.
 *
 * @package    qtype
 * @subpackage timedrecording
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * timed Recording question type editing form.
 *
 * @copyright  2012 timed Recording Question 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_timedrecording_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        $qtype = question_bank::get_qtype('timedrecording');
		
		//Response format: audio or video
        $mform->addElement('select', 'responseformat',
                get_string('responseformat', 'qtype_timedrecording'), $qtype->response_formats());
        $mform->setDefault('responseformat', 'audio');



        $mform->addElement('editor', 'questionbody', get_string('questionbody', 'qtype_timedrecording'),
            array('rows' => 10), $this->editoroptions);

        //The list of recorders
        /*
        $recorders =$qtype->available_recorders();
         $mform->addElement('select', 'recorder',
                get_string('recorder', 'qtype_timedrecording'), $recorders);
        $mform->setDefault('recorder', 'mp3');
        */
        $mform->addElement('hidden','recorder','split');
        $mform->setType('recorder',PARAM_TEXT);

        //The preparation time
        /*
    	$mform->addElement('duration', 'preparationtime', get_string('preparationtime', 'qtype_timedrecording'));
    	  $mform->setDefault('preparationtime', 20);
        */
        $mform->addElement('hidden','preparationtime',20);
        $mform->setType('preparationtime',PARAM_INT);
    	
    	$mform->addElement('duration', 'recordingtime', get_string('recordingtime', 'qtype_timedrecording'));    
         $mform->setDefault('recordingtime', 30);

         //Autoforward
        /*
        $mform->addElement('advcheckbox', 'autoforward',
                    get_string('autoforward', 'qtype_timedrecording'),get_string('autoforwarddetails', 'qtype_timedrecording'));
            $mform->setDefault('autoforward', 1);
        */
        $mform->addElement('hidden','autoforward',1);
        $mform->setType('autoforward',PARAM_INT);
			
		// audio as part of question resource
		$mform->addElement('filemanager', 'mediaprompt', get_string('mediaprompt', 'qtype_timedrecording'), null,
                    array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));

        $mform->addElement('editor', 'graderinfo', get_string('graderinfo', 'qtype_timedrecording'),
                array('rows' => 10), $this->editoroptions);
		
		//This was 1 for the original solution with custom grading
		//seems more intuitive to make it 10
		//$mform->setDefault('defaultmark', 11);
		$mform->setDefault('defaultmark', 10);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        if (empty($question->options)) {
            return $question;
        }
        $question->responseformat = $question->options->responseformat;
        $question->responsefieldlines = $question->options->responsefieldlines;
        $question->attachments = $question->options->attachments;
        
        $question->preparationtime = $question->options->preparationtime;
        $question->recordingtime = $question->options->recordingtime;
        $question->autoforward = $question->options->autoforward;
        $question->recorder= $question->options->recorder;
		

		
		//Set mediaprompt details, and configure a draft area to accept any uploaded/recorded files
		//all this and this whole method does, is to load existing files into a filearea
		//so it is not called when creating a new question, only when editing an existing one
		
		//best to use file_get_submitted_draft_itemid - because copying questions gets weird otherwise
		//$draftitemid =$question->options->mediaprompt;
		$draftitemid = file_get_submitted_draft_itemid('mediaprompt');

		file_prepare_draft_area($draftitemid, $this->context->id, 'qtype_timedrecording', 'mediaprompt', 
				!empty($question->id) ? (int) $question->id : null,
                array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));
		$question->mediaprompt = $draftitemid;

        //grader info
        $draftid = file_get_submitted_draft_itemid('graderinfo');
        $question->graderinfo = array();
        $question->graderinfo['text'] = file_prepare_draft_area(
            $draftid,           // draftid
            $this->context->id, // context
            'qtype_timedrecording',      // component
            'graderinfo',       // filarea
            !empty($question->id) ? (int) $question->id : null, // itemid
            $this->fileoptions, // options
            $question->options->graderinfo // text
        );
        $question->graderinfo['format'] = $question->options->graderinfoformat;
        $question->graderinfo['itemid'] = $draftid;

        //question body
        $draftid = file_get_submitted_draft_itemid('questionbody');
        $question->questionbody = array();
        $question->questionbody['text'] = file_prepare_draft_area(
            $draftid,           // draftid
            $this->context->id, // context
            'qtype_timedrecording',      // component
            'questionbody',       // filarea
            !empty($question->id) ? (int) $question->id : null, // itemid
            $this->fileoptions, // options
            $question->options->questionbody // text
        );
        $question->questionbody['format'] = $question->options->questionbodyformat;
        $question->questionbody['itemid'] = $draftid;

        return $question;
    }


    public function qtype() {
        return 'timedrecording';
    }
}
