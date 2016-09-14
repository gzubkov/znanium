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
 * znanium module admin settings and defaults
 *
 * @package    mod_znanium
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_FRAME,
                                                           RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
                                   RESOURCELIB_DISPLAY_EMBED,
                                   RESOURCELIB_DISPLAY_OPEN,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('znanium/framesize',
        get_string('framesize', 'znanium'), get_string('configframesize', 'znanium'), 130, PARAM_INT));
    $settings->add(new admin_setting_configpasswordunmask('znanium/secretphrase', get_string('password'),
        get_string('configsecretphrase', 'znanium'), ''));
    $settings->add(new admin_setting_configcheckbox('znanium/rolesinparams',
        get_string('rolesinparams', 'znanium'), get_string('configrolesinparams', 'znanium'), false));
    $settings->add(new admin_setting_configmultiselect('znanium/displayoptions',
        get_string('displayoptions', 'znanium'), get_string('configdisplayoptions', 'znanium'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('znaniummodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('znanium/printintro',
        get_string('printintro', 'znanium'), get_string('printintroexplain', 'znanium'), 1));
    $settings->add(new admin_setting_configselect('znanium/display',
        get_string('displayselect', 'znanium'), get_string('displayselectexplain', 'znanium'), RESOURCELIB_DISPLAY_AUTO, $displayoptions));
    $settings->add(new admin_setting_configtext('znanium/popupwidth',
        get_string('popupwidth', 'znanium'), get_string('popupwidthexplain', 'znanium'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('znanium/popupheight',
        get_string('popupheight', 'znanium'), get_string('popupheightexplain', 'znanium'), 450, PARAM_INT, 7));
}
