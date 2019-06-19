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
 * Library file for course_group plugin
 *
 * @package    local_course_group
 * @copyright  2019 Jimmy WIMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Some file import.
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');


//----------------------Creation and the display of of the button---------------------------------------------
function local_course_group_extend_navigation ($nav) {
     global $PAGE;

    $url = new moodle_url('/local/course_group/index.php', array('id' => $PAGE->course->id));
    $coursenode = $nav->find($PAGE->course->id, $nav::TYPE_COURSE);
    $navtext =  get_string('title','local_course_group');
    $coursenode->add($navtext, $url,
        navigation_node::TYPE_SETTING, null, 'viewcoursestat', new pix_icon('i/group',"stat"));
}