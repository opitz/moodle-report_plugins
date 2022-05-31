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
 * Get information about all installed plugin GIT submodules and store it in a database table
 *
 * @package    tool_plugindetails
 * @copyright  2022 Queen Mary University London / M.Opitz (m.opitz@qmul.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_login();
require_sesskey();

//$basePath = required_param('basePath', PARAM_RAW);
//$dirroot = $CFG->dirroot;
//$pluginPath = "../../../../"; // This is the root of the Moodle installation
$pluginPath = $CFG->dirroot.'/'; // This is the root of the Moodle installation
$theArray = [];

walk_dir($pluginPath,$theArray);

// Now write the results to the database
foreach ($theArray as $plugin) {
    echo update_plugin($plugin);
}

function walk_dir($dirPath, &$theArray) {
    $o = ''; $dirs =[];

    exec("ls $dirPath", $dirs);
    foreach ($dirs as $dir) {
  //      $o .= $dir . '<br>';
        if (!is_file($dirPath . $dir)) {
            if (!file_exists($dirPath . $dir . '/.git') || !is_file($dirPath . $dir . '/.git')) {
                walk_dir($dirPath . $dir . '/', $theArray);
            } else {
                // Get the data
                $plugin = new stdClass();
                $plugin->install_path = substr($dirPath . $dir, 12);
                $plugin->title = $dir;

                // Get the developer from version.php
                get_developer($plugin);
                get_github_url($plugin);
                $theArray[] = $plugin;
            }
        }
    }
    return $o;
}

function get_developer(&$plugin) {
    $file = fopen('../../../../' .$plugin->install_path . "/version.php", "r");

    //Output lines until EOF is reached and look for 'copyright'
    while(! feof($file)) {
        $line = fgets($file);
        if (strstr($line, 'copyright')) {
            // Remove some text from $line where found.
            $line = str_replace('* @copyright', '',$line);
            $line = str_replace('&copy;', '',$line);
            $line = str_ireplace('Copyright;', '',$line);
            $line = str_ireplace('(C)', '',$line);

            $plugin->developer = trim($line);
        }
    }
    fclose($file);
}

function get_github_url(&$plugin) {
    $plugin->github_url = 'coming soon-ish...';

    $gitpath = '../../../../.git/';
    $result = new stdClass();
    exec("ls $gitpath", $result);
    return $result;
}

function get_plugins() {
    $o = ''; $orray = '';
    $relativeBase = "../../../../";
    $moodleversion = exec("cat $relativeBase/version.php | grep -m 1 '\$release'");
    return $moodleversion;

//    exec("ls -a $relativeBase.git", $orray);
    foreach ($orray as $line) {
        $o .= $line . "<br>";
    }

    return $o;

}

function update_plugin($plugin) {
    global $DB;
    $record = $DB->get_record('report_plugins', ['install_path' => $plugin->install_path]);
    // If there is no such record create it.
    if (!$record) {
        $record_exists = false;
        $record = new stdClass();
        $record->install_path = $plugin->install_path;
    } else {
        $record_exists = true;
    }
    $record->title = $plugin->title;
    $record->developer = $plugin->developer;
    $record->github_url = $plugin->github_url;

    // If it is a new record insert, otherwise update it.
//    (!$record ? $DB->insert_record('report_plugins', $record) : $DB->update_record('report_plugins', $record));
    if (!$record_exists) {
        $DB->insert_record('report_plugins', $record);
    } else {
        $DB->update_record('report_plugins', $record);
    }
    return "$record->install_path<br>";
}