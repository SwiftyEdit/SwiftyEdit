<!DOCTYPE html>
<html lang="{$languagePack}" id="swiftyedit" data-bs-theme="auto">
	<head>
		{$prepend_head_code}
		{include file='head.tpl'}

		{if $json_ld != ""}
		<script type="application/ld+json">{$json_ld}</script>
		{/if}

		{$append_head_code}
	</head>
	
	<body class="{$page_hash}">
		{$prepend_body_code}
		{include file="$body_template"}
		
		{if $se_snippet_privacy_policy != ''}
			<div class="privacy_policy">
				<div style="float:right;padding-left:10px;">
					<a href="#NULL" class="btn btn-success" id="permit_cookie">Okay</a>
				</div>
				<div class="privacy_policy_text"></div>
			</div>

		
		<script>
		var permit_cookies_str = '{$se_snippet_privacy_policy}';
		$( ".privacy_policy_text" ).html( permit_cookies_str );
			
		$( "#permit_cookie" ).click(function() {
	  	Cookies.set('permit_cookies', 'true', { expires: 7 });
	  	$( "div.privacy_policy" ).addClass( "d-none" );
		});
		
		if(Cookies.get('permit_cookies') == 'true') {
			$( "div.privacy_policy" ).addClass( "d-none" );
		}
		</script>

		{/if}

		{$append_body_code}
	</body>
</html>
