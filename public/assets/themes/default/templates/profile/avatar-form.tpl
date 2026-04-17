<form>
    <div class="card mb-3">
        <div class="card-header">
            {$lang_legend_avatar}
        </div>

    <div class="card-body">

        <div class="mb-3">
            <label for="formFile" class="form-label">{$lang_msg_avatar}</label>
            <input class="form-control" name="avatar" type="file" size="50">
        </div>

        <button class="btn btn-primary" type="button" name="upload_avatar"
                hx-post="/xhr/se/profile/"
                hx-encoding="multipart/form-data"
                hx-trigger="click"
                hx-target="#avatar-response"
                hx-swap="innerHTML">
            Upload
        </button>

    </div>
    </div>
    {$hidden_csrf_token}
</form>