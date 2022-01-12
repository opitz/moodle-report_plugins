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
 * Export plugin data to an Excel spreadsheet
 *
 * @package    report_plugins
 * @copyright  2022 Matthias Opitz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');

require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/plugins/classes/renderer.php');
require_once($CFG->dirroot.'/report/plugins/lib.php');
require_login();
//require_sesskey();

require_once("$CFG->libdir/phpspreadsheet/vendor/autoload.php");

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;

//echo "exporting to Excel here...";

$plugins = $DB->get_records('report_plugins');
$pluginsbytype = core_plugin_manager::instance()->get_plugins();

$plugins1 = [];
foreach ($plugins as $plugin) {
    $plugins1[$plugin->install_path] = $plugin;
}

foreach ($pluginsbytype as $plugintype => $plugins2) {
    // Get the uses of certain plugin types and match the values.
    switch ($plugintype) {
        case 'block':
            $pluginuses = get_block_plugins();
            match_uses($plugins2, $pluginuses);
            break;
        case 'format':
            $pluginuses = get_format_plugins();
            match_uses($plugins2, $pluginuses);
            break;
        case 'mod':
            $pluginuses = get_module_plugins();
            match_uses($plugins2, $pluginuses);
            break;
    }
    foreach ($plugins2 as $plugin2) {
        if ($plugin2->source == 'ext' ) {
            $install_path = substr($plugin2->rootdir, 14);
            if (isset($plugins1[$install_path])) {
                $plugins1[$install_path]->uses = $plugin2->uses;
                $plugins1[$install_path]->dependencies = $plugin2->dependencies;
            }
        }
    }
}

//$plugins3 = [];
//foreach ($plugins1 as $plugin) {
//    $plugins3[] = $plugin;
//}


$filename = 'Plugins_Test.xlsx';

export2excel($plugins, $filename);

function export2excel($plugins, $fileName) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $template = pluginTemplate();
    $pluginKeys = array_keys($template);

    $sheet->fromArray(
        $template,       // The data to set
        NULL,       // Array values with this value will not be set
        'A1'         // Top left coordinate of the worksheet range where
    //    we want to set these values (default is A1)
    );
    if ($plugins) foreach ($plugins as $key => $plugin) {
        $row = array();
        foreach ($pluginKeys as $pluginKey) {
            if ($pluginKey == 'dependencies') {
                $text = '';
                // If there are dependencies show them and the required version.
                if (count($plugin->$pluginKey) > 0) {
                    foreach ($plugin->$pluginKey as $dependency => $version) {
                        if (strlen($text) > 0) {
                            $text .= ", ";
                        }
                        $text .= "$dependency ($version)";
                    }
                }
                $row[$pluginKey] = $text;
            } else {
                $row[$pluginKey] = $plugin->$pluginKey;
            }
        }
        // Add the row to the sheet starting with row 2
        $sheet->fromArray($row,NULL, 'A'.((int)$key+1));
    }

    // Formatting
    $sheet->getColumnDimension('A')->setVisible(false); // hide the repository_url
    $sheet->getColumnDimension('C')->setVisible(false); // hide the github_url
    $sheet->getColumnDimension('D')->setVisible(false); // hide the install_path

    $sheet->getColumnDimension('B')->setWidth(40); // set width of title column
    $sheet->getColumnDimension('E')->setWidth(40); // set width of developer column
    $sheet->getColumnDimension('F')->setWidth(40); // set width of description column
    $sheet->getColumnDimension('H')->setWidth(80); // set width of wiki_url column
    $sheet->getColumnDimension('I')->setWidth(40); // set width of info_url column
    $sheet->getColumnDimension('J')->setWidth(40); // set width of requester column
    $sheet->getColumnDimension('K')->setWidth(40); // set width of requester column

    // Formatting the header
    $header = $sheet->getStyle('A1:O1');
    $header->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE);
    $header->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
    $header->getFill()->getStartColor()->setARGB('FFCCCCCC');
    $header->getFont()->setBold(true);

    // Set cells to wrap text
    $sheet->getStyle('H1:F999')->getAlignment()->setWrapText(true);
    // Set cells to vertical align at the top
    $sheet->getStyle('A:O')->getAlignment()->setVertical('top');

    // Finally write the file
    $writer = new Xlsx($spreadsheet);
//    $writer->save('Plugins_export_test.xlsx');

    // Send data headers so browsers will download not display
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
    $writer->save('php://output');
}
