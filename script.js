
function toggle_courses_rows_visibility(row)
{
    require(['jquery'], function($)
    {
        $("[data-teacher-number="+row.id+"]").toggleClass('hidden');
        $("[data-teacher-number-of-item="+row.id+"]").toggleClass('hidden', true);
    });
}

function toggle_items_rows_visibility(row)
{
    require(['jquery'], function($)
    {
        $("[data-course-number="+row.id+"]").toggleClass('hidden');
    });
}

function submit_form(id)
{
    document.getElementById(id).submit();
}

