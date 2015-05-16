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
 * Essay question definition class.
 *
 * @package    qtype
 * @subpackage timedrecording
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Represents a timedrecording question.
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_timedrecording_question extends question_with_responses {
    public $responseformat;
	public $graderinfo;
    public $graderinfoformat;
    
   

    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
    	question_engine::load_behaviour_class('manualgraded');
        return new qbehaviour_manualgraded($qa, $preferredbehaviour);
        
        //initially planned to use a custom behaviour class to prevent user
        //reloading page to circumvent time controls. shelved. Justin 20120528
        //question_engine::load_behaviour_class('timed');
        //return new qbehaviour_timed($qa, $preferredbehaviour);
    }

    /**
     * @param moodle_page the page we are outputting to.
     * @return qtype_timedrecording_format_renderer_base the response-format-specific renderer.
     */
    public function get_format_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_timedrecording', 'format_' . $this->responseformat);
    }
	
	/**
	*	This tells Moodle what fields to expect, in particular it tells it 
	*   to look for uploaded file URLs in the answer field
	*/
    public function get_expected_data() {
		if(!defined('question_attempt::PARAM_CLEANHTML_FILES')) {
				$expecteddata = array('answer' => question_attempt::PARAM_RAW_FILES);
		}else{
			$expecteddata = array('answer' => question_attempt::PARAM_CLEANHTML_FILES);
		}
		$expecteddata['answerformat'] = PARAM_FORMAT;
        return $expecteddata;
    }

    public function summarise_response(array $response) {
	
        if (isset($response['answer'])) {
            $formatoptions = new stdClass();
            $formatoptions->para = false;
            return html_to_text(format_text(
                    $response['answer'], FORMAT_HTML, $formatoptions), 0, false);
        } else {
            return null;
        }
    }

    public function get_correct_response() {
        return null;
    }

    public function is_complete_response(array $response) {
        return !empty($response['answer']);
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'answer') && ($this->attachments == 0 ||
                question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'attachments'));
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
     	//print_object($qa);
        if ($component == 'question' && $filearea == 'response_answer') {
		   //since we will put files in respnse_answer, this is likely to be always true.
		   return true;
		   
		 //if we are playing a media prompt, there is no need to restrict this, so we return true
		 //perhaps for the play only once question, we might look here
		 } else if ($component == 'qtype_timedrecording' && $filearea == 'mediaprompt') {
			return true;
        
		
		} else if ($component == 'qtype_timedrecording' && $filearea == 'graderinfo') {
            return $options->manualcomment;
			
        } else {
            return parent::check_file_access($qa, $options, $component,
                    $filearea, $args, $forcedownload);
					
        }
    }
}
