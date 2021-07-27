<?php

namespace Report\AverageCheckTime;

ini_set('memory_limit', '1024M');

class DataGetter 
{

    private $rawGrades;
    private $teachers;

    function __construct()
    {
        $this->rawGrades = $this->get_raw_grades();
        $this->teachers = $this->parse_raw_grades();

        print_r($this->teachers);
    }

    public function display_report_page() : void 
    {
        echo 'in class';
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
        ';
        $params = array();

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

        usort($teachers, function($a,$b) { return strcmp($a->name, $b->name); });

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


}

