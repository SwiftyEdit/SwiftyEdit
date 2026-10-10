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

		{$append_body_code}
	</body>
</html>
