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
 * Import Excel spreadsheet into plugin data
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

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    if (!strstr($_FILES['fileToUpload']['type'], 'spreadsheet')) {
        echo $_FILES["fileToUpload"]["name"] . " is not a valid Excel file - aborting!";
        $upLoadOk = 0;
        return 0;
    }

    // Read the spreadsheet.
    $spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES["fileToUpload"]["tmp_name"]);
    $sheet = $spreadSheet->getSheet(0)->toArray();
    $columns = $sheet[0]; // Get the column titles and their order from the 1st line.

    foreach ($sheet as $key => $row) {
        if ($key < 1) {
            continue; // Do not process the header row.
        }
        importPluginData($row, $columns);
    }

    redirect('index.php');
}


function importPluginData($row, $columns) {
    global $DB;
    $template = pluginTemplate();
    $pluginFields = array_keys($template);

    // Using the install path as unique identifier for a plugin.
    $uid = 'install_path';
    $install_path = $row[array_search($uid, $pluginFields)];
    $plugin = $DB->get_record('report_plugins', [$uid => $install_path]);
    if ($plugin && $install_path) {
        foreach ($pluginFields as $field) {
            $plugin->$field = $row[array_search($template[$field], $columns)];
        }
        if ($plugin->title === NULL) {
            $plugin->title = 'n.a.';
        }
        if ($plugin->public != NULL) {
            $plugin->public = 1;
        }
        if ($plugin->qmul_plugin != NULL) {
            $plugin->qmul_plugin = 1;
        }
        return $DB->update_record('report_plugins', $plugin);
    }
    return false;
}
