
function add_periods_and_submit_form(form_id)
{
    let form = document.getElementById(form_id);

    let fromDate = document.getElementById('from_date');
    fromDate.setAttribute("form", form_id);

    let toDate = document.getElementById('to_date');
    toDate.setAttribute("form", form_id);

    submit_form(form_id);
}

function submit_form(id)
{
    document.getElementById(id).submit();
}

function toggle_warning()
{
    require(['jquery'], function($)
    {
        $("#warningBox").toggleClass('hidden');
    });
}

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


