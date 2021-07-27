<?php

    
$ADMIN->add
(
    'reports', 
    new admin_externalpage('reportaveragechecktime', get_string('pluginname', 'report_averagechecktime'), 
    "$CFG->wwwroot/report/averagechecktime/index.php", 
    'report/averagechecktime:view')
);

$settings = null;


