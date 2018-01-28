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
 * Question type class for the timedrecording question type.
 *
 * @package    qtype
 * @subpackage timedrecording
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * The timedrecording question type.
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_timedrecording extends question_type {
   public function is_manual_graded() {
        return true;
    }

    public function response_file_areas() {
        return array('answer');
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_timedrecording_opts',
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }
	

    public function save_question_options($formdata) {
        global $DB;
        $context = $formdata->context;

        $options = $DB->get_record('qtype_timedrecording_opts', array('questionid' => $formdata->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $formdata->id;
            $options->id = $DB->insert_record('qtype_timedrecording_opts', $options);
        }

	 $options->recordingtime = $formdata->recordingtime;
	 $options->preparationtime = $formdata->preparationtime;
	 $options->autoforward = $formdata->autoforward;
	
	//"import_or_save_files" won't work, because it expects output from an editor which is an array with member itemid
	//the filemanager doesn't produce this, so need to use file save draft area directly
	//$options->mediaprompt = $this->import_or_save_files($formdata->mediaprompt,
    //            $context, 'qtype_timedrecording', 'mediaprompt', $formdata->id);
	
	file_save_draft_area_files($formdata->mediaprompt, $context->id, 'qtype_timedrecording',
                    'mediaprompt',  $formdata->id, array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));
     
      //save what is probably the itemid of the mediaprompt filearea
		$options->mediaprompt = $formdata->mediaprompt;
	 	$options->recorder = $formdata->recorder;
        $options->responseformat = $formdata->responseformat;
		$options->graderinfo = $this->import_or_save_files($formdata->graderinfo,
                $context, 'qtype_timedrecording', 'graderinfo', $formdata->id);
        $options->graderinfoformat = $formdata->graderinfo['format'];
        $options->questionbody = $this->import_or_save_files($formdata->questionbody,
            $context, 'qtype_timedrecording', 'questionbody', $formdata->id);
        $options->questionbodyformat = $formdata->questionbody['format'];
        $DB->update_record('qtype_timedrecording_opts', $options);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
	
        parent::initialise_question_instance($question, $questiondata);
		
        $question->responseformat = $questiondata->options->responseformat;
		$question->graderinfo = $questiondata->options->graderinfo;
        $question->graderinfoformat = $questiondata->options->graderinfoformat;
        $question->questionbody = $questiondata->options->questionbody;
        $question->questionbodyformat = $questiondata->options->questionbodyformat;
        $question->preparationtime = $questiondata->options->preparationtime;
        $question->recordingtime = $questiondata->options->recordingtime;
         $question->autoforward = $questiondata->options->autoforward;
		$question->mediaprompt = $questiondata->options->mediaprompt;
		$question->recorder = $questiondata->options->recorder;
    }

    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function response_formats() {
    
        return array(
            'audio' => get_string('formataudio', 'qtype_timedrecording'),
            'video' => get_string('formatvideo', 'qtype_timedrecording')
        );
    }
    
    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function available_recorders() {
    	$recorders = \filter_poodll\settingstools::fetch_html5_recorder_items();
    	$recorders['shadow']='Shadow';
    	$recorders['split']='Split';
    	return $recorders;
    }

    /**
     * @return array the choices that should be offered for the number of attachments.
     */
    public function attachment_options() {
        return array(
            0 => get_string('no'),
            1 => '1',
            2 => '2',
            3 => '3',
            -1 => get_string('unlimited'),
        );
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_timedrecording', 'graderinfo', $questionid);
        $fs->move_area_files_to_new_context($oldcontextid,
            $newcontextid, 'qtype_timedrecording', 'questionbody', $questionid);
		$fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_timedrecording', 'mediaprompt', $questionid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_timedrecording', 'graderinfo', $questionid);
        $fs->delete_area_files($contextid, 'qtype_timedrecording', 'questionbody', $questionid);
		$fs->delete_area_files($contextid, 'qtype_timedrecording', 'mediaprompt', $questionid);
    }
}
