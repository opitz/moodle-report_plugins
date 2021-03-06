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
 * Plugins report
 *
 * @package    report_plugins
 * @copyright  2021 QMUL / Matthias Opitz (m.opitz@qmul.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/plugins/classes/renderer.php');
require_once($CFG->dirroot.'/report/plugins/lib.php');

require_login();

// Set up the page.
$pluginname = get_string('pluginname', 'report_plugins');
$PAGE->set_title($pluginname);
$PAGE->set_heading($pluginname);

// Get JavaScript.
$PAGE->requires->js_call_amd('report_plugins/navigation', 'init', array());
//$PAGE->requires->js_call_amd('report_plugins/ajax', 'init', array());

$output = $PAGE->get_renderer('report_plugins');

echo $output->header();
echo $output->render_grauschleier();
echo $output->render_waitingbox();
echo $output->render_plugins_navigation();
echo $output->render_detailsarea();
echo $output->render_coursesarea();
echo $output->render_importpage();

// Get all plugins with some information sorted by type.
$pluginsbytype = core_plugin_manager::instance()->get_plugins();
echo $output->render_pluginsbytype($pluginsbytype);

echo $output->footer();

