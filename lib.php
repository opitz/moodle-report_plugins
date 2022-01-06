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
 * Library
 *
 * @package    report_plugins
 * @copyright  2021 QMUL / Matthias Opitz (m.opitz@qmul.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * get all block plugins and their uses
 *
 * @return array
 * @throws dml_exception
 */
function get_block_plugins() {
    global $DB;

    $sql = "
        select bi.blockname as name, count(cx.instanceid) as uses
        from {block_instances} bi
        join {context} cx on cx.id = bi.parentcontextid
        where 1
        and cx.contextlevel = 50
        group by bi.blockname
        order by bi.blockname
    ";

    return $DB->get_records_sql($sql);
}

function get_module_plugins() {
    global $DB;

    $sql = "
        select
        m.name, count(distinct cm.course) as uses
        from {modules} m
        join {course_modules} cm on cm.module = m.id
        where 1
        group by m.name
        order by m.name
    ";

    return $DB->get_records_sql($sql);
}

function get_format_plugins() {
    global $DB;

    $sql = "
        select 
        format as name, count(id) as uses
        from {course}
        group by format
        order by format
    ";

    return $DB->get_records_sql($sql);
}

/*
function report_plugins_before_footer() {
    echo html_writer::tag('div', 'The End', ['style' => 'text-align: center;']);
}
*/

function match_uses(&$plugins, $pluginuses) {
    foreach ($pluginuses as $pluginuse) {
        if (isset($plugins[$pluginuse->name])) {
            $plugins[$pluginuse->name]->uses = $pluginuse->uses;
        }
    }
}