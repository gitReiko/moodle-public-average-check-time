<?php

namespace Report\AverageCheckTime;

class PageBuilder 
{
    private $teachers;

    function __construct($teachers)
    {
        $this->teachers = $teachers;
    }

    public function get_page() : string
    {
        $page = $this->get_page_header();

        $page.= \html_writer::start_tag('ol');

        foreach($this->teachers as $teacher)
        {
            $page.= $this->get_teacher($teacher);
        }

        $page.= \html_writer::end_tag('ol');

        return $page;
    }

    private function get_page_header() : string 
    {
        $text = get_string('pluginname', 'report_averagechecktime');
        return \html_writer::tag('h2', $text);
    }

    private function get_teacher(\stdClass $teacher) : string 
    {
        $page = \html_writer::tag('li', $teacher->name);

        $page.= \html_writer::start_tag('ol');

        foreach($teacher->courses as $course)
        {
            $page.= $this->get_course($course);
        }

        $page.= \html_writer::end_tag('ol');

        return $page;
    }

    private function get_course(\stdClass $course) : string 
    {
        $page = \html_writer::tag('li', $course->name);

        $page.= \html_writer::start_tag('ol');

        foreach($course->items as $item)
        {
            $page.= $this->get_item($item);
        }

        $page.= \html_writer::end_tag('ol');

        return $page;
    }

    private function get_item(\stdClass $item) : string 
    {
        $page = \html_writer::tag('li', $item->name);

        return $page;
    }

}
