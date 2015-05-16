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
 * Essay question type upgrade code.
 *
 * @package    qtype
 * @subpackage timedrecording
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Upgrade code for the timedrecording question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_timedrecording_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2011031000) {
        // Define table qtype_timedrecording_options to be created
        $table = new xmldb_table('qtype_timedrecording_options');

        // Adding fields to table qtype_timedrecording_options
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null);
        $table->add_field('responseformat', XMLDB_TYPE_CHAR, '16', null,
                XMLDB_NOTNULL, null, 'editor');
        $table->add_field('responsefieldlines', XMLDB_TYPE_INTEGER, '4', null,
                XMLDB_NOTNULL, null, '15');
        $table->add_field('attachments', XMLDB_TYPE_INTEGER, '4', null,
                XMLDB_NOTNULL, null, '0');
        $table->add_field('graderinfo', XMLDB_TYPE_TEXT, 'small', null,
                null, null, null);
        $table->add_field('graderinfoformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, '0');

        // Adding keys to table qtype_timedrecording_options
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('questionid', XMLDB_KEY_FOREIGN_UNIQUE,
                array('questionid'), 'question', array('id'));

        // Conditionally launch create table for qtype_timedrecording_options
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // timedrecording savepoint reached
        upgrade_plugin_savepoint(true, 2011031000, 'qtype', 'timedrecording');
    }

    if ($oldversion < 2011060300) {
        // Insert a row into the qtype_timedrecording_options table for each existing timedrecording question.
        $DB->execute("
                INSERT INTO {qtype_timedrecording_options} (questionid, responseformat,
                        responsefieldlines, attachments, graderinfo, graderinfoformat)
                SELECT q.id, 'editor', 15, 0, '', " . FORMAT_MOODLE . "
                FROM {question} q
                WHERE q.qtype = 'timedrecording'
                AND NOT EXISTS (
                    SELECT 'x'
                    FROM {qtype_timedrecording_options} qeo
                    WHERE qeo.questionid = q.id)");

        // timedrecording savepoint reached
        upgrade_plugin_savepoint(true, 2011060300, 'qtype', 'timedrecording');
    }
	
	//adding field for timedrecording mediaprompt
	 if ($oldversion < 2012061100) {
		
		// Define field questiontextformat to be added to question_order_sub
        $table = new xmldb_table('qtype_timedrecording_options');
        $field = new xmldb_field('mediaprompt', XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'autoforward');

        // Conditionally launch add field questiontextformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		// timedrecording savepoint reached
        upgrade_plugin_savepoint(true, 2012061100, 'qtype', 'timedrecording');
	 
	 
	 }


    // Table names length was toooo long 
    if ($oldversion < 2012062700) {
    	$table = new xmldb_table('qtype_timedrecording_options');	
		if($dbman->table_exists($table)){
			$dbman->rename_table( $table, 'qtype_timedrecording_opts', true, true);   
    	}  
    	// poodllrecording savepoint reached
        upgrade_plugin_savepoint(true, 2012062700, 'qtype', 'timedrecording');
    
    }
    
    // Added an MP3 recorder to the mix
     if ($oldversion < 2015051500) {
        // Define table qtype_timedrecording_options to be created
        $table = new xmldb_table('qtype_timedrecording_opts');
        $field = new xmldb_field('recorder', XMLDB_TYPE_CHAR, '16', null,
                XMLDB_NOTNULL, null, 'mp3');

 		// Conditionally launch add field recorder
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // timedrecording savepoint reached
        upgrade_plugin_savepoint(true, 2015051500, 'qtype', 'timedrecording');
    }

    return true;

}
