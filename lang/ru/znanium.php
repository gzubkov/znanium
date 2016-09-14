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
 * Strings for component 'znanium', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    mod_znanium
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['clicktoopen'] = 'Нажмите {$a} для чтения.';
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['configframesize'] = 'When a web page or an uploaded file is displayed within a frame, this value is the height (in pixels) of the top frame (which contains the navigation).';
$string['configrolesinparams'] = 'Enable if you want to include localized role names in list of available parameter variables.';
$string['configsecretphrase'] = 'This secret phrase is used to produce encrypted code value that can be sent to some servers as a parameter.  The encrypted code is produced by an md5 value of the current user IP address concatenated with your secret phrase. ie code = md5(IP.secretphrase). Please note that this is not reliable because IP address may change and is often shared by different computers.';
$string['contentheader'] = 'Содержимое';
$string['createznanium'] = 'Создать ссылку на книгу из ЭБС znanium';
$string['displayoptions'] = 'Варианты отображения';
$string['displayselect'] = 'Отображение';
$string['displayselect_help'] = 'This setting, together with the URL file type and whether the browser allows embedding, determines how the URL is displayed. Options may include:

* Automatic - The best display option for the URL is selected automatically
* Embed - The URL is displayed within the page below the navigation bar together with the URL description and any blocks
* Open - Only the URL is displayed in the browser window
* In pop-up - The URL is displayed in a new browser window without menus or an address bar
* In frame - The URL is displayed within a frame below the navigation bar and URL description
* New window - The URL is displayed in a new browser window with menus and an address bar';
$string['displayselectexplain'] = 'Choose display type, unfortunately not all types are suitable for all URLs.';
$string['bookid'] = 'Идентификатор книги в ЭБС';
$string['framesize'] = 'Frame height';
$string['invalidstoredurl'] = 'Cannot display this resource, bookid is invalid.';
$string['chooseavariable'] = 'Choose a variable...';
$string['invalidznanium'] = 'Entered bookid is invalid';
$string['modulename'] = 'znanium';
$string['modulename_help'] = 'Модуль позволяет использовать книги из ЭБС znanium без повторной авторизации пользователей.';
$string['modulename_link'] = 'mod/znanium/view';
$string['modulenameplural'] = 'znaniums';
$string['page-mod-znanium-x'] = 'Any znanium module page';
$string['parameterinfo'] = '&amp;parameter=variable';
$string['parametersheader'] = 'znanium variables';
$string['parametersheader_help'] = 'Some internal Moodle variables may be automatically appended to the znanium. Type your name for the parameter into each text box(es) and then select the required matching variable.';
$string['pluginadministration'] = 'Настройки модуля znanium';
$string['pluginname'] = 'znanium';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['printintro'] = 'Отображать описание модуля';
$string['printintroexplain'] = 'Отображать описание модуля под содержимым?';
$string['rolesinparams'] = 'Include role names in parameters';
$string['serverznanium'] = 'Server URL';
$string['znanium:addinstance'] = 'Добавить новую ссылку на книгу из ЭБС znanium';
$string['znanium:view'] = 'Просмотреть книгу из ЭБС znanium';
