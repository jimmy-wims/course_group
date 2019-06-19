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
 * Displays the course_group
 *
 * @package    local_course_group
 * @copyright  2019 Jimmy WIMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/lib.php');

$mode = optional_param('mode', 1, PARAM_INT); //clicked button id
$courseid = required_param('id', PARAM_INT); //course id

include('function.php'); //include the file with the function which create the file

global $PAGE,$DB;

$params = array('id' => $courseid, 'mode' => $mode); //parameters of the page
$PAGE->set_url(new moodle_url('/local/course_group/index.php', $params)); //we create the url of the page
$PAGE->navbar->add(get_string('title', 'local_course_group')); //we add the plugin to the navigation bar

$tabsResults = array(); //array of bouton mode

$course = $DB->get_record('course', array('id' => $courseid));  //we search the course in the data base
require_login($course);
$context = context_course::instance($courseid);

$PAGE->set_heading($course->fullname); //set the heading of the page
$PAGE->set_title($course->fullname. ': ' .get_string('title', 'local_course_group')); //set the title of the page

//we look if the user is a manager, teacher, or editingteacher
 $sql = "SELECT * FROM mdl_role_assignments AS ra LEFT JOIN mdl_user_enrolments AS ue ON ra.userid = ue.userid LEFT JOIN mdl_role AS r ON ra.roleid = r.id LEFT JOIN mdl_context AS c ON c.id = ra.contextid LEFT JOIN mdl_enrol AS e ON e.courseid = c.instanceid AND ue.enrolid = e.id WHERE r.shortname=? AND ue.userid=? AND e.courseid=?";

$isStudent = $DB->get_records_sql($sql, array('student',$USER->id,$course->id));


//navigation array(student, cours)
$tabsResults[] = new tabobject(1, new moodle_url('/local/course_group/index.php',
    array('id' => $courseid, 'mode' => 1)), get_string('student','local_course_group'));
if(empty($isStudent))
    $tabsResults[] = new tabobject(2, new moodle_url('/local/course_group/index.php',
        array('id' => $courseid,'mode' => 2)), get_string('group','local_course_group'));

if($mode==1) //if the menu 1 (student) is choose
{
    if(empty($isStudent)) //if the user isn't a student
    {
        //we search all the students
        $sql = "SELECT us.id,us.firstname,us.lastname from mdl_user as us where us.id in(SELECT ra.userid FROM mdl_role_assignments AS ra LEFT JOIN mdl_user_enrolments AS ue ON ra.userid = ue.userid LEFT JOIN mdl_role AS r ON ra.roleid = r.id LEFT JOIN mdl_context AS c ON c.id = ra.contextid LEFT JOIN mdl_enrol AS e ON e.courseid = c.instanceid AND ue.enrolid = e.id WHERE r.shortname='student' AND e.courseid=?) ORDER BY us.firstname ASC";
    }
    else 
    {
        //we search the user who is connected
        $sql = "SELECT id,firstname,lastname from mdl_user where id=$USER->id";
    }

    $tabStudents=array();

    $students = $DB->get_records_sql($sql, array($course->id)); //we apply the sql request with the course id

    $affTab = html_writer::start_tag('table',array('class'=>'tabGroup')); //creation of the table
        $affTab = $affTab . "<tr>";
            $affTab = $affTab . "<th>".get_string('student','local_course_group')."</th>"; //creation of the header
            $affTab = $affTab . "<th>".get_string('group','local_course_group')."</th>";
        $affTab = $affTab . "</tr>";
        foreach ($students as $student) {   //for all the student
            $affTab = $affTab . "<tr>";
                $affTab = $affTab . "<td>$student->firstname $student->lastname</td>"; //the display of the name of the student
                $group = groups_get_user_groups($course->id, $student->id); //get the group of the student
                if(isset($group[0][0]))
                {
                    $groupname="";
                    for($i=0; $i<count($group[0]); $i++)
                    {
                        $groupname = $groupname . groups_get_group_name($group[0][$i]) . ", "; //store all the group of the student
                    }
                    $groupname=substr($groupname, 0, -2); 
                }
                else
                    $groupname = get_string('none','local_course_group'); //if student have no group 
                $tabStudents["$student->firstname $student->lastname"] = $groupname; //enter the name and the group in a array to create the file 'list_student'
                $affTab = $affTab . "<td>$groupname</td>"; //the display of the group
            $affTab = $affTab . "</tr>";
        }
    $affTab = $affTab . html_writer::end_tag('table');  

    if(!empty($_GET['download'])) //if the button download is clicked
    {
        create_Liststudent('list_students_'. $course->shortname.'.csv',$tabStudents); //creation and downloading of the file about list_student
    }
 
    echo $OUTPUT->header();
    echo "<H1><u><center>".get_string('title', 'local_course_group')."</center></u></H1>"; //the display of the second title 
   
    //the display of the navigation array
    echo $OUTPUT->tabtree($tabsResults, $mode); 
   
    //the display of the students table
    echo $affTab;

    //the display of the download button
    echo '<a href="index?id='.$course->id.'&download=1&mode='.$mode.'">'.get_string('download','local_course_group').'</a>';
}
else    //if the menu 2 (group) is choose
{
    if(empty($isStudent)) //if the user isn't a student
    {
        //we search all the groups
        $groups=groups_get_all_groups($course->id, 0,0,'g.*');

        $selectGroups=array(); //array for selected form
        foreach ($groups as $group) { 
            $selectGroups[]=$group->name; //we set the table for the selected form
        }

        $filename=""; //name of the file
        $pos=''; //position of the selected group int the seleceted form 
        if(isset($_GET['group'])&&$_GET['group']!='') //if a group is selected
        {
            $pos=$_GET['group'];
            foreach ($groups as $group) {   //we are looking for which group has been selected
                if($selectGroups[$pos]==$group->name)
                {
                    $groupid=$group->id; 
                    $filename=$group->name; 
                    break;
                }
             }
            //we search the info about the selected group
            $sql = "SELECT id,name from mdl_groups where id=?";
            $groups = $DB->get_records_sql($sql, array($groupid));
        }
        $listGroups=array();
        $down=true;

        $tabGroups=array();

        foreach ($groups as $group) {
            //we search the grouping of the group
            $sql="SELECT gping.name from mdl_groupings AS gping WHERE gping.id in (SELECT groupingid from mdl_groupings_groups where groupid=?)"; 
            $mainGroup = $DB->get_records_sql($sql, array($group->id));
            if(!empty($mainGroup)) //if there is a grouping 
            {
                foreach ($mainGroup as $main) {
                    $tabGroups[$main->name][$group->id]=$group->name;
                }
            }
            else
                $tabGroups[$group->name][$group->id]=$group->name;
        }

        $cpt=0;
        $affTab = html_writer::start_tag('table',array('class'=>'tabGroup'));
            foreach ($tabGroups as $mainGroup => $groups) {
                foreach ($groups as $groupid => $group) {
                    $students=groups_get_members($groupid,'u.*','lastname ASC'); //we search the student of the group
                    //if the group contain students or the user have not choose a group 
                    if(!empty($students) || !isset($_GET['group']) || $_GET['group']=='') 
                    {
                        if($cpt==0)
                            $affTab = $affTab . "<tr style=\"background:#FFFFFF\"><th colspan='2'>$mainGroup</th></tr>";
                        $cpt++;
                        $affTab = $affTab . "<tr style=\"background:#D0E4F5\">";
                        if($mainGroup==$group) //if the group have no grouping
                        {
                            $affTab = $affTab . "<td colspan=2>";
                        }
                        else
                        {
                            $affTab = $affTab . "<th>$group</th> <td>";
                        }
                        if(empty($students)) //if there is no student in the group
                        {
                            $affTab = $affTab . get_string('noMember','local_course_group');
                            $listGroups[$mainGroup][$group][] = get_string('noMember','local_course_group');
                        }
                        else
                        {
                            foreach ($students as $student) { //the display of all the name the students of the groups
                                $affTab = $affTab . "-$student->firstname $student->lastname <br/>";
                                $listGroups[$mainGroup][$group][] = "$student->firstname $student->lastname";
                            }
                            $affTab = $affTab . "</td></tr>";
                        }
                    }
                    elseif (isset($_GET['group']) && $_GET['group']!='') { //if a empty group is choose
                        $affTab = $affTab . get_string('noMember','local_course_group');
                        $down=false;
                    }
                }
                $cpt=0;
            }
        $affTab = $affTab . html_writer::end_tag('table');

        if(empty($groups)) //if the course has no group
        {
            $down=false; //the button "download" will not appear if $down=false
            $affTab = $affTab . get_string('noCourseGroup','local_course_group');
        }

        $filename=explode(' ',$filename); //we delete the space
        if(isset($filename[1])) //if filename contained space
            $filename=$filename[1];
        else
            $filename="";

        if(!empty($_GET['download'])) //if the button "download" is clicked
        {
            create_Listgroup('list_groups_'.$course->shortname.$filename.'.csv',$listGroups); //creation of the file 'listGroups'
        }

        echo $OUTPUT->header(); //the display of the moodle header
        echo $OUTPUT->tabtree($tabsResults, $mode); //the display of the navigation array

        //the code html for the selected form
        echo html_writer::start_tag('form', array('action' => 'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'].'', 'method' => 'get')); //creation of the selected form
        echo html_writer::start_div();
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id','value' => $course->id));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'mode','value' => $mode));
                
            echo html_writer::label("Groupe", 'menugroupe', false, array('class' => 'accesshide'));
            echo html_writer::select($selectGroups, "group","allgroup");

            echo html_writer::empty_tag('input', array('type' => 'submit', 'value'=>'Chercher'));
                
        echo html_writer::end_div();
        echo html_writer::end_tag('form');

        echo "<br/><br/>";

        echo $affTab; //the display of the groups table

        //link to download the file 'list_student'
        if($down) 
             echo '<a href="index?id='.$course->id.'&download=1&mode=$mode&group='.$pos.'">'.get_string('download','local_course_group').'</a>';
    }
    else
    {
        //if the user is a student the plugin will display an error message
        echo $OUTPUT->header();
       echo "<font size='1'><table class='xdebug-error xe-parse-error' dir='ltr' border='1' cellspacing='0' cellpadding='1'>
        <tr><th align='left' bgcolor='#f57900' colspan='5'><span style='background-color: #cc0000; color: #fce94f; font-size: x-large;'>( ! )</span><b>".get_string('access','local_course_group')."</b></th></tr>
        </table></font>";
    }
}
//the display of the default footer of moodle page
echo $OUTPUT->footer();
