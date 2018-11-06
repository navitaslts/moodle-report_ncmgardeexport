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
 * User Grade Report .
 *
 * @package     report_ncmgradeexport
 * @category    admin
 * @copyright   2018 Nicolas Jourdain <nicolas.jourdain@navitas.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/report/ncmgradeexport/lib.php');
// require_once($CFG->dirroot . '/report/ncmusergrades/locallib.php');

require_login();

global $DB;

$context = context_system::instance();

// Protect page based on capability.
require_capability('report/ncmgradeexport:use', $context);

$url = new moodle_url('/report/ncmgradeexport/index.php');

$title = get_string('titlereport', 'report_ncmgradeexport');

if ($context->contextlevel == CONTEXT_SYSTEM) {
    $heading = $SITE->fullname;
} else if ($context->contextlevel == CONTEXT_COURSECAT) {
    $heading = $context->get_context_name();
} else {
    throw new coding_exception('Unexpected context!');
}
// Creating the form.
$mform = new \report_ncmgradeexport\filter_form(null);

// Set css.
// $PAGE->requires->css('/report/ncmusergrades/style/gradetable.css');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($heading);
$PAGE->set_pagelayout('admin'); // OR report.

// $PAGE->navbar->add(get_string('pluginname', 'report_ncmgradeexport'));

$output = $PAGE->get_renderer('report_ncmgradeexport');

echo $output->header();
echo $output->heading($title);

if ($fromform = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.
    // Set default data (if any).
    $mform->set_data($fromform);
    // Display the form.
    $mform->display();


    $cat = coursecat::get($fromform->category);
    $childrencourses = $cat->get_courses();
    
    // Loop on each course of the selected category.
    foreach($childrencourses as $course) {
        echo "<pre>### Course: " . $course->id . " - ". $course->shortname ."</pre>";

        $formoptions = array(
            'includeseparator' => true,
            'publishing' => true,
            'simpleui' => true,
            'multipledisplaytypes' => true
        );

        $params = array(
            'includeseparator' => true,
            'publishing' => true,
            'simpleui' => true,
            'multipledisplaytypes' => true
        );
        $mform = new grade_export_form(null, $params);
        $data = $mform->get_data();
        // $export = new grade_export_pdf($course, $groupid, $data);
        $export = new ncmgradeexport_report($course, $groupid=null, $data);
    }

    // // Get user details.
    // $user = $DB->get_record('user', array('username' => strtolower($fromform->userid)), '*', MUST_EXIST);
    // $userdetails = user_get_user_details($user);
    // echo ncmusergrades_user_desc($userdetails);
    // // Get all the courses the user is enrolled into.
    // $courses = enrol_get_users_courses($user->id, false, 'id, shortname, showgrades');

    // // Group the course by category.
    // $mycategories = array();
    // foreach ($courses as $course) {
    //     // Get the category.
    //     if (!isset($mycategories[$course->category])) {
    //         $mycategory = coursecat::get($course->category);
    //         $mycategories[$mycategory->id]->category = $mycategory;
    //     }
    //     $mycategories[$mycategory->id]->courses[$course->id] = $course;
    // }

    // Sort the categories, Most recent at the top.
    // usort($mycategories, function ($a, $b) {
    //     return strcmp($a->category->name, $b->category->name);
    // });

    // foreach ($mycategories as $mycategory) {

    //     $mycourses = $mycategory->courses;

    //     foreach ($mycourses as $course) {

    //         echo "<h3>{$mycategory->category->name} / {$course->fullname} ({$course->shortname})</h3>";

    //         // Return tracking object.
    //         $gpr = new grade_plugin_return(array('type' => 'report', 'plugin' => 'overview', 'userid' => $user->id));
    //         $context = context_course::instance($course->id);
    //         $userreport = new ncm_grade_report_user($course->id, $gpr, $context, $user->id);

    //         if ($userreport->fill_table()) {
    //             echo $userreport->print_table(true) . '<br/>';
    //         }
    //     }
    // }

} else {
    // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed.
    // or on the first display of the form.

    // Set default data (if any).
    $mform->set_data($toform = array());
    // Display the form.
    $mform->display();
}

echo $output->footer();