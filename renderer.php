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
 * Timed Recording question renderer class.
 *
 * @package    qtype
 * @subpackage timedrecording
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/filter/poodll/poodllresourcelib.php');
require_once($CFG->dirroot . '/filter/poodll/poodllfilelib.php');

/**
 * Generates the output for timedrecording questions.
 *
 * @copyright  Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_timedrecording_renderer extends qtype_renderer {



    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();
        $responseoutput = $question->get_format_renderer($this->page);

        // Answer field.
        $step = $qa->get_last_step_with_qt_var('answer');
        if (empty($options->readonly)) {
            $answer = $responseoutput->response_area_input('answer', $qa,
                    $step, 1, $options->context);

        } else {
            $answer = $responseoutput->response_area_read_only('answer', $qa,
                    $step, 1, $options->context);
        }

		
        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa),
                array('class' => 'qtext'));

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::tag('div', $answer, array('class' => 'answer'));
        $result .= html_writer::end_tag('div');

        return $result;
    }


  
    public function manual_comment(question_attempt $qa, question_display_options $options) {
        if ($options->manualcomment != question_display_options::EDITABLE) {
            return '';
        }

        $question = $qa->get_question();
        return html_writer::nonempty_tag('div', $question->format_text(
                $question->graderinfo, $question->graderinfo, $qa, 'qtype_timedrecording',
                'graderinfo', $question->id), array('class' => 'graderinfo'));
    }
}


/**
 * A base class to abstract out the differences between different type of
 * response format.
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_timedrecording_format_renderer_base extends plugin_renderer_base {
    /**
     * Render the students response when the question is in read-only mode.
     * @param string $name the variable name this input edits.
     * @param question_attempt $qa the question attempt being display.
     * @param question_attempt_step $step the current step.
     * @param int $lines approximate size of input box to display.
     * @param object $context the context teh output belongs to.
     * @return string html to display the response.
     */
    public abstract function response_area_read_only($name, question_attempt $qa,
            question_attempt_step $step, $lines, $context);

    /**
     * Render the students input area: ie show a recorder
     * @param string $name the variable name this input edits.
     * @param question_attempt $qa the question attempt being display.
     * @param question_attempt_step $step the current step.
     * @param int $lines approximate size of input box to display.
     * @param object $context the context teh output belongs to.
     * @return string html to display the response for editing.
     */
    public abstract function response_area_input($name, question_attempt $qa,
            question_attempt_step $step, $lines, $context);

    /**
     * @return string specific class name to add to the input element.
     */
    protected abstract function class_name();
}


/**
 * An timedrecording format renderer for timedrecordings for audio
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_timedrecording_format_audio_renderer extends plugin_renderer_base {
   

    protected function class_name() {
        return 'qtype_timedrecording_audio';
    }

	//This is not necessary, but when testing it can be handy to display this
	protected function textarea($response, $lines, $attributes) {
        $attributes['class'] = $this->class_name() . ' qtype_essay_response';
        $attributes['rows'] = $lines;
        $attributes['cols'] = 60;
        return html_writer::tag('textarea', s($response), $attributes);
	}

    
    
       protected function prepare_response_for_editing($name,
            question_attempt_step $step, $context) {
                        
        return $step->prepare_response_files_draft_itemid_with_text(
                $name, $context->id, $step->get_qt_var($name));
                
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {	
    		global $CFG,$PAGE;
   			//fetch file from storage and figure out URL
			$pathtofile="";
    		$storedfiles=$qa->get_last_qt_files($name,$context->id);
    		foreach ($storedfiles as $sf){
    			$pathtofile=$qa->get_response_file_url($sf);
    			break;
    		}
			
			//replace score textbox with a dropdown list
			//$PAGE->requires->yui2_lib('dom');
			//$PAGE->requires->yui2_lib('element');
			
			$jsmodule = array('name'     => 'qtype_timedrecording',    
					'fullpath' => '/question/type/timedrecording/module.js',    
					'requires' => array('base', 'io', 'node', 'json'),    
					'strings' => array(        
							array('choosegrade', 'qtype_timedrecording'),        
							array('novicelow', 'qtype_timedrecording'),        
							array('novicemid', 'qtype_timedrecording'),        
							array('novicehigh', 'qtype_timedrecording'),
							array('intermediatelow', 'qtype_timedrecording'),        
							array('intermediatemid', 'qtype_timedrecording'),        
							array('intermediatehigh', 'qtype_timedrecording'), 
							array('advancedlow', 'qtype_timedrecording'),        
							array('advancedmid', 'qtype_timedrecording'),        
							array('advancedhigh', 'qtype_timedrecording'), 
							array('distinguished', 'qtype_timedrecording'),        
							array('superior', 'qtype_timedrecording') 
							)
					);
					
			//JS dropdown grading thingy swapout js
			//This was a feature of the original timed recording, but I have removed it for now because
			//I needed to update the YUI code to YUI3, which I did. But it still needs some work. And
			//I doubt this grading scale is generically useful . So its disabled .. for now. Justin 20150516
			//$PAGE->requires->js_init_call('M.qtype_timedrecording.init_dropdown', array(),false,$jsmodule);
			
			//prepare audio player
			if($pathtofile!=""){
				 $files = fetchSimpleAudioPlayer('swf',$pathtofile,"http",400,25);
			}else{
				$files = "No recording found";
			}
			return $files;
    }


    public function response_area_input($name, $qa, $step, $lines, $context) {
    	global $USER,$CFG,$PAGE;
    	
    	//print_object($qa);
    	$usercontextid= context_user::instance( $USER->id)->id;
    	
		//prepare a draft file id for use
		list($draftitemid, $response) = $this->prepare_response_for_editing( $name, $step, $context);
		
		//prepare the tags for our hidden( or shown ) input
		$inputname = $qa->get_qt_field_name($name);
		//$inputname="answer";
		$inputid =  $inputname . '_id';
		
		//our answerfield
		$ret =	html_writer::empty_tag('input', array('type' => 'hidden','id'=>$inputid, 'name' => $inputname));
		//this is just for testing purposes so we can see the value the recorder is writing
		//$ret = $this->textarea($step->get_qt_var($name), $lines, array('name' => $inputname,'id'=>$inputid));
		
		
		//our answerfield draft id key
		$ret .=	html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $inputname . ':itemid', 'value'=> $draftitemid));
		
		//our answerformat
		$ret .= html_writer::empty_tag('input', array('type' => 'hidden','name' => $inputname . 'format', 'value' => 1));
	
		$q = $qa->get_question();
		$preparetime=$q->preparationtime;
		$recordtime=$q->recordingtime;
		if($q->autoforward){
			$autoforward='true';
		}else{
			$autoforward='false';
		}

		// get file system handle for fetching url to submitted media prompt (if there is one) 
		$fs = get_file_storage();
		$files = $fs->get_area_files($q->contextid, 'qtype_timedrecording', 'mediaprompt', $q->id);
		$mediaurl="";
		if($files && count($files)>0){
			$file = array_pop($files);
			$mediaurl = $qa->rewrite_pluginfile_urls('@@PLUGINFILE@@/' . $file->get_filename(), $file->get_component(),$file->get_filearea() , $file->get_itemid());
		}
		
		//init the JS
		$jsmodule = array('name'     => 'qtype_timedrecording',    
				'fullpath' => '/question/type/timedrecording/module.js',    
				'requires' => array('base')
				);
		$PAGE->requires->js_init_call('M.qtype_timedrecording.init', array(),false,$jsmodule);
		
		//fetch the appopriate recorder
		if($q->recorder=='red'){
			$recorder = $this->fetchRed5TimedRecorderForSubmission('swf','question',
				$inputid,$usercontextid ,'user','draft',$draftitemid,$preparetime,$recordtime,$autoforward,$mediaurl);
		}else{
			$recorder = $this->fetchMP3TimedRecorderForSubmission('swf','question',
				$inputid,$usercontextid ,'user','draft',$draftitemid,$preparetime,$recordtime,$autoforward,$mediaurl);
		
		
		}
		
		//return the html for the question
   		return $ret . $recorder;
    }

    
    function fetchMP3TimedRecorderForSubmission($runtime, $assigname, $updatecontrol="saveflvvoice",$contextid,$component,$filearea,$itemid,$preparetime,$recordtime,$autoforward,$mediaurl){
		global $CFG, $USER, $COURSE;

		//Set the microphone config params
		$micrate = $CFG->filter_poodll_micrate;
		$micgain = $CFG->filter_poodll_micgain;
		$micsilence = $CFG->filter_poodll_micsilencelevel;
		$micecho = $CFG->filter_poodll_micecho;
		$micloopback = $CFG->filter_poodll_micloopback;
		$micdevice = $CFG->filter_poodll_studentmic;
		
		//removed from params to make way for moodle 2 filesystem params Justin 20120213
		$width="600";
		$height="150";
		$poodllfilelib= $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';
		
		$autosubmit='true';
		$courseid = -1;
		$canpause=false;
		$saveformat="mp3";


		//Stopped using this 
		//$filename = $CFG->filter_poodll_filename;
		 $overwritemediafile = $CFG->filter_poodll_overwrite==1 ? "true" : "false" ;
		if ($updatecontrol == "saveflvvoice"){
			$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
		}else{
			$savecontrol = "";
		}
		
		//Get localised labels: 
		$secondslabel = get_string('secondslabel', 'qtype_timedrecording');
		$minutelabel = get_string('minutelabel', 'qtype_timedrecording');
		$minuteslabel = get_string('minuteslabel', 'qtype_timedrecording');
		$recordlabel = get_string('recordlabel', 'qtype_timedrecording');
		$stoplabel = get_string('stoplabel', 'qtype_timedrecording');
		$preptimelabel = get_string('preparationtime', 'qtype_timedrecording');
		$rectimelabel = get_string('recordingtime', 'qtype_timedrecording');
		$preptimeleftlabel = get_string('preparationtimeremaining', 'qtype_timedrecording');
		$rectimeleftlabel = get_string('recordingtimeremaining', 'qtype_timedrecording');
		
		$params = array();
		$params['course'] = $courseid;
		$params['updatecontrol'] = $updatecontrol;
		$params['uid'] = $USER->id;
		$params['rate'] = 22;//$micrate;
		$params['gain'] = 25;//$micgain;
		$params['prefdevice'] = $micdevice;
		$params['loopback'] = $micloopback;
		$params['echosupression'] = $micecho;
		$params['silencelevel'] = 10;
		$params['filename'] = "123456.flv";
		$params['assigName'] = $assigname;
		$params['course'] = $courseid;
		$params['updatecontrol'] = $updatecontrol;
		$params['saveformat'] = $saveformat;
		$params['posturl'] = $poodllfilelib;
		$params['p1'] = $updatecontrol;
		$params['p2'] = $contextid;
		$params['p3'] = $component;
		$params['p4'] = $filearea;
		$params['p5'] = $itemid;
		$params['autosubmit'] = $autosubmit;
		$params['canpause'] = $canpause;
		$params['preparetime'] = $preparetime;
		$params['recordtime'] = $recordtime;
		$params['autoforward'] = $autoforward;
		$params['secondslabel'] = $secondslabel;
		$params['minutelabel'] = $minutelabel;
		$params['minuteslabel'] = $minuteslabel;
		$params['recordlabel'] = $recordlabel;
		$params['stoplabel'] = $stoplabel;
		$params['preptimelabel'] = $preptimelabel;
		$params['rectimelabel'] = $rectimelabel;
		$params['preptimeleftlabel'] = $preptimeleftlabel;
		$params['rectimeleftlabel'] = $rectimeleftlabel;
		//callbackjs
		$params['callbackjs'] = 'M.qtype_timedrecording.callback';
		//mediaurl
		if($mediaurl && $mediaurl!=""){
			$params['mediaurl'] = $mediaurl;
		}
		//lang strings
		//fetch and merge lang params
		$langparams = filter_poodll_fetch_recorder_strings();
		$params = array_merge($params, $langparams);
			
		$returnString=  $this->fetchTimedRecorderEmbedCode('PoodllMP3TimedRecorder.lzx.swf10.swf',
							$params,$width,$height,'#CFCFCF');
							
		$returnString .= 	 $savecontrol;
							
		return $returnString ;
			
	
	}
    
    
	function fetchRed5TimedRecorderForSubmission($runtime, $assigname, $updatecontrol="saveflvvoice",$contextid,$component,$filearea,$itemid,$preparetime,$recordtime,$autoforward,$mediaurl){
		global $CFG, $USER, $COURSE;
		
		//Set the servername 
		$flvserver = $CFG->poodll_media_server;
		//Set the microphone config params
		$micrate = $CFG->filter_poodll_micrate;
		$micgain = $CFG->filter_poodll_micgain;
		$micsilence = $CFG->filter_poodll_micsilencelevel;
		$micecho = $CFG->filter_poodll_micecho;
		$micloopback = $CFG->filter_poodll_micloopback;
		$micdevice = $CFG->filter_poodll_studentmic;
		
		//removed from params to make way for moodle 2 filesystem params Justin 20120213
		$userid="dummy";
		$width="600";
		$height="150";
		$filename="12345"; 
		$poodllfilelib= $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';
		
		//Course ID should always be -1 for Moodle 2
		$courseid = -1;

		
		//set up auto transcoding (mp3) or not
		if($CFG->filter_poodll_audiotranscode){
			$saveformat = "mp3";
		}else{
			$saveformat = "flv";
		}
		
		//If no user id is passed in, try to get it automatically
		//Not sure if  this can be trusted, but this is only likely to be the case
		//when this is called from the filter. ie not from an assignment.
		if ($userid=="") $userid = $USER->username;
		
		//Stopped using this 
		//$filename = $CFG->filter_poodll_filename;
		 $overwritemediafile = $CFG->filter_poodll_overwrite==1 ? "true" : "false" ;
		if ($updatecontrol == "saveflvvoice"){
			$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
		}else{
			$savecontrol = "";
		}
		
		//Get localised labels: 
		$secondslabel = get_string('secondslabel', 'qtype_timedrecording');
		$minutelabel = get_string('minutelabel', 'qtype_timedrecording');
		$minuteslabel = get_string('minuteslabel', 'qtype_timedrecording');
		$recordlabel = get_string('recordlabel', 'qtype_timedrecording');
		$stoplabel = get_string('stoplabel', 'qtype_timedrecording');
		$preptimelabel = get_string('preparationtime', 'qtype_timedrecording');
		$rectimelabel = get_string('recordingtime', 'qtype_timedrecording');
		$preptimeleftlabel = get_string('preparationtimeremaining', 'qtype_timedrecording');
		$rectimeleftlabel = get_string('recordingtimeremaining', 'qtype_timedrecording');
		
		$params = array();
		
				$params['red5url'] = urlencode($flvserver);
				$params['overwritefile'] = $overwritemediafile;
				$params['rate'] = $micrate;
				$params['gain'] = $micgain;
				$params['prefdevice'] = $micdevice;
				$params['loopback'] = $micloopback;
				$params['echosupression'] = $micecho;
				$params['silencelevel'] = $micsilence;
				$params['filename'] = "123456.flv";
				$params['assigName'] = $assigname;
				$params['course'] = $courseid;
				$params['updatecontrol'] = $updatecontrol;
				$params['saveformat'] = $saveformat;
				$params['uid'] = $userid;
				//for file system in moodle 2
				$params['poodllfilelib'] = $poodllfilelib;
				$params['contextid'] = $contextid;
				$params['component'] = $component;
				$params['filearea'] = $filearea;
				$params['itemid'] = $itemid;
				$params['preparetime'] = $preparetime;
				$params['recordtime'] = $recordtime;
				$params['autoforward'] = $autoforward;
				$params['secondslabel'] = $secondslabel;
				$params['minutelabel'] = $minutelabel;
				$params['minuteslabel'] = $minuteslabel;
				$params['recordlabel'] = $recordlabel;
				$params['stoplabel'] = $stoplabel;
				$params['preptimelabel'] = $preptimelabel;
				$params['rectimelabel'] = $rectimelabel;
				$params['preptimeleftlabel'] = $preptimeleftlabel;
				$params['rectimeleftlabel'] = $rectimeleftlabel;
				
				
				if($mediaurl && $mediaurl!=""){
					$params['mediaurl'] = $mediaurl;
				}
			
				$returnString=  $this->fetchTimedRecorderEmbedCode('PoodLLTimedRecorder.lzx.swf9.swf',
									$params,$width,$height,'#CFCFCF');
									
				$returnString .= 	 $savecontrol;
									
				return $returnString ;
	}
	
	//This is a bit ugly. It is just copied out of poodllresourcelib.php
	//There, and here, it needs a rewrite.
	function fetchTimedRecorderEmbedCode($widget,$paramsArray,$width,$height, $bgcolor="#FFFFFF"){
		global $CFG, $PAGE, $EMBEDJSLOADED;
		
		//build the parameter string out of the passed in array
		$params="?";
		foreach ($paramsArray as $key => $value) {
			$params .= '&' . $key . '=' . $value;
		}
		
		//add in any common params
		$params .= '&debug=false&lzproxied=false'; 
		

		
		//added the global and conditional inclusion of embed js here because repo doesn't get the JS loaded in the header
		//In other cases the load code at top of this file is on time. Justin 20120704
		$embedcode="";
		if(!$EMBEDJSLOADED){
			$embedcode .= "<script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script> ";
			$EMBEDJSLOADED=true;
		}
		
		$retcode = "
			<table><tr><td class=\"fitvidsignore\">
			<script type=\'text/javascript\'>
				lzOptions = { ServerRoot: \'\'};
			</script> 
		   " . $embedcode . "
			<script type=\"text/javascript\">
	" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/question/type/timedrecording/flash/' . $widget . $params . 
			 '\', bgcolor: \'' . $bgcolor . '\', cancelmousewheel: true, allowfullscreen: true, width: \'' .$width . '\', height: \'' . $height . '\', id: \'lzapp_' . rand(100000, 999999) . '\', accessible: true});	
			
	' . "
			</script>
			<noscript>
				Please enable JavaScript in order to use this application.
			</noscript>
			</td></tr>
			</table>";
			
			return $retcode;
	}

}

/**
 * An timedrecording format renderer for timedrecordings for video
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_timedrecording_format_video_renderer extends qtype_timedrecording_format_audio_renderer {
    

    protected function class_name() {
        return 'qtype_timedrecording_video';
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {
				
			//fetch file from storage and figure out URL
    		$storedfiles=$qa->get_last_qt_files($name,$context->id);
    		foreach ($storedfiles as $sf){
    			$pathtofile=$qa->get_response_file_url($sf);
    			break;
    		}

			return fetchSimpleVideoPlayer('swf',$pathtofile,400,380,"http");
	
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
    	global $USER;
    	$usercontextid=get_context_instance(CONTEXT_USER, $USER->id)->id;
    	
		//prepare a draft file id for use
		list($draftitemid, $response) = $this->prepare_response_for_editing( $name, $step, $context);


		$inputname = $qa->get_qt_field_name($name);
		$inputid =  $inputname . '_id';
		
			//our answerfield
		$ret =	html_writer::empty_tag('input', array('type' => 'hidden','id'=>$inputid, 'name' => $inputname));
		//$ret = $this->textarea($step->get_qt_var($name), $lines, array('name' => $inputname,'id'=>$inputid));
		
		//our answerfield draft id key
		$ret .=	html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $inputname . ':itemid', 'value'=> $draftitemid));
		
		$ret .= html_writer::empty_tag('input', array('type' => 'hidden','name' => $inputname . 'format', 'value' => FORMAT_PLAIN));

       
		//the context id $context->id here is wrong, so we just use "5" because it works, why is it wrong ..? J 20120214
		return $ret . fetchVideoRecorderForSubmission('swf','question',$inputid, $usercontextid ,'user','draft',$draftitemid);
		return $ret;
		
    }
    
}