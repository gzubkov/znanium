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
 * Private znanium module utility functions
 *
 * @package    mod_znanium
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/znanium/lib.php");
require_once("$CFG->dirroot/blocks/iit/iit.class.php");


/**
 * This methods does weak znanium validation, we are looking for major problems only,
 * no strict RFE validation.
 *
 * @param $znanium
 * @return bool true is seems valid, false if definitely not valid znanium
 */
function znanium_appears_valid_znanium($bookid) {
    return true;
    /*if (preg_match('/^(\/|https?:|ftp:)/i', $url)) {
        // note: this is not exact validation, we look for severely malformed URLs only
        return (bool)preg_match('/^[a-z]+:\/\/([^:@\s]+:[^@\s]+@)?[a-z0-9_\.\-]+(:[0-9]+)?(\/[^#]*)?(#.*)?$/i', $url);
    } else {
        return (bool)preg_match('/^[a-z]+:\/\/...*$/i', $url);
    }*/
}

/**
 * Fix common znanium problems that we want teachers to see fixed
 * the next time they edit the resource.
 *
 * This function does not include any XSS protection.
 *
 * @param string $bookid
 * @return string
 */
function znanium_fix_submitted_bookid($bookid) {
    $bookid = trim($bookid);
    return $bookid;
}

/**
 * Return full znanium with all extra parameters
 *
 * This function does not include any XSS protection.
 *
 * @param string $znanium
 * @param object $cm
 * @param object $course
 * @param object $config
 * @return string znanium with & encoded as &amp;
 */
function znanium_get_full_znanium($znanium, $cm, $course, $config=null) {
    global $USER;
    global $DB;
    $parameters = empty($znanium->parameters) ? array() : unserialize($znanium->parameters);

    if (isloggedin()) {
        if ($USER->id <= 9) {
            $uid = 10;
        } else {
            $uid = $USER->id;
        }
        $login = $DB->get_record('znanium_login', array('userid'=>$uid), '*', MUST_EXIST);

        $iit = new Iit($USER->idnumber);

        return $iit->getZnaniumLink($znanium->bookid, $login);
    }

    return false;

/*
    // make sure there are no encoded entities, it is ok to do this twice
    $fullznanium = html_entity_decode($znanium->externalznanium, ENT_QUOTES, 'UTF-8');

    if (preg_match('/^(\/|https?:|ftp:)/i', $fullurl) or preg_match('|^/|', $fullurl)) {
        // encode extra chars in URLs - this does not make it always valid, but it helps with some UTF-8 problems
        $allowed = "a-zA-Z0-9".preg_quote(';/?:@=&$_.+!*(),-#%', '/');
        $fullurl = preg_replace_callback("/[^$allowed]/", 'url_filter_callback', $fullurl);
    } else {
        // encode special chars only
        $fullurl = str_replace('"', '%22', $fullurl);
        $fullurl = str_replace('\'', '%27', $fullurl);
        $fullurl = str_replace(' ', '%20', $fullurl);
        $fullurl = str_replace('<', '%3C', $fullurl);
        $fullurl = str_replace('>', '%3E', $fullurl);
    }

    // add variable url parameters
    if (!empty($parameters)) {
        if (!$config) {
            $config = get_config('url');
        }
        $paramvalues = url_get_variable_values($url, $cm, $course, $config);

        foreach ($parameters as $parse=>$parameter) {
            if (isset($paramvalues[$parameter])) {
                $parameters[$parse] = rawurlencode($parse).'='.rawurlencode($paramvalues[$parameter]);
            } else {
                unset($parameters[$parse]);
            }
        }

        if (!empty($parameters)) {
            if (stripos($fullurl, 'teamspeak://') === 0) {
                $fullurl = $fullurl.'?'.implode('?', $parameters);
            } else {
                $join = (strpos($fullurl, '?') === false) ? '?' : '&';
                $fullurl = $fullurl.$join.implode('&', $parameters);
            }
        }
    }

    // encode all & to &amp; entity
    $fullurl = str_replace('&', '&amp;', $fullurl);

    return $fullurl;
    */
}

/**
 * Unicode encoding helper callback
 * @internal
 * @param array $matches
 * @return string
 */
function znanium_filter_callback($matches) {
    return rawznaniumencode($matches[0]);
}

/**
 * Print znanium header.
 * @param object $znanium
 * @param object $cm
 * @param object $course
 * @return void
 */
function znanium_print_header($znanium, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$znanium->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($znanium);
    echo $OUTPUT->header();
}

/**
 * Print znanium heading.
 * @param object $znanium
 * @param object $cm
 * @param object $course
 * @param bool $notused This variable is no longer used.
 * @return void
 */
function znanium_print_heading($znanium, $cm, $course, $notused = false) {
    global $OUTPUT;
    echo $OUTPUT->heading(format_string($znanium->name), 2);
}

/**
 * Print znanium introduction.
 * @param object $znanium
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function znanium_print_intro($znanium, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($znanium->displayoptions) ? array() : unserialize($znanium->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($znanium->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'znaniumintro');
            echo format_module_intro('znanium', $znanium, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Display znanium frames.
 * @param object $znanium
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function znanium_display_frame($znanium, $cm, $course) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        znanium_print_header($znanium, $cm, $course);
        znanium_print_heading($znanium, $cm, $course);
        znanium_print_intro($znanium, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('znanium');
        $context = context_module::instance($cm->id);
        $exteznanium = znanium_get_full_znanium($znanium, $cm, $course, $config);
        $navznanium = "$CFG->wwwroot/mod/znanium/view.php?id=$cm->id&amp;frameset=top";
        $coursecontext = context_course::instance($course->id);
        $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
        $title = strip_tags($courseshortname.': '.format_string($znanium->name));
        $framesize = $config->framesize;
        $modulename = s(get_string('modulename','znanium'));
        $contentframetitle = s(format_string($znanium->name));
        $dir = get_string('thisdirection', 'langconfig');

        $extframe = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navznanium" title="$modulename"/>
    <frame src="$exteznanium" title="$contentframetitle"/>
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $extframe;
        die;
    }
}

/**
 * Print znanium info and link.
 * @param object $znanium
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function znanium_print_workaround($znanium, $cm, $course) {
    global $OUTPUT;

    znanium_print_header($znanium, $cm, $course);
    znanium_print_heading($znanium, $cm, $course, true);
    znanium_print_intro($znanium, $cm, $course, true);

    $fullznanium = znanium_get_full_znanium($znanium, $cm, $course);

    $display = znanium_get_final_display_type($znanium);
    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $jsfullznanium = addslashes_js($fullznanium);
        $options = empty($znanium->displayoptions) ? array() : unserialize($znanium->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $extra = "onclick=\"window.open('$jsfullznanium', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $extra = "onclick=\"this.target='_blank';\"";

    } else {
        $extra = '';
    }

    echo '<div class="urlworkaround">';
    print_string('clicktoopen', 'znanium', "<a href=\"$fullznanium\" $extra>$fullznanium</a>");
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}

/**
 * Display embedded znanium file.
 * @param object $znanium
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function znanium_display_embed($znanium, $cm, $course) {
    global $CFG, $PAGE, $OUTPUT;

    $fullznanium  = znanium_get_full_znanium($znanium, $cm, $course);
    $title    = $znanium->name;

    $link = html_writer::tag('a', $fullznanium, array('href'=>str_replace('&amp;', '&', $fullznanium)));
    $clicktoopen = get_string('clicktoopen', 'znanium', $link);
    $moodleznanium = new moodle_url($fullznanium);

    $mediarenderer = $PAGE->get_renderer('core', 'media');
    $embedoptions = array(
        core_media::OPTION_TRUSTED => true,
        core_media::OPTION_BLOCK => true
    );

    $code = resourcelib_embed_general($fullznanium, $title, $clicktoopen, $mimetype);

    znanium_print_header($znanium, $cm, $course);

    echo $code;

    echo $OUTPUT->footer();
    die;
}

/**
 * Decide the best display format.
 * @param object $znanium
 * @return int display type constant
 */
function znanium_get_final_display_type($znanium) {
    global $CFG;

    return RESOURCELIB_DISPLAY_OPEN;
/*
    if ($znanium->display != RESOURCELIB_DISPLAY_AUTO) {
        return $znanium->display;
    }

    // detect links to local moodle pages
    if (strpos($znanium->external2url, $CFG->wwwroot) === 0) {
        if (strpos($url->external2url, 'file.php') === false and strpos($url->external2url, '.php') !== false ) {
            // most probably our moodle page with navigation
            return RESOURCELIB_DISPLAY_OPEN;
        }
    }

    static $download = array('application/zip', 'application/x-tar', 'application/g-zip',     // binary formats
                             'application/pdf', 'text/html');  // these are known to cause trouble for external links, sorry
    static $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
                             'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
                             'video/quicktime', 'video/mpeg', 'video/mp4',
                             'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats,
                            );

    $mimetype = resourcelib_guess_url_mimetype($url->external2url);

    if (in_array($mimetype, $download)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (in_array($mimetype, $embed)) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
    */
}

/**
 * Get the parameters that may be appended to znanium
 * @param object $config znanium module config options
 * @return array array describing opt groups
 */
function znanium_get_variable_options($config) {
    global $CFG;

    $options = array();
    $options[''] = array('' => get_string('chooseavariable', 'znanium'));

    $options[get_string('course')] = array(
        'courseid'        => 'id',
        'coursefullname'  => get_string('fullnamecourse'),
        'courseshortname' => get_string('shortnamecourse'),
        'courseidnumber'  => get_string('idnumbercourse'),
        'coursesummary'   => get_string('summary'),
        'courseformat'    => get_string('format'),
    );

    $options[get_string('modulename', 'znanium')] = array(
        'znaniuminstance'     => 'id',
        'znaniumcmid'         => 'cmid',
        'znaniumname'         => get_string('name'),
        'znaniumidnumber'     => get_string('idnumbermod'),
    );

    $options[get_string('miscellaneous')] = array(
        'sitename'        => get_string('fullsitename'),
        'serverznanium'       => get_string('serverznanium', 'znanium'),
        'currenttime'     => get_string('time'),
        'lang'            => get_string('language'),
    );
    if (!empty($config->secretphrase)) {
        $options[get_string('miscellaneous')]['encryptedcode'] = get_string('encryptedcode');
    }

    $options[get_string('user')] = array(
        'userid'          => 'id',
        'userusername'    => get_string('username'),
        'useridnumber'    => get_string('idnumber'),
        'userfirstname'   => get_string('firstname'),
        'userlastname'    => get_string('lastname'),
        'userfullname'    => get_string('fullnameuser'),
        'useremail'       => get_string('email'),
        'usericq'         => get_string('icqnumber'),
        'userphone1'      => get_string('phone1'),
        'userphone2'      => get_string('phone2'),
        'userinstitution' => get_string('institution'),
        'userdepartment'  => get_string('department'),
        'useraddress'     => get_string('address'),
        'usercity'        => get_string('city'),
        'usertimezone'    => get_string('timezone'),
        'userurl'         => get_string('webpage'),
    );

    if ($config->rolesinparams) {
        $roles = role_fix_names(get_all_roles());
        $roleoptions = array();
        foreach ($roles as $role) {
            $roleoptions['course'.$role->shortname] = get_string('yourwordforx', '', $role->localname);
        }
        $options[get_string('roles')] = $roleoptions;
    }

    return $options;
}

/**
 * Get the parameter values that may be appended to znanium
 * @param object $znanium module instance
 * @param object $cm
 * @param object $course
 * @param object $config module config options
 * @return array of parameter values
 */
function znanium_get_variable_values($znanium, $cm, $course, $config) {
    global $USER, $CFG;

    $site = get_site();

    $coursecontext = context_course::instance($course->id);

    $values = array (
        'courseid'        => $course->id,
        'coursefullname'  => format_string($course->fullname),
        'courseshortname' => format_string($course->shortname, true, array('context' => $coursecontext)),
        'courseidnumber'  => $course->idnumber,
        'coursesummary'   => $course->summary,
        'courseformat'    => $course->format,
        'lang'            => current_language(),
        'sitename'        => format_string($site->fullname),
        'serverurl'       => $CFG->wwwroot,
        'currenttime'     => time(),
        'znaniuminstance'     => $znanium->id,
        'znaniumcmid'         => $cm->id,
        'znaniumname'         => format_string($znanium->name),
        'znaniumidnumber'     => $cm->idnumber,
    );

    if (isloggedin()) {
        $values['userid']          = $USER->id;
        $values['userusername']    = $USER->username;
        $values['useridnumber']    = $USER->idnumber;
        $values['userfirstname']   = $USER->firstname;
        $values['userlastname']    = $USER->lastname;
        $values['userfullname']    = fullname($USER);
        $values['useremail']       = $USER->email;
        $values['usericq']         = $USER->icq;
        $values['userphone1']      = $USER->phone1;
        $values['userphone2']      = $USER->phone2;
        $values['userinstitution'] = $USER->institution;
        $values['userdepartment']  = $USER->department;
        $values['useraddress']     = $USER->address;
        $values['usercity']        = $USER->city;
        $now = new DateTime('now', core_date::get_user_timezone_object());
        $values['usertimezone']    = $now->getOffset() / 3600.0; // Value in hours for BC.
        $values['userurl']         = $USER->url;
    }

    // weak imitation of Single-Sign-On, for backwards compatibility only
    // NOTE: login hack is not included in 2.0 any more, new contrib auth plugin
    //       needs to be createed if somebody needs the old functionality!
    if (!empty($config->secretphrase)) {
        $values['encryptedcode'] = znanium_get_encrypted_parameter($znanium, $config);
    }

    //hmm, this is pretty fragile and slow, why do we need it here??
    if ($config->rolesinparams) {
        $coursecontext = context_course::instance($course->id);
        $roles = role_fix_names(get_all_roles($coursecontext), $coursecontext, ROLENAME_ALIAS);
        foreach ($roles as $role) {
            $values['course'.$role->shortname] = $role->localname;
        }
    }

    return $values;
}

/**
 * BC internal function
 * @param object $znanium
 * @param object $config
 * @return string
 */
function znanium_get_encrypted_parameter($znanium, $config) {
    global $CFG;

    if (file_exists("$CFG->dirroot/local/externserverfile.php")) {
        require_once("$CFG->dirroot/local/externserverfile.php");
        if (function_exists('extern_server_file')) {
            return extern_server_file($znanium, $config);
        }
    }
    return md5(getremoteaddr().$config->secretphrase);
}

/**
 * Optimised mimetype detection from general znanium
 * @param $fullznanium
 * @param int $size of the icon.
 * @return string|null mimetype or null when the filetype is not relevant.
 */
function znanium_guess_icon($fullznanium, $size = null) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    /*if (substr_count($fullznanium, '/') < 3 or substr($fullznanium, -1) === '/') {
        // Most probably default directory - index.php, index.html, etc. Return null because
        // we want to use the default module icon instead of the HTML file icon.
        return null;
    }*/

    $icon = file_extension_icon($fullznanium, $size);
    $htmlicon = file_extension_icon('.htm', $size);
    $unknownicon = file_extension_icon('', $size);

    // We do not want to return those icon types, the module icon is more appropriate.
    if ($icon === $unknownicon || $icon === $htmlicon) {
        return null;
    }

    return $icon;
}
