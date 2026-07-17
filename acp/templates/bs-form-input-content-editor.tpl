<div class="{container_classes}">
    <div class="d-flex justify-content-between align-items-center">
        {format_switch}
        <button type="button" class="btn btn-sm btn-outline-secondary content-editor-fullscreen-btn" data-bs-toggle="modal" data-bs-target="#contentEditorFullscreenModal" data-editor-target="{inputid}" title="Vollbild">
            <i class="bi bi-arrows-fullscreen"></i>
        </button>
    </div>
    <label for="{inputid}" class="form-label">{label}</label>
    <div id="{inputid}" class="content-editor-mount" data-editor="{editor}" data-value-field="{inputid}_value" data-content="{content_json}"></div>
    <textarea id="{inputid}_value" name="{input_name}" class="content-editor-value" style="display:none"></textarea>
</div>
