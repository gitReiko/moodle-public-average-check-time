<?php

namespace Report\AverageCheckTime;

ini_set('memory_limit', '1024M');

class DataGetter 
{
    const DAY = 86400;
    const HOUR = 3600;
    const MINUTE = 60;

    private $sortType;
    private $fromDate;
    private $toDate;
    private $rawGrades;
    private $teachers;

    function __construct()
    {
        $this->sortType = $this->get_sort_type_from_request();
        $this->fromDate = $this->get_from_date_from_request();
        $this->toDate = $this->get_to_date_from_request();
        $this->rawGrades = $this->get_raw_grades();
        $this->teachers = $this->parse_raw_grades();
        $this->teachers = $this->calculate_averages($this->teachers);
        $this->teachers = $this->convert_time_into_convenient_format($this->teachers);
        $this->teachers = $this->sort_teachers_by($this->teachers);
    }

    public function get_sort_type()
    {
        return $this->sortType;
    }

    public function get_from_date() 
    {
        return $this->fromDate;
    }

    public function get_to_date() 
    {
        return $this->toDate;
    }

    public function get_teachers()
    {
        return $this->teachers;
    }

    private function get_sort_type_from_request() : string 
    {
        return optional_param(Main::SORT_TYPE, Main::SORT_BY_NAME, PARAM_TEXT);
    }

    private function get_from_date_from_request() : string 
    {
        $date = optional_param(Main::FROM_DATE, null, PARAM_TEXT);

        if($date)
        {
            return $date;
        }
        else 
        {
            if($this->get_current_month_number() >= 9) $month = '09';
            else $month = '02'; 
    
            $year = date('Y', time());
    
            return $year.'-'.$month.'-01';
        }
    }

    private function get_to_date_from_request() : string 
    {
        $date = optional_param(Main::TO_DATE, null, PARAM_TEXT);

        if($date)
        {
            return $date;
        }
        else 
        {
            if($this->get_current_month_number() >= 9) 
            {
                $month = '02';
                $year = date('Y', time()) + 1;
            }
            else 
            {
                $month = '09';
                $year = date('Y', time());
            }
    
            return $year.'-'.$month.'-01';
        }
    }

    private function get_current_month_number() : int 
    {
        return date('n', time());
    }

    private function get_raw_grades() 
    {
        global $DB;

        $sql = 'SELECT gg.id AS gradegradesid, gg.itemid, gg.usermodified AS teacherid, 
                        gg.finalgrade AS studentgrade, gg.timecreated, gg.timemodified, 
                        gi.courseid, gi.itemname, gi.itemmodule, c.fullname AS coursename, 
                        u.firstname AS teacherfirstname, u.lastname AS teacherlastname
                FROM {grade_grades} AS gg
                INNER JOIN {grade_items} AS gi 
                ON gg.itemid = gi.id
                INNER JOIN {course} AS c
                ON gi.courseid = c.id
                INNER JOIN {user} AS u 
                ON gg.usermodified = u.id
                WHERE gg.userid <> gg.usermodified
                AND gg.timecreated <> gg.timemodified
                AND gg.usermodified IS NOT NULL
                AND gg.finalgrade IS NOT NULL
                AND gg.timecreated IS NOT NULL 
                AND gg.timecreated >= ?
                AND gg.timemodified <= ?
        ';

        $params = array(strtotime($this->fromDate), strtotime($this->toDate));

        return $DB->get_records_sql($sql, $params);
    }

    private function parse_raw_grades()
    {
        $teachers = array();

        foreach($this->rawGrades as $rawGrade)
        {
            if($this->is_teacher_exist($teachers, $rawGrade))
            {
                $teachers = $this->modify_teacher($teachers, $rawGrade);
            }
            else 
            {
                $teachers = $this->add_teacher($teachers, $rawGrade);
            }
        }

        return $teachers;
    }

    private function is_teacher_exist(array $teachers, \stdClass $rawGrade) : bool 
    {
        foreach($teachers as $teacher)
        {
            if($teacher->id == $rawGrade->teacherid)
            {
                return true;
            }
        }

        return false;
    }

    private function add_teacher(array $teachers, \stdClass $rawGrade)
    {
        $newTeacher = new \stdClass;
        $newTeacher->id = $rawGrade->teacherid;
        $newTeacher->name = $rawGrade->teacherlastname.' '.$rawGrade->teacherfirstname;
        $newTeacher->averageTime = 0;
        $newTeacher->averageGrade = 0;
        $newTeacher->gradesCount = 0;

        $newTeacher->courses = array();
        $newTeacher->courses = $this->add_course($newTeacher->courses, $rawGrade);

        $teachers[] = $newTeacher;

        return $teachers;
    }

    private function add_course(array $courses, \stdClass $rawGrade)
    {
        $newCourse = new \stdClass;
        $newCourse->id = $rawGrade->courseid;
        $newCourse->name = $rawGrade->coursename;
        $newCourse->averageTime = 0;
        $newCourse->averageGrade = 0;
        $newCourse->gradesCount = 0;

        $newCourse->items = array();
        $newCourse->items = $this->add_item($newCourse->items, $rawGrade);

        $courses[] = $newCourse;

        return $courses;
    }

    private function add_item(array $items, \stdClass $rawGrade)
    {
        $newItem = new \stdClass;
        $newItem->id = $rawGrade->itemid;
        $newItem->name = $rawGrade->itemname;
        $newItem->module = $rawGrade->itemmodule;
        $newItem->averageTime = 0;
        $newItem->averageGrade = 0;
        $newItem->gradesCount = 0;

        $newItem->students = array();
        $newItem->students = $this->add_student($newItem->students, $rawGrade);

        $items[] = $newItem;

        return $items;
    }

    private function add_student(array $students, \stdClass $rawGrade) 
    {
        $newStudent = new \stdClass;
        $newStudent->grade = $rawGrade->studentgrade;
        $newStudent->checktime = $rawGrade->timemodified - $rawGrade->timecreated;

        $students[] = $newStudent;

        return $students;
    }

    private function modify_teacher(array $teachers, \stdClass $rawGrade)
    {
        foreach($teachers as $teacher)
        {
            if($teacher->id == $rawGrade->teacherid)
            {
                if($this->is_course_exist($teacher->courses, $rawGrade))
                {
                    $teacher->courses = $this->modify_course($teacher->courses, $rawGrade);
                }
                else 
                {
                    $teacher->courses = $this->add_course($teacher->courses, $rawGrade);
                }

                usort($teacher->courses, function($a,$b) { return strcmp($a->name, $b->name); });
            }
        }

        return $teachers;
    }

    private function is_course_exist(array $courses, \stdClass $rawGrade) : bool 
    {
        foreach($courses as $course)
        {
            if($course->id == $rawGrade->courseid)
            {
                return true;
            }
        }

        return false;
    }

    private function modify_course(array $courses, \stdClass $rawGrade)
    {
        foreach($courses as $course)
        {
            if($course->id == $rawGrade->courseid)
            {
                if($this->is_item_exist($course->items, $rawGrade))
                {
                    $course->item = $this->add_student_to_item($course->items, $rawGrade);
                }
                else 
                {
                    $course->items = $this->add_item($course->items, $rawGrade);
                }

                usort($course->items, function($a,$b) { return strcmp($a->name, $b->name); });
            }
        }

        return $courses;
    }

    private function is_item_exist(array $items, \stdClass $rawGrade) : bool 
    {
        foreach($items as $item)
        {
            if($item->id == $rawGrade->itemid)
            {
                return true;
            }
        }

        return false;
    }

    private function add_student_to_item(array $items, \stdClass $rawGrade)
    {
        foreach($items as $item)
        {
            if($item->id == $rawGrade->itemid)
            {
                $item->students = $this->add_student($item->students, $rawGrade);
            }
        }

        return $items;
    }

    private function calculate_averages(array $teachers)
    {
        $teacher = $this->sum_all_values($teachers);
        $teacher = $this->find_average_values($teachers);

        return $teachers;
    }

    private function sum_all_values(array $teachers)
    {
        foreach($teachers as $teacher)
        {
            foreach($teacher->courses as $course)
            {
                foreach($course->items as $item)
                {
                    foreach($item->students as $student)
                    {
                        $item->averageTime += $student->checktime;
                        $item->averageGrade += $student->grade;
                        $item->gradesCount++;
                    }

                    $course->averageTime += $item->averageTime;
                    $course->averageGrade += $item->averageGrade;
                    $course->gradesCount += $item->gradesCount;
                }

                $teacher->averageTime += $course->averageTime;
                $teacher->averageGrade += $course->averageGrade;
                $teacher->gradesCount += $course->gradesCount;
            }
        }

        return $teachers;
    }

    private function find_average_values(array $teachers)
    {
        foreach($teachers as $teacher)
        {
            foreach($teacher->courses as $course)
            {
                foreach($course->items as $item)
                {
                    $item->averageTime /= $item->gradesCount;
                    $item->averageGrade /= $item->gradesCount;
                }

                $course->averageTime /= $course->gradesCount;
                $course->averageGrade /= $course->gradesCount;
            }

            $teacher->averageTime /= $teacher->gradesCount;
            $teacher->averageGrade /= $teacher->gradesCount;
        }

        return $teachers;
    }

    private function convert_time_into_convenient_format(array $teachers)
    {
        foreach($teachers as $teacher)
        {
            foreach($teacher->courses as $course)
            {
                foreach($course->items as $item)
                {
                    $item->averageTimeString = $this->get_time_string($item->averageTime);
                }

                $course->averageTimeString = $this->get_time_string($course->averageTime);
            }

            $teacher->averageTimeString = $this->get_time_string($teacher->averageTime);
        }

        return $teachers;
    }

    private function get_time_string(int $timestamp) : string
    {
        $daysCount = intval($timestamp / self::DAY);
        $timeLeft = $timestamp % self::DAY;

        $hoursCount = intval($timeLeft / self::HOUR);
        $timeLeft = $timeLeft % self::HOUR;

        $minutesCount = intval($timeLeft / self::MINUTE);
        $timeLeft = $timeLeft % self::MINUTE;

        $secondsCount = $timeLeft;

        $str = '';

        if(!empty($daysCount))
        {
            $str.= $daysCount.' '.get_string('days', 'report_averagechecktime').' ';
        }
        
        if(!empty($hoursCount))
        {
            $str.= $hoursCount.' '.get_string('hours', 'report_averagechecktime').' ';
        }

        if(!empty($minutesCount))
        {
            $str.= $minutesCount.' '.get_string('minutes', 'report_averagechecktime').' ';
        }

        if(!empty($secondsCount))
        {
            $str.= $secondsCount.' '.get_string('seconds', 'report_averagechecktime').' ';
        }

        return $str;
    }

    private function sort_teachers_by(array $teachers)
    {
        if($this->sortType === Main::SORT_BY_NAME)
        {
            usort($teachers, function($a,$b) 
            { 
                return strcmp($a->name, $b->name); 
            });
        }
        else if($this->sortType === Main::SORT_BY_GRADE)
        {
            usort($teachers, function($a,$b) 
            {
                if ($a->averageGrade == $b->averageGrade) return 0;
                return ($a->averageGrade < $b->averageGrade) ? 1 : -1;
            });  
        }
        else if($this->sortType === Main::SORT_BY_TIME)
        {
            usort($teachers, function($a,$b) 
            {
                if ($a->averageTime == $b->averageTime) return 0;
                return ($a->averageTime < $b->averageTime) ? 1 : -1;
            });  
        }
        
        return $teachers;
    }


}

