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
 * Get a list of all course administrators of courses that use the given plugin
 *
 * @package    report_plugins
 * @copyright  2020-21 Matthias Opitz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_login();
require_sesskey();

function render_plugin_courses($displayname, $pluginname, $plugintype) {
    global $DB;
    $o = '';

    switch ($plugintype) {
        case 'block' :
            $sql = "
                select
                c.id as courseid
                , c.shortname
                , c.fullname
                from {block_instances} bi
                join {context} cx on cx.id = bi.parentcontextid
                join {course} c on c.id = cx.instanceid
                where 1
                and cx.contextlevel = 50
                and bi.blockname = '$pluginname'
            ";
            break;
        case 'format' :
            $sql = "
                select 
                distinct c.id as courseid
                , c.shortname
                , c.fullname
                from {course} c
                where format = '$pluginname'
            ";
            break;
        case 'mod' :
            $sql = "
                select
                distinct c.id as courseid
                , c.shortname
                , c.fullname
                from {modules} m
                join {course_modules} cm on cm.module = m.id
                join {course} c on c.id = cm.course
                where 1
                and m.name = '$pluginname'
            ";
            break;
        default:
            $sql = '';
    }

    if ($sql == '') {
        return '';
    }
    $courses = $DB->get_records_sql($sql);
    $o .= render_displayname($displayname);
    $o .= render_courses_data($courses);

    return $o;
}
function render_displayname($displayname) {
    $o = '';
    $o .= html_writer::tag('div', html_writer::tag('h2', "Courses using the \"$displayname\" plugin"));
    return $o;
}
function render_courses_data($courses) {
    $o = '';
    $o .= html_writer::start_tag('table', ['class' => 'alternate']);
    $o .= html_writer::start_tag('tr');
    $o .= html_writer::tag('th', 'Course ID');
    $o .= html_writer::tag('th', 'Fullname');
    $o .= html_writer::tag('th', 'Admins', ['class' => 'admins-col']);
    $o .= html_writer::end_tag('tr');

    foreach ($courses as $course) {
        $context = context_course::instance($course->courseid);
        $o .= html_writer::start_tag('tr');
        $o .= html_writer::tag('td',
            html_writer::tag('a',
                $course->courseid,
                [
                    'href' => "/course/view.php?id=$course->courseid",
                    'target' => '_blank'
                ]
            )
        );
        $o .= html_writer::tag('td', $course->fullname);

//        $updaters = get_users_by_capability($context, 'moodle/course:coursecreator');
        $teachers = get_course_teachers($course->courseid);
//print_r($updaters);
        $o .= html_writer::start_tag('td', ['class' => 'admins-col']);
        foreach ($teachers as $teacher) {
            $o .= "$teacher->firstname $teacher->lastname ($teacher->role)<br>";
        }
        $o .= html_writer::end_tag('td');

        $o .= html_writer::end_tag('tr');

//        $o .= html_writer::tag('div', "$course->courseid => $course->fullname");

    }
    $o .= html_writer::end_tag('table');

    return $o;
}

function get_course_teachers ($courseid) {
    global $DB;
    $sql = "
        select
        u.id as userid
        , u.firstname
        , u.lastname
        , u.username
        , u.email
        , r.shortname as role
        from mdl_role_assignments ra
        join mdl_role r on r.id = ra.roleid
        join mdl_user u on u.id = ra.userid
        join mdl_context cx on cx.id = ra.contextid and contextlevel = 50
        where 1
        and r.archetype like '%teacher%'    
        and instanceid = $courseid
    ";

    return $DB->get_records_sql($sql);
}

function test() {
    global $COURSE;
    $context = context_course::instance($COURSE->id);

//    $participants = new \core_user\table\participants("user-index-participants-{$COURSE->id}");
    $users = get_enrolled_users($context,'moodle/course:update');
    print_r($users);
    $o = '';
    foreach ($users as $user) {
        foreach ($user as $key => $value)
        $o .= "$user->firstname $user->lastname<br>";
    }
    return $o;
}

$displayname = required_param('displayname', PARAM_RAW);
$pluginname = required_param('pluginname', PARAM_RAW);
$plugintype = required_param('plugintype', PARAM_RAW);

echo render_plugin_courses($displayname, $pluginname, $plugintype);
//print_r(test());