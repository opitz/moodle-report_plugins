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
 * Import data from an Excel spreadsheet
 *
 * @package    report_plugins
 * @copyright  2020-21 Matthias Opitz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');

require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/plugins/classes/renderer.php');
require_once($CFG->dirroot.'/report/plugins/lib.php');
require_login();
require_sesskey();
//echo "am anfang schuf der HErr himmel und erde...";
require_once("$CFG->libdir/phpspreadsheet/vendor/autoload.php");

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;


$inputFileName = 'test.xlsx';
$spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);

$sheet = $spreadSheet->getSheet(0)->toArray();
$columns = $sheet[0]; // Get the column titles and their order from the 1st line.
$plugins = [];
foreach ($sheet as $key => $row) {
    if ($key < 1) {
        continue; // Do not process the header row.
    }
    importPluginData($row, $columns);
}

/*
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Hello World !');

$writer = new Xlsx($spreadsheet);
$writer->save('hello_world.xlsx');
*/
echo "Es ist vollbracht!";

function pluginTemplate() {
    return [
        "repository_url" => "Repository URL",
        "title" => "Title",
        "github_url" => "GitHub URL",
        "install_path" => "Install Path",
        "dependencies" => "Dependencies (min. version)",
        "developer" => "Developer",
        "qmul_plugin" => "QMUL Plugin",
        "description" => "Description",
        "plugin_url" => "Plugin URL",
        "wiki_url" => "Wiki URL",
        "info_url" => "Info URL",
        "requester" => "Requester",
        "year_added" => "Year added",
        "uses_number" => "Nr of Uses",
        "public" => "Public",
    ];
}

/**
 * Matching the column titles from the spreadsheet to data fields in the data table.
 *
 * @return string[]
 */
function column2field() {
    return [
        "Repository URL" => "repository_url",
        "Title" => "title",
        "Github Url" => "github_url",
        "nstall Path" => "nstall_ath",
        "Dependencies (min. version)" => "dependencies",
        "Developer" => "developer",
        "QMUL Plugin" => "qmul_plugin",
        "Description" => "description",
        "Plugin URL" => "plugin_url",
        "Wiki URL" => "wiki_url",
        "Info URL" => "info_url",
        "Requester" => "requester",
        "Year added" => "year_added",
        "Nr of Uses" => "uses_number",
        "Public" => "public",
    ];
}

function pluginUploadFile(Request $request) {
    $request->validate([
        'file1' => 'required|mimes:xlsx|max:10000'
    ]);
    $file = $request->file('file1');
    $name = time().'.xlsx';
    $path = public_path('documents'.DIRECTORY_SEPARATOR);

    if ( $file->move($path, $name) ){
    }
}

function importPluginData0($row) {
    global $DB;
    $template = pluginTemplate();
    $pluginKeys = array_keys($template);

    // Using the install path as unique identifier for a plugin.
    $uid = 'install_path';
    $install_path = $row[array_search($uid, $pluginKeys)];
    $plugin = $DB->get_record('report_plugins', [$uid => $install_path]);
    if ($plugin && $install_path) {
        foreach ($pluginKeys as $index => $key) {
            $plugin->$key = $row[$index];
        }
        if ($plugin->title === NULL) {
            $plugin->title = 'n.a.';
        }
        if ($plugin->public != NULL) {
//                ddd($plugin->public);
            $plugin->public = '1';
        }
        return $DB->update_record('report_plugins', $plugin);
    }
    return false;
}
function importPluginData($row, $columns) {
    global $DB;
    $template = pluginTemplate();
    $pluginFields = array_keys($template);

    $columnfields = column2field();

    // Using the install path as unique identifier for a plugin.
    $uid = 'install_path';
    $install_path = $row[array_search($uid, $pluginFields)];
    $plugin = $DB->get_record('report_plugins', [$uid => $install_path]);
    if ($plugin && $install_path) {
//        $rval = array_search($template['title'], $columns);
//        $plugin->title = $row[$rval];
        foreach ($pluginFields as $field) {
            $plugin->$field = $row[array_search($template[$field], $columns)];
        }
        if ($plugin->title === NULL) {
            $plugin->title = 'n.a.';
        }
        if ($plugin->public != NULL) {
            $plugin->public = '1';
        }
        return $DB->update_record('report_plugins', $plugin);
    }
    return false;
}


