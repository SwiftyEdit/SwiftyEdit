<div id="form_response">

</div>

<div class="card">
    <div class="card-header d-flex">
        <div>Mode {mode} {id}</div>
        <div class="ms-auto"><a href="?tn=categories" class="btn btn-default"><i class="bi bi-x-lg"></i> {btn_close}</a></div>
    </div>
    <div class="card-body">
    <form id="edit_cat">

        <div class="row">
            <div class="col-9">
                <div class="mb-3">
                    <label>{label_title}</label>
                    <input type="text" class="form-control" name="cat_name" value="{val_cat_name}">
                </div>
                <div class="mb-3">
                    <label>{thumbnail}</label>
                    {select_thumbnail}
                </div>
            </div>
            <div class="col-3">
                <div class="mb-3">
                    <label>{label_priority}</label>
                    <input type="numer" class="form-control" name="cat_sort" value="{val_cat_priority}">
                </div>
                <div class="mb-3">
                    <label>{label_language}</label>
                    {select_language}
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label>{label_description}</label>
            <textarea class='form-control' rows='8' name='cat_description'>{val_cat_description}</textarea>
        </div>


        <button class="btn btn-success" id="btnSubmitCategory" type="submit" name="submit_cat" value="{mode}">{btn_submit_text}</button>

        <input type="hidden" name="csrf_token" value="{csrf_token}">
        <input type="hidden" name="cat_id" value="{id}">
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
                url: 'core/ajax/write-categories.php',
                type: 'POST',
                data: serializedData,

                success: function(response){
                    $("#form_response").html(response);
                }
            });
        });
    });
</script>