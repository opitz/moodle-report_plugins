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
 * Get the details of a plugin
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

$frankenstyle = required_param('frankenstyle', PARAM_RAW);
$plugin = core_plugin_manager::instance()->get_plugin_info($frankenstyle);
$plugin->installpath = str_replace('/var/www/html/', '', $plugin->rootdir);
//$plugin = new stdClass();

//$plugin->displayname = required_param('displayname', PARAM_RAW);
//$plugin->installpath = required_param('installpath', PARAM_RAW);
//$plugin->version = required_param('version', PARAM_RAW);
//$plugin->release = required_param('release', PARAM_RAW);
$plugin->uses = required_param('uses', PARAM_RAW);




echo plugin_details($plugin);

function plugin_details($plugin) {
    add_data($plugin);
    $o = '';
    $details = [
        'Name' => 'displayname',
        'Path' => 'installpath',
        'Version' => 'versiondb',
        'Release' => 'release',
        'Developer' => 'developer',
        'Dependencies' => 'dependencies',
        'Uses' => 'uses',
        'Description' => 'description',
        'More Information' => 'info_url',
        'Github' => 'github_url',
        'Plugin Catalog' => 'plugin_url',
        'DevOps Wiki' => 'wiki_url',
        'Requested by' => 'requested_by',
        'Year added' => 'year_added',
    ];
    $o .= html_writer::start_tag('table', ['id' => 'details-table', 'class' => 'lined']);
    foreach ($details as $title => $source) {
        $o .= html_writer::start_tag('tr');
        $o .= html_writer::tag('th', $title, ['class' => 'title-col']);
        if ($source == 'dependencies') {
            $text = '';
            // If there are dependencies show them and the required version.
            if (count($plugin->$source) > 0) {
                foreach ($plugin->$source as $dependency => $version) {
                    $text .= "$dependency ($version)<br>";
                }
            }
            $o .= html_writer::tag('td', $text);
        } else if ($source == 'github_url') {
            $text = '';
            $github_urls = explode(',', $plugin->$source);
            if (count($github_urls) > 0) {
                foreach ($github_urls as $github_url) {
                    $text .= html_writer::tag('a', $github_url, ['href' => $github_url, 'target' => '_blank']);
                    $text .= html_writer::empty_tag('br');
                }
            }
            $o .= html_writer::tag('td', $text);
        } else if ($source == 'info_url' || $source == 'plugin_url' || $source == 'wiki_url') {
            $text = html_writer::tag('a', $plugin->$source, ['href' => $plugin->$source, 'target' => '_blank']);
            $text .= html_writer::empty_tag('br');
            $o .= html_writer::tag('td', $text);
        } else {
            $o .= html_writer::tag('td', $plugin->$source);
        }
        $o .= html_writer::end_tag('tr');
    }
    $o .= html_writer::end_tag('table');

    return $o;
}

function add_data(&$plugin) {
    global $DB;

    $record = $DB->get_record('report_plugins', ['install_path' => $plugin->installpath]);
    if ($record) foreach ($record as $key => $value) {
        $plugin->$key = $value;
    }
}