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

    public function render_detailspage() {
        $o = '';
        $o .= html_writer::tag('div','', ['id' => 'details', 'class' => 'details']);
        return $o;
    }

    public function render_importpage() {
        $o = '';
        $o .= html_writer::start_tag('div',['id' => 'import-excel', 'class' => 'import']);

        $o .= html_writer::tag('h1', "Import Excel Data");
        $o .= html_writer::start_tag('form', array('method' => 'post',
            'action' => 'uploadexcel.php',
            'enctype' => 'multipart/form-data'));
        $o .= html_writer::start_tag('div', ['class' => 'form-group']);
        $o .= html_writer::tag('label', 'Choose File');
        $o .= html_writer::tag('input', '', ['id' => 'import-filename', 'class' => 'form-control', 'value' => '']);
        $o .= html_writer::tag('button', 'Upload', [
            'id' => 'import-btn',
            'type' => 'submit',
            'name' => 'submit',
            'class' => 'btn btn-success',
        ]);
        /*

        <h1>Upload Excel File</h1>
	<form method="POST" action="uploadexcel.php" enctype="multipart/form-data">
		<div class="form-group">
			<label>Choose File</label>
			<input type="file" name="uploadFile" class="form-control" />
		</div>
		<div class="form-group">
			<button type="submit" name="submit" class="btn btn-success">Upload</button>
		</div>
	</form>

*/
        $o .= html_writer::end_tag('div');
        return $o;
    }

    public function render_pluginsbytype($pluginsbytype) {
        $o = '';
        $o .= html_writer::start_tag('div', ['class' => 'plugins']);
        foreach ($pluginsbytype as $plugintype => $plugins) {
            $o .= $this->render_plugins_report($plugins, $plugintype);
        }
        $o .= html_writer::end_tag('div');
        return $o;
    }

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

        // The headers of the columns and what data to show.
        $columns = [
            'Full Name' => 'displayname',
            'Directory' => 'rootdir',
            'Version' => 'versiondb',
            'Release' => 'release',
            'Dependencies' => 'dependencies',
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
        $toggler = '<i class="toggle-type toggler-open fa fa-angle-down" type="' . $plugintype . '" style="cursor: pointer;"></i>';
        $toggler .= '<i class="toggle-type toggler-closed fa fa-angle-right" type="' . $plugintype . '" style="cursor: pointer; display: none;"></i>';
        $header = $toggler . ' ' . $typetitle[$plugintype] . " ($plugintype)";

        // Now put everything together.

        $o .= html_writer::start_tag('div', ['class' => "type-wrapper $plugintype"]);
        $o .= html_writer::tag('div', $this->heading($header), ['class' => "type-title $plugintype"]);
        $o .= $this->show_plugin_list($plugins, $plugintype, $columns);
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
    public function show_plugin_list($plugins, $plugintype, $columns) {
        $o='';
//        $o .= html_writer::start_tag('table', ['class' => 'alternate']);
        $o .= html_writer::start_tag('table', ['class' => 'lined']);
        $o .= html_writer::start_tag('tr', ['class' => "table-header $plugintype"]);

        // Build the table header.
        foreach ($columns as $column => $source) {
            $o .= html_writer::tag('th', $column, ['id' => $source]);
        }
        $o .= html_writer::end_tag('tr');
        foreach ($plugins as $plugin) {
            $o .= html_writer::start_tag('tr',
                [
                    'class' => "$plugin->type $plugin->name $plugin->source",
                    'pluginname' => $plugin->name,
                    'type' => $plugin->type
                ]
            );
            // Hide a column with the franken_style name oof the plugin to be used by JavaScript when showing details.
            $o .= html_writer::tag('td', $plugin->type . '_' . $plugin->name, ['class' => 'frankenstyle']);

            foreach ($columns as $key => $column) {
                // If the column shows the rootdir show only the relative part
                if ($column == 'rootdir') {
                    $text = substr($plugin->$column, 14);
                } else if ($column == 'dependencies') {
                    $text = '';
                    // If there are dependencies show them and the required version.
                    if (count($plugin->$column) > 0) {
                        foreach ($plugin->$column as $dependency => $version) {
                            $text .= "$dependency ($version)<br>";
                        }
                    }
                } else {
                    $text = $plugin->$column;
                }

                $o .= html_writer::tag('td', $text, ['class' => $columns[$key]]);
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
    public function render_navigation() {
        $o = '';
        // Navigation for the plugins page
        $o .= html_writer::start_tag('div', ['class' => 'plugins']);
        $o .= html_writer::start_tag('form', [
            'id' => 'export-excel2',
            'method' => 'post',
            'action' => 'export_excel.php'
        ]);
        $o .= html_writer::tag('div', 'Hide Core', ['id' => 'toggle-core', 'class' => 'btn btn-primary hide-core']);
        $o .= "&nbsp;";
        $o .= html_writer::tag('div', 'Import Excel Data', ['id' => 'import-excel', 'class' => 'btn btn-secondary import-excel']);
        $o .= "&nbsp;";
//        $o .= html_writer::tag('div', 'Export Excel Data', ['id' => 'export-excel', 'class' => 'btn btn-secondary export-excel']);

        $o .= html_writer::tag('input', '', [
            'type' => 'submit',
            'value' => 'Export Excel Data',
            'class' => 'btn btn-primary'
        ]);
        $o .= html_writer::end_tag('form');

        $o .= html_writer::end_tag('div');


        // Navigation for the details page
        $o .= html_writer::start_tag('div', ['class' => 'details', 'style' => 'display: none;']);
        $o .= html_writer::tag('div', 'Close', ['id' => 'close-details', 'class' => 'btn btn-primary']);
        $o .= "&nbsp;";
        $o .= html_writer::tag('div', 'Hide Admins', ['id' => 'toggle-admins', 'class' => 'btn btn-primary hide-admins']);
        $o .= html_writer::end_tag('div');

        $o .= html_writer::empty_tag('hr');
        return $o;
    }

}