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
 * Plugin report renderer.
 *
 * @package    report_plugins
 * @copyright  2021 QMUL / Matthias Opitz (m.opitz@qmul.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * Report plugin renderer for printing reports.
 *
 * @package    report_plugins
 * @copyright  2021 QMUL / Matthias Opitz (m.opitz@qmul.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_plugins_renderer extends plugin_renderer_base {

    public function render_plugins_report($plugins, $plugintype = false) {
        $o ='';
        $typetitle = [
            'block' => 'Course Block',
            'mod' => 'Course Modules',
            'assignsubmission' => 'Assign Submission',
            'assignfeedback' => 'Assign Feedback',
            'assignment' => 'Assignment',
            'booktool' => 'Book tool',
            'format' => 'Course Format',
            'quiz' => 'Quiz',
            'quizaccess' => 'Quiz Access',
            'qtype' => 'Question Type',
            'qbehaviour' => 'Question Behaviour',
            'qformat' => 'Question Format',
            'filter' => 'Filter',
            'theme' => 'Theme',
            'atto' => 'Atto Editor',
            'tinymce' => 'TinyMCE Editor',
            'enrol' => 'Enrolment',
            'auth' => 'Authentication',
            'tool' => 'Admin Tools',
            'profilefield' => 'Profile Field',
            'report' => 'Reports',
            'gradeexport' => 'Grade Export',
            'gradeimport' => 'Grade Import',
            'gradereport' => 'Grade Report',
            'gradingform' => 'Grading Form',
            'repository' => 'Repository',
            'plagiarism' => 'Plagiarism',
            'fileconverter' => 'Fileconverter',
            'local' => 'Local',
            'widgettype' => 'Widget Type',
        ];

        // Return nothing when the plugin type is not listed in $typetitle.
        if ($plugintype === false || !isset($typetitle[$plugintype])) {
            return $o;
        }

        // The headers of the columns to show and what data to show.
        $columns = [
            'Full Name' => 'displayname',
            'Directory' => 'rootdir',
            'Version' => 'versiondb',
            'Release' => 'release',
            'Uses' => 'uses'
        ];

        // Get the uses of certain plugin types and match the values.
        switch ($plugintype) {
            case 'block':
                $pluginuses = get_block_plugins();
                match_uses($plugins, $pluginuses);
                break;
            case 'format':
                $pluginuses = get_format_plugins();
                match_uses($plugins, $pluginuses);
                break;
            case 'mod':
                $pluginuses = get_module_plugins();
                match_uses($plugins, $pluginuses);
                break;
        }
        $header = $typetitle[$plugintype] . " ($plugintype)";

        // Now put everything together.
        $o .= html_writer::start_tag('div', ['class' => "type-wrapper "]);
        if ($header) {
            $o .= $this->heading($header);
        }
        $o .= $this->show_plugin_list($plugins, $columns);
        $o .= html_writer::empty_tag('hr');
        $o .= html_writer::end_tag('div');

        return $o;
    }
    /**
     * Show a list of plugins with the given columns
     *
     * @param $plugins
     * @param $columns
     * @return string
     */
    public function show_plugin_list($plugins, $columns) {
        $o='';
//        $o .= html_writer::start_tag('table', ['class' => 'alternate']);
        $o .= html_writer::start_tag('table', ['class' => 'lined']);
        $o .= html_writer::start_tag('tr', ['class' => 'table-header']);

        // Build the table header.
        foreach ($columns as $column => $source) {
            $o .= html_writer::tag('th', $column, ['id' => $source]);
        }
        $o .= html_writer::end_tag('tr');
        foreach ($plugins as $plugin) {
            $o .= html_writer::start_tag('tr', ['class' => $plugin->type."_$plugin->name $plugin->source"]);
            foreach ($columns as $key => $column) {
                $o .= html_writer::tag('td', $plugin->$column, ['class' => $columns[$key]]);
            }
            $o .= html_writer::end_tag("tr");
        }
        $o .= html_writer::end_tag("table");
        return $o;
    }

    /**
     * Return the HTML code for the navigation
     *
     * @return string
     */
    public function show_navigation() {
        $o = '';
        $o .= html_writer::tag('div', 'Hide Core', ['id' => 'toggle-core', 'class' => 'btn btn-primary hide-core']);
        $o .= html_writer::empty_tag('hr');

        return $o;
    }

}