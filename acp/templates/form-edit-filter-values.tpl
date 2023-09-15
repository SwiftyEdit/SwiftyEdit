<div id="form_response">

</div>

<div class="card">
    <div class="card-header d-flex">
        <div>Mode {mode} {id}</div>
        <div class="ms-auto"><a href="?tn=shop&sub=shop-filter" class="btn btn-default"><i class="bi bi-x-lg"></i> {btn_close}</a></div>
    </div>
    <div class="card-body">
    <form id="edit_filter_value">

        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label>{label_title}</label>
                    <input type="text" class="form-control" name="filter_name" value="{value_name}">
                </div>
                <div class="mb-3">
                    <label>{label_description}</label>
                    <textarea class="form-control" name="filter_description">{value_description}</textarea>
                </div>
            </div>
            <div class="col-3">
                <div class="mb-3">
                    <label>{label_priority}</label>
                    <input type="number" class="form-control" name="filter_priority" value="{value_priority}">
                </div>
            </div>
            <div class="col-3">
                <div class="mb-3">
                    <label>{label_group_name}</label>
                    {select_parent_group}
                </div>
            </div>
        </div>

        <button class="btn btn-success" id="btnSubmitValue" type="submit" name="submit_value" value="{mode}">{btn_submit_text}</button>
        <button class="btn btn-danger {btn_delete_class}" id="btnDeleteValue" type="submit" name="delete_value" value="{mode}">{btn_delete_text}</button>

        <input type="hidden" name="csrf_token" value="{csrf_token}">
        <input type="hidden" name="value_id" value="{id}">
        <input type="hidden" name="mode" value="{mode}">

    </form>
    </div>
</div>

<script>
    $(document).ready(function() {


        $("#edit_filter_value button").click(function (e) {
            e.preventDefault();

            if ($(this).attr("name") == "submit_value") {
                let action = 'submit';
                form_submit(action);
            }
            if ($(this).attr("name") == "delete_value") {
                let action = 'delete';
                form_submit(action);
                $("#edit_filter_value").hide();
            }

            function form_submit(action) {

                var form = $('#edit_filter_value');
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