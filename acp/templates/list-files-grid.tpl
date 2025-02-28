
    <div class="col-md-6">
        <i class="bi bi-file-earmark"></i>
        <a href="{preview_link}">{short_filename}</a> {labels}
    </div>
    <div class="col-md-1">
        {filesize}
    </div>
    <div class="col-md-1">
        <i class="bi bi-download"></i> {media_file_hits}
    </div>
    <div class="col-md-2">
        {show_filetime}
    </div>
    <div class="col-md-2">
        <div class="d-flex justify-content">
            <form action="/admin/uploads/edit/" method="POST" class="d-inline">
                {edit_button}
                <input type="hidden" name="csrf_token" value="{csrf_token}">
            </form>
        {delete_button}
        </div>
    </div>

