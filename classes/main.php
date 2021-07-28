<?php

namespace Report\AverageCheckTime;

require_once 'data_getter.php';
require_once 'page_builder.php';

class Main 
{
    const SORT_TYPE = 'sort_type';
    const SORT_BY_NAME = 'sort_by_name';
    const SORT_BY_GRADE = 'sort_by_grade';
    const SORT_BY_TIME = 'sort_by_time';

    private $sortType;
    private $teachers;

    function __construct()
    {
        $getter = new DataGetter;

        $this->teachers = $getter->get_teachers();
        $this->sortType = $getter->get_sort_type();
    }

    public function display_report_page() : void 
    {
        $pageBuilder = new PageBuilder($this->sortType, $this->teachers);
        
        echo $pageBuilder->get_page();
    }

}

