<div id="form_response">

</div>

<div class="card">
    <div class="card-header d-flex">
        <div>Mode {mode} {id}</div>
        <div class="ms-auto"><a href="?tn=shop&sub=shop-filter" class="btn btn-default"><i class="bi bi-x-lg"></i> {btn_close}</a></div>
    </div>
    <div class="card-body">
    <form id="edit_group" class="dirtyignore">

        <div class="row">
            <div class="col-5">
                <div class="mb-3">
                    <label>{label_group}</label>
                    <input type="text" class="form-control" name="filter_group_name" value="{val_group_name}">
                </div>
                <div class="mb-3">
                    <label>{label_description}</label>
                    <textarea class="form-control" name="filter_group_description">{val_group_description}</textarea>
                </div>
            </div>
            <div class="col-3">
                <div class="mb-3">
                    <label>{label_priority}</label>
                    <input type="number" class="form-control" name="filter_group_priority" value="{val_group_priority}">
                </div>
                <div class="mb-3">
                    <label>Type</label>
                    {select_input_type}
                </div>
                <div class="mb-3">
                    <label>{label_language}</label>
                    {select_language}
                </div>
            </div>
            <div class="col-4">
                <label>{label_categories}</label>
                <div class="scroll-container">
                    {select_categories}
                </div>
            </div>
        </div>

        <button class="btn btn-success" id="btnSubmitCategory" type="submit" name="submit_group" value="{mode}">{btn_submit_text}</button>
        <button class="btn btn-danger {btn_delete_class}" id="btnDeleteCategory" type="submit" name="delete_group" value="{mode}">{btn_delete_text}</button>

        <input type="hidden" name="csrf_token" value="{csrf_token}">
        <input type="hidden" name="group_id" value="{id}">
        <input type="hidden" name="mode" value="{mode}">

    </form>
    </div>
</div>

<script>


    $(document).ready(function(){


        $("#edit_group button").click(function (e) {
            e.preventDefault();

            if ($(this).attr("name") == "submit_group") {
                let action = 'submit';
                form_submit(action);
            }
            if ($(this).attr("name") == "delete_group") {
                let action = 'delete';
                form_submit(action);
                $("#edit_group").hide();
            }

            function form_submit(action) {

                var form = $('#edit_group');
                var serializedData = form.serialize() + '&action=' + action;

                $.ajax({
                    url: 'core/ajax/write-filter.php',
                    type: 'POST',
                    data: serializedData,

                    success: function(response){
                        $("#form_response").html(response);
                        $('#form_response > div').delay(3000).fadeOut('slow');
                    }
                });
            }
        });
    });
</script>