<?php

namespace Report\AverageCheckTime;

class PageBuilder 
{
    const _FORM = '_form';

    const TEACHER_ID = 'teacher_id_';
    const COURSE_ID = 'course_id_';

    private $sortType;
    private $teachers;

    function __construct($sortType, $teachers)
    {
        $this->sortType = $sortType;
        $this->teachers = $teachers;
    }

    public function get_page() : string
    {
        $page = $this->get_page_header();
        $page.= $this->get_teachers_table();
        $page.= $this->get_sort_forms();

        return $page;
    }

    private function get_page_header() : string 
    {
        $attr = array('class' => 'reportHeader');
        $text = get_string('pluginname', 'report_averagechecktime');
        return \html_writer::tag('h2', $text, $attr);
    }

    private function get_teachers_table() : string 
    {
        $attr = array('class' => 'averageTable');
        $str = \html_writer::start_tag('table', $attr);
        $str.= $this->get_teacher_table_header();
        $str.= $this->get_teacher_table_body();
        $str.= \html_writer::end_tag('table');

        return $str;
    }

    private function get_teacher_table_header() : string 
    {
        $sortBy = Main::SORT_BY_NAME;
        $text = get_string('teacher', 'report_averagechecktime');
        $str = $this->get_column_header($sortBy, $text);

        $sortBy = Main::SORT_BY_GRADE;
        $text = get_string('average_grade', 'report_averagechecktime');
        $str.= $this->get_column_header($sortBy, $text);

        $sortBy = Main::SORT_BY_TIME;
        $text = get_string('average_check_time', 'report_averagechecktime');
        $str.= $this->get_column_header($sortBy, $text);

        $str = \html_writer::tag('tr', $str);
        $str = \html_writer::tag('thead', $str);

        return $str; 
    }

    private function get_column_header(string $sortBy, string $text) : string 
    {
        $attr = array(
            'onclick' => 'submit_form(`'.$sortBy.self::_FORM.'`)',
            'title' => get_string('sort_title', 'report_averagechecktime')
        );

        $str = $text;
        if($this->sortType == $sortBy)
        {
            $str.= '↓';
        }

        return \html_writer::tag('td', $str, $attr);
    }

    private function get_teacher_table_body() : string 
    {
        $str = \html_writer::start_tag('tbody');

        $teacherNumber = 1;
        foreach($this->teachers as $teacher)
        {
            $str.= $this->get_teacher_row($teacher, $teacherNumber);
            $teacherNumber++;
        }

        $str.= \html_writer::end_tag('tbody');

        return $str;
    }

    private function get_teacher_row(\stdClass $teacher, int $teacherNumber) : string 
    {
        $attr = array(
            'id' => self::TEACHER_ID.$teacherNumber,
            'class' => 'teacher-row',
            'onclick' => 'toggle_courses_rows_visibility(this)',
            'title' => get_string('more_info_title', 'report_averagechecktime')
        );
        $str = \html_writer::start_tag('tr', $attr);

        $text = $teacherNumber.'. '.$teacher->name;
        $str.= \html_writer::tag('td', $text);

        $attr = array('class' => 'tac');
        $text = round($teacher->averageGrade, 2);
        $str.= \html_writer::tag('td', $text, $attr);

        $text = $teacher->averageTimeString;
        $str.= \html_writer::tag('td', $text);

        $str.= \html_writer::end_tag('tr');

        $str.= $this->get_courses_rows($teacher, $teacherNumber);

        return $str;
    }

    private function get_courses_rows(\stdClass $teacher, int $teacherNumber) : string 
    {
        $str = '';

        $courseNumber = 1;
        foreach($teacher->courses as $course)
        {
            $str.= $this->get_course_row($course, $teacherNumber, $courseNumber);
            $courseNumber++;
        }

        return $str;
    }

    private function get_course_row(\stdClass $course, int $teacherNumber, int $courseNumber) : string 
    {
        $attr = array(
            'id' => self::TEACHER_ID.$teacherNumber.self::COURSE_ID.$courseNumber,
            'data-teacher-number' => self::TEACHER_ID.$teacherNumber,
            'class' => 'course-row hidden',
            'onclick' => 'toggle_items_rows_visibility(this)',
            'title' => get_string('more_info_title', 'report_averagechecktime')
        );
        $str = \html_writer::start_tag('tr', $attr);

        $attr = array('style' => 'padding-left:25px');
        $text = $courseNumber.'. '.$course->name;
        $str.= \html_writer::tag('td', $text, $attr);

        $attr = array('class' => 'tac');
        $text = round($course->averageGrade, 2);
        $str.= \html_writer::tag('td', $text, $attr);

        $text = $course->averageTimeString;
        $str.= \html_writer::tag('td', $text);

        $str.= \html_writer::end_tag('tr');

        $str.= $this->get_items_rows($course, $teacherNumber, $courseNumber);

        return $str;
    }

    private function get_items_rows(\stdClass $course, int $teacherNumber, int $courseNumber) : string 
    {
        $str = '';

        $itemNumber = 1;
        foreach($course->items as $item)
        {
            $str.= $this->get_item_row($item, $teacherNumber, $courseNumber, $itemNumber);
            $itemNumber++;
        }

        return $str;
    }

    private function get_item_row(\stdClass $item, int $teacherNumber, int $courseNumber, int $itemNumber) : string 
    {
        $attr = array(
            'data-course-number' => self::TEACHER_ID.$teacherNumber.self::COURSE_ID.$courseNumber,
            'data-teacher-number-of-item' => self::TEACHER_ID.$teacherNumber,
            'class' => 'item-row hidden'
        );
        $str = \html_writer::start_tag('tr', $attr);

        $attr = array('style' => 'padding-left:50px');
        $text = $itemNumber.'. '.$item->name;
        $str.= \html_writer::tag('td', $text, $attr);

        $attr = array('class' => 'tac');
        $text = round($item->averageGrade, 2);
        $str.= \html_writer::tag('td', $text, $attr);

        $text = $item->averageTimeString;
        $str.= \html_writer::tag('td', $text);

        $str.= \html_writer::end_tag('tr');

        return $str;
    }

    private function get_sort_forms() : string 
    {
        $forms = $this->get_sort_by_grade_form(Main::SORT_BY_NAME);
        $forms.= $this->get_sort_by_grade_form(Main::SORT_BY_GRADE);
        $forms.= $this->get_sort_by_grade_form(Main::SORT_BY_TIME);

        return $forms;
    }

    private function get_sort_by_grade_form(string $sortType) : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::SORT_TYPE,
            'value' => $sortType
        );
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'method' => 'post',
            'id' => $sortType.self::_FORM,
            'class' => 'hidden'
        );
        return \html_writer::tag('form', $params, $attr);
    }

}
