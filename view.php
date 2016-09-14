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
 * znanium module main user interface
 *
 * @package    mod_znanium
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/znanium/lib.php");
require_once("$CFG->dirroot/mod/znanium/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // znanium instance id
$redirect = optional_param('redirect', 0, PARAM_BOOL);

if ($u) {  // Two ways to specify the module
    $znanium = $DB->get_record('znanium', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('znanium', $znanium->id, $znanium->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('znanium', $id, 0, false, MUST_EXIST);
    $znanium = $DB->get_record('znanium', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/znanium:view', $context);

// Completion and trigger events.
znanium_view($znanium, $course, $cm, $context);

$PAGE->set_url('/mod/znanium/view.php', array('id' => $cm->id));

// Make sure znanium exists before generating output - some older sites may contain empty znaniums
// Do not use PARAM_URL here, it is too strict and does not support general URIs!
/*
$extznanium = trim($znanium->bookid);
if (empty($extznanium) or $extznanium === 'http://') {
    znanium_print_header($znanium, $cm, $course);
    znanium_print_heading($znanium, $cm, $course);
    znanium_print_intro($znanium, $cm, $course);
    notice(get_string('invalidstoredznanium', 'znanium'), new moodle_url('/course/view.php', array('id'=>$cm->course)));
    die;
}
unset($extznanium);
*/
$displaytype = znanium_get_final_display_type($znanium);
if ($displaytype == RESOURCELIB_DISPLAY_OPEN) {
    // For 'open' links, we always redirect to the content - except if the user
    // just chose 'save and display' from the form then that would be confusing
    if (strpos(get_local_referer(false), 'modedit.php') === false) {
        $redirect = true;
    }
}

if ($redirect) {
    // coming from course page or znanium index page,
    // the redirection is needed for completion tracking and logging
    $fullznanium = str_replace('&amp;', '&', znanium_get_full_znanium($znanium, $cm, $course));

    if (!course_get_format($course)->has_view_page()) {
        // If course format does not have a view page, add redirection delay with a link to the edit page.
        // Otherwise teacher is redirected to the external znanium without any possibility to edit activity or course settings.
        $editznanium = null;
        if (has_capability('moodle/course:manageactivities', $context)) {
            $editznanium = new moodle_url('/course/modedit.php', array('update' => $cm->id));
            $edittext = get_string('editthisactivity');
        } else if (has_capability('moodle/course:update', $context->get_course_context())) {
            $editznanium = new moodle_url('/course/edit.php', array('id' => $course->id));
            $edittext = get_string('editcoursesettings');
        }
        if ($editznanium) {
            redirect($fullznanium, html_writer::link($editznanium, $edittext)."<br/>".
                    get_string('pageshouldredirect'), 10);
        }
    }
    redirect($fullznanium);
}

switch ($displaytype) {
    case RESOURCELIB_DISPLAY_EMBED:
        znanium_display_embed($znanium, $cm, $course);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        znanium_display_frame($znanium, $cm, $course);
        break;
    default:
        znanium_print_workaround($znanium, $cm, $course);
        break;
}
