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
    const FROM_DATE = 'from_date';
    const TO_DATE = 'to_date';

    private $sortType;
    private $fromDate;
    private $toDate;
    private $teachers;

    function __construct()
    {
        $getter = new DataGetter;

        $this->sortType = $getter->get_sort_type();
        $this->fromDate = $getter->get_from_date();
        $this->toDate = $getter->get_to_date();
        $this->teachers = $getter->get_teachers();
    }

    public function display_report_page() : void 
    {
        $pageBuilder = new PageBuilder(
            $this->sortType, 
            $this->fromDate,
            $this->toDate,
            $this->teachers
        );
        
        echo $pageBuilder->get_page();
    }

}

