<?php

namespace Report\AverageCheckTime;

require_once 'data_getter.php';
require_once 'page_builder.php';

class Main 
{
    private $teachers;

    function __construct()
    {
        $getter = new DataGetter;

        $this->teachers = $getter->get_teachers();
    }

    public function display_report_page() : void 
    {
        $pageBuilder = new PageBuilder($this->teachers);
        
        echo $pageBuilder->get_page();
    }

}

