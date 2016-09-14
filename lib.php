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
 * Mandatory public API of znanium module
 *
 * @package    mod_znanium
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in znanium module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function znanium_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return false; // было true
        case FEATURE_SHOW_DESCRIPTION:        return false; // true

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function znanium_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function znanium_reset_userdata($data) {
    return array();
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function znanium_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function znanium_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add znanium instance.
 * @param object $data
 * @param object $mform
 * @return int new znanium instance id
 */
function znanium_add_instance($data, $mform) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/znanium/locallib.php');

    $parameters = array();
    for ($i=0; $i < 100; $i++) {
        $parameter = "parameter_$i";
        $variable  = "variable_$i";
        if (empty($data->$parameter) or empty($data->$variable)) {
            continue;
        }
        $parameters[$data->$parameter] = $data->$variable;
    }
    $data->parameters = serialize($parameters);

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    $data->bookid = znanium_fix_submitted_bookid($data->bookid);

    $data->timemodified = time();
    $data->id = $DB->insert_record('znanium', $data);

    return $data->id;
}

/**
 * Update znanium instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function znanium_update_instance($data, $mform) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/znanium/locallib.php');

    $parameters = array();
    for ($i=0; $i < 100; $i++) {
        $parameter = "parameter_$i";
        $variable  = "variable_$i";
        if (empty($data->$parameter) or empty($data->$variable)) {
            continue;
        }
        $parameters[$data->$parameter] = $data->$variable;
    }
    $data->parameters = serialize($parameters);

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    $data->bookid = znanium_fix_submitted_bookid($data->bookid);

    $data->timemodified = time();
    $data->id           = $data->instance;

    $DB->update_record('znanium', $data);

    return true;
}

/**
 * Delete znanium instance.
 * @param int $id
 * @return bool true
 */
function znanium_delete_instance($id) {
    global $DB;

    if (!$znanium = $DB->get_record('znanium', array('id'=>$id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('znanium', array('id'=>$znanium->id));

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return cached_cm_info info
 */
function znanium_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/znanium/locallib.php");

    if (!$znanium = $DB->get_record('znanium', array('id'=>$coursemodule->instance),
            'id, name, display, displayoptions, bookid, parameters, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $znanium->name;

    //note: there should be a way to differentiate links from normal resources
    $info->icon = znanium_guess_icon($znanium->bookid, 24);

    $display = znanium_get_final_display_type($znanium);

    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $fullznanium = "$CFG->wwwroot/mod/znanium/view.php?id=$coursemodule->id&amp;redirect=1";
        $options = empty($znanium->displayoptions) ? array() : unserialize($znanium->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $info->onclick = "window.open('$fullznanium', '', '$wh'); return false;";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $fullznanium = "$CFG->wwwroot/mod/znanium/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->onclick = "window.open('$fullznanium'); return false;";

    }

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('znanium', $znanium, $coursemodule->id, false);
    }

    return $info;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function znanium_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-znanium-*'=>get_string('page-mod-znanium-x', 'znanium'));
    return $module_pagetype;
}

/**
 * Export znanium resource contents
 *
 * @return array of file content
 */
function znanium_export_contents($cm, $baseznanium) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/znanium/locallib.php");
    $contents = array();
    $context = context_module::instance($cm->id);

    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $znaniumrecord = $DB->get_record('znanium', array('id'=>$cm->instance), '*', MUST_EXIST);

    $fullznanium = str_replace('&amp;', '&', znanium_get_full_znanium($znaniumrecord, $cm, $course));
    $isznanium = clean_param($fullznanium, PARAM_URL);
    if (empty($isznanium)) {
        return null;
    }

    $znanium = array();
    $znanium['type'] = 'znanium';
    $znanium['filename']     = clean_param(format_string($znaniumrecord->name), PARAM_FILE);
    $znanium['filepath']     = null;
    $znanium['filesize']     = 0;
    $znanium['fileurl']      = $fullznanium;
    $znanium['timecreated']  = null;
    $znanium['timemodified'] = $znaniumrecord->timemodified;
    $znanium['sortorder']    = null;
    $znanium['userid']       = null;
    $znanium['author']       = null;
    $znanium['license']      = null;
    $contents[] = $znanium;

    return $contents;
}


/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
/*
function znanium_dndupload_register() {
    return array('types' => array(
                     array('identifier' => 'znanium', 'message' => get_string('createznanium', 'znanium'))
                 ));
}
*/
/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */

/*
function znanium_dndupload_handle($uploadinfo) {
    // Gather all the required data.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '<p>'.$uploadinfo->displayname.'</p>';
    $data->introformat = FORMAT_HTML;
    $data->bookid = clean_param($uploadinfo->content, PARAM_URL);
    $data->timemodified = time();

    // Set the display options to the site defaults.
    $config = get_config('znanium');
    $data->display = $config->display;
    $data->popupwidth = $config->popupwidth;
    $data->popupheight = $config->popupheight;
    $data->printintro = $config->printintro;

    return znanium_add_instance($data, null);
}
*/
/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $znanium        znanium object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function znanium_view($znanium, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $znanium->id
    );

    $event = \mod_znanium\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('znanium', $znanium);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}
