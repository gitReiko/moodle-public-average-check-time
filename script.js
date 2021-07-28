
function toggle_courses_rows_visibility(row)
{
    require(['jquery'], function($)
    {
        $("[data-teacher-number="+row.id+"]").toggleClass('hidden');
    });
}

function toggle_items_rows_visibility(row)
{
    require(['jquery'], function($)
    {
        $("[data-course-number="+row.id+"]").toggleClass('hidden');
    });
}

