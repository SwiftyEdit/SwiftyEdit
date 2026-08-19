<!-- sidebar -->
<div class="card p-3">
    <div class="sidebar-actions sticky-lg-top">
        {submit_button}
    </div>
    <div class="mb-1 pb-1 border-bottom">
        {select_language}
    </div>
    <div class="mb-2 pb-1 border-bottom">
        <label>{label_categories}</label>
        <div class="scroll-container" style="max-height: 150px;">
            {checkbox_categories}
        </div>
    </div>
    <div class="mb-2 pb-1 border-bottom">
        <label>{label_tags}</label>
        <input type="text" class="form-control tags" name="content_tags" value="{content_tags}">
    </div>
    <div class="mb-2 pb-1 border-bottom">
        <label>{label_releasedate}</label>
        <input class='dp form-control' name="post_releasedate" type="datetime-local" value="{post_releasedate}">
    </div>
    <div class="mb-2 pb-1 border-bottom">
        {checkbox_hide_detail_page}
    </div>
    <div class="mb-2 pb-1 border-bottom">
        <label>{label_priority}</label>
        <input type="number" name="post_priority" value="{post_priority}" class="form-control">
        {checkbox_fixed}
    </div>
    <div class="mb-2 pb-1 border-bottom">
        {select_status}
    </div>
    <div class="mb-2 pb-1 border-bottom">
        {select_comments}
    </div>
    <div class="mb-2 pb-1 border-bottom">
        {select_votings}
    </div>
    <div class="mb-2 pb-1 border-bottom">
        <label>{labels}</label>
        <div>{post_labels}</div>
    </div>

    <input name="post_type" type="hidden" value="{post_type}">
    <input name="modus" type="hidden" value="{modus}">
    <input name="post_id" type="hidden" value="{post_id}">
    <input type="hidden" name="csrf_token" value="{token}">
    <input type="hidden" name="post_date" value="{post_date}">

</div>