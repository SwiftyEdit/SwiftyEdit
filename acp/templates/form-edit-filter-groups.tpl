<div id="form_response">

</div>

<div class="card">
    <div class="card-header d-flex">
        <div>Mode {mode} {id}</div>
        <div class="ms-auto"><a href="?tn=shop&sub=shop-filter" class="btn btn-default"><i class="bi bi-x-lg"></i> {btn_close}</a></div>
    </div>
    <div class="card-body">
    <form id="edit_cat">

        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label>{label_group_name}</label>
                    <input type="text" class="form-control" name="filter_group_name" value="{val_group_name}">
                </div>
            </div>
            <div class="col-2">
                <div class="mb-3">
                    <label>{label_priority}</label>
                    <input type="number" class="form-control" name="filter_group_priority" value="{val_group_priority}">
                </div>

            </div>
            <div class="col-2">
                <div class="mb-3">
                    <label>Type</label>
                    {select_input_type}
                </div>
            </div>
            <div class="col-2">
                <div class="mb-3">
                    <label>{label_language}</label>
                    {select_language}
                </div>
            </div>
        </div>

        <button class="btn btn-success" id="btnSubmitCategory" type="submit" name="submit_group" value="{mode}">{btn_submit_text}</button>

        <input type="hidden" name="csrf_token" value="{csrf_token}">
        <input type="hidden" name="group_id" value="{id}">
        <input type="hidden" name="mode" value="{mode}">

    </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#edit_cat').submit(function(e) {
            e.preventDefault();

            var form = $(this);
            var serializedData = form.serialize();

            $.ajax({
                url: 'core/ajax/write-filter.php',
                type: 'POST',
                data: serializedData,

                success: function(response){
                    $("#form_response").html(response);
                }
            });
        });
    });
</script>