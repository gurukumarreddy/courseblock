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
 * Course summary block
 *
 * @package    block_coursesummary
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com) @Guru Kumar Reddy G
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_coursesummary extends block_base {

    /**
     * @var bool Flag to indicate whether the header should be hidden or not.
     */
    private $headerhidden = true;

    function init() {
        $this->title = get_string('pluginname', 'block_coursesummary');
    }

    function applicable_formats() {
        return array('all' => true, 'mod' => false, 'tag' => false, 'my' => false);
    }

    function specialization() {
        // Page type starts with 'course-view' and the page's course ID is not equal to the site ID.
        if (strpos($this->page->pagetype, PAGE_COURSE_VIEW) === 0 && $this->page->course->id != SITEID) {
            $this->title = get_string('coursesummary', 'block_coursesummary');
            $this->headerhidden = false;
        }
    }

    function get_content() {
        global $CFG, $OUTPUT,$USER, $DB;
    $id = optional_param('id', 0, PARAM_INT);
    $params = array('id' => $id);
    $course = $DB->get_record('course', $params, '*', MUST_EXIST);



        if($this->content !== NULL) {
            return $this->content;
        }

        if (empty($this->instance)) {
            return '';
        }

        $this->content = new stdClass();
        $sql = "SELECT
                cm.id as moduleid,
            c.shortname AS 'Course',
            m.name AS activitytype,
            CASE
                WHEN m.name = 'assign'  THEN (SELECT name FROM mdl_assign WHERE id = cm.instance)
                WHEN m.name = 'assignment'  THEN (SELECT name FROM mdl_assignment WHERE id = cm.instance)
                WHEN m.name = 'book'  THEN (SELECT name FROM mdl_book WHERE id = cm.instance)
                WHEN m.name = 'chat'  THEN (SELECT name FROM mdl_chat WHERE id = cm.instance)
                WHEN m.name = 'choice'  THEN (SELECT name FROM mdl_choice WHERE id = cm.instance)
                WHEN m.name = 'data'  THEN (SELECT name FROM mdl_data WHERE id = cm.instance)
                WHEN m.name = 'feedback'  THEN (SELECT name FROM mdl_feedback WHERE id = cm.instance)
                WHEN m.name = 'folder'  THEN (SELECT name FROM mdl_folder WHERE id = cm.instance)
                WHEN m.name = 'forum' THEN (SELECT name FROM mdl_forum WHERE id = cm.instance)
                WHEN m.name = 'glossary' THEN (SELECT name FROM mdl_glossary WHERE id = cm.instance)
                WHEN m.name = 'imscp' THEN (SELECT name FROM mdl_imscp WHERE id = cm.instance)
                WHEN m.name = 'label'  THEN (SELECT name FROM mdl_label WHERE id = cm.instance)
                WHEN m.name = 'lesson'  THEN (SELECT name FROM mdl_lesson WHERE id = cm.instance)
                WHEN m.name = 'lti'  THEN (SELECT name FROM mdl_lti  WHERE id = cm.instance)
                WHEN m.name = 'page'  THEN (SELECT name FROM mdl_page WHERE id = cm.instance)
                WHEN m.name = 'quiz'  THEN (SELECT name FROM mdl_quiz WHERE id = cm.instance)
                WHEN m.name = 'resource'  THEN (SELECT name FROM mdl_resource WHERE id = cm.instance)
                WHEN m.name = 'scorm'  THEN (SELECT name FROM mdl_scorm WHERE id = cm.instance)
                WHEN m.name = 'survey'  THEN (SELECT name FROM mdl_survey WHERE id = cm.instance)
                WHEN m.name = 'url'  THEN (SELECT name FROM mdl_url  WHERE id = cm.instance)
                WHEN m.name = 'wiki' THEN (SELECT name FROM mdl_wiki  WHERE id = cm.instance)
                WHEN m.name = 'workshop' THEN (SELECT name FROM mdl_workshop  WHERE id = cm.instance)
               ELSE 'Other activity'
            END AS activityname,
            CASE
                WHEN cm.completion = 0 THEN '0 None'
                WHEN cm.completion = 1 THEN '1 Self'
                WHEN cm.completion = 2 THEN '2 Auto'
            END AS activtycompletiontype

            FROM mdl_course_modules cm 
            JOIN mdl_course c ON cm.course = c.id
            JOIN mdl_modules m ON cm.module = m.id
            WHERE
            c.id = $course->id";
        $records = $DB->get_records_sql($sql);
        $test = array();
        foreach($records as $record){
            $cmid = $record->moduleid;
            $activityname = "<a href=".$CFG->wwwroot."/mod/".$record->activitytype."/view.php?id=".$record->moduleid." target='_blank' >".$record->activityname."</a>";
            $com = $DB->get_record_sql("SELECT * FROM {course_modules_completion} WHERE coursemoduleid = $record->moduleid and userid = $USER->id and completionstate = 1");
            if(!empty($com)){
                $cd = date("d-M-Y",$com->timemodified);
            }else{
                $cd = 'Pending';
            }

            $test[] = $cmid.' '.$activityname.' '.$cd.' ';
        }

$this->content->text = implode("<br>",$test);

        $this->content->footer = '';

        return $this->content;
    }

    function hide_header() {
        return $this->headerhidden;
    }

}


