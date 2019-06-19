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
 * function file for course_group plugin
 *
 * @package    local_course_group
 * @copyright  2019 Jimmy WIMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


//function to create the file "list_groups"
function create_Listgroup($name,$listGroups)
{    
    //adding the header line
    $contenue=get_string('group','local_course_group')."; ".get_string('subGroup','local_course_group')."; ".get_string('nameStudents','local_course_group'); 
    foreach ($listGroups as $mainGroup => $groups) {
        foreach ($groups as $group => $member) {
            $contenue=$contenue."\n"."$mainGroup; "; //adding the groups of the student
            if($group!=$mainGroup)  //if there is a sub-group
                $contenue=$contenue."$group; ";
            else
                $contenue=$contenue.get_string('none','local_course_group')."; ";
            for($i=0;$i<count($member);$i++)    
                $contenue=$contenue."$member[$i] "; //adding all the student of the groups
        }
    }

    header('Content-Type: application/csv');    //force download of the file
    header('Content-Disposition: attachment; filename='. $name);
    header('Pragma: no-cache');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    echo $contenue;

    exit;
}

 function create_ListStudent($name,$students)
  {
        $contenue=get_string('nameStudent','local_course_group')."; ".get_string('group','local_course_group'); //adding the header line
        foreach ($students as $nom => $group) {
            $contenue = $contenue ."\n"."$nom; $group";    //adding the name of student and their group
        }

        header('Content-Type: application/csv');    //force download of the file
        header('Content-Disposition: attachment; filename='. $name);
        header('Pragma: no-cache');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        echo $contenue;

    exit;
  }