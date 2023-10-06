
<div class="card p-2 mb-3">

<form action="" method="POST">	
	<div class="row">
		<div class="col-md-9">
			<div class="form-group">
				<label>{label_title}</label>
				<input type="text" name="option_title" value="{feature_title}" class="form-control">
			</div>
			<div class="form-group">
		    <label>{label_value}</label>
				<input type="text" name="option_text[]" value="" class="form-control">
				{option_text_inputs}
		  </div>
  
		</div>
		<div class="col-md-3">


			<div class="mb-3 pb-3">
				<label>{label_language}</label>
				{select_lang}
			</div>

			<div class="mb-3 pb-3">
				<label>{label_priority}</label>
				<input type="number" name="option_priority" value="{feature_priority}" class="form-control">
			</div>			
			<hr>
			{btn_send_form}
		</div>
	</div>
	
	
	{hidden_csrf}
</form>

</div>