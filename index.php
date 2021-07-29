<?php

require_once '../../config.php';
require_once 'classes/main.php';
require_once($CFG->libdir.'/adminlib.php');

use Report\AverageCheckTime\Main as main;

$url = new moodle_url("/report/averagechecktime/index.php");
$PAGE->set_url($url);

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'report_averagechecktime'));
$PAGE->set_heading(get_string('pluginname', 'report_averagechecktime'));
$PAGE->requires->css('/report/averagechecktime/styles.css');
$PAGE->requires->js('/report/averagechecktime/script.js');

require_login();
admin_externalpage_setup('reportaveragechecktime', '', null, '', array('pagelayout'=>'report'));
  
echo $OUTPUT->header();

$main = new main;
$main->display_report_page();

echo $OUTPUT->footer();



