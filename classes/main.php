<?php

namespace Report\AverageCheckTime;

require_once 'data_getter.php';

class Main 
{

    function __construct()
    {
        $getter = new DataGetter;
    }

    public function display_report_page() : void 
    {
        echo 'in class';
    }

}

