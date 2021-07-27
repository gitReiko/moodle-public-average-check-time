<?php

require_once '../../config.php';
require_once 'classes/main.php';
//require_once 'enums.php';

use Report\AverageCheckTime\Main as main;

$url = new moodle_url("/report/averagechecktime/index.php");
$PAGE->set_url($url);

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'report_averagechecktime'));
$PAGE->set_heading(get_string('pluginname', 'report_averagechecktime'));
//$PAGE->requires->css('/report/averagechecktime/styles.css');
//$PAGE->requires->js('/report/averagechecktime/script.js');

require_login();
  
echo $OUTPUT->header();

$main = new main;
$main->display_report_page();

echo $OUTPUT->footer();





/*

Данные собираются из промежуточной таблицы журнала оценок (grade_grades).
Они могут быть неточны.

1. непроверенные работы
2. работы, которые проверил другой преподаватель
3. небольшое количество работ проверенных очень поздно могут вносить значительные искажения
4. перепроверенные позже работы будут вносить искажения

*/
