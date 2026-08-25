<title>{$page_title|htmlentities}</title>
<meta charset="utf-8">

<base href="{$se_base_href}">

<meta name="robots" content="{$page_meta_robots}" />
<meta name="author" content="{$page_meta_author}" />
{if $page_meta_description != ''}
	<meta name="description" content="{$page_meta_description|htmlentities}" />
{else}
	<meta name="description" content="{$prefs_pagedescription|htmlentities}" />
{/if}
<meta name="keywords" content="{$page_meta_keywords}" />
<meta name="date" content="{$page_meta_date}" />

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

{if $page_canonical_url != ''}
	<link rel=“canonical“ href="{$page_canonical_url}" />
{/if}
{if $favicon_base != ''}
	<link rel="icon" type="image/png" sizes="32x32" href="{$favicon_base}/favicon-32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="{$favicon_base}/favicon-16.png">
	<link rel="icon" href="{$favicon_base}/favicon.ico">
	<link rel="apple-touch-icon" sizes="180x180" href="{$favicon_base}/favicon-180.png">
	<link rel="manifest" href="{$favicon_base}/site.webmanifest">
{/if}
<link rel="alternate" type="application/rss+xml" title="{$prefs_pagetitle} | RSS" href="/rss.php" />


<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:url" content="{$se_page_url}">
<meta property="og:title" content="{$page_title|htmlentities}">
<meta property="og:site_name" content="{$prefs_pagetitle}">

<meta property="og:image" content="{$page_thumbnail}">
{foreach $page_thumbnails as $thumbs}
<meta property="og:image" content="{$thumbs}">
{/foreach}

<!-- CSS -->
<link rel="stylesheet" media="screen" href="{$se_inc_dir}/themes/{$se_template}/dist/core.css" />
{if $se_template_stylesheet != ''}
	<link rel="stylesheet" media="screen" href="{$se_inc_dir}/themes/{$se_template}/dist/skins/{$se_template_stylesheet}" />
{/if}
{foreach $se_theme_components as $component_id => $component}
	{if $component.enabled && $component.has_css}
		<link rel="stylesheet" media="screen" href="{$se_inc_dir}/themes/{$se_template}/dist/{$component_id}.css" />
	{/if}
{/foreach}

<!-- JavaScript -->
<script type="text/javascript" src="{$se_inc_dir}/themes/{$se_template}/dist/core.js"></script>
{foreach $se_theme_components as $component_id => $component}
	{if $component.enabled && $component.has_js}
		<script type="text/javascript" src="{$se_inc_dir}/themes/{$se_template}/dist/{$component_id}.js"></script>
	{/if}
{/foreach}


{$page_head_styles}	
{$page_head_enhanced}
{$modul_head_enhanced}
{$prefs_pagesglobalhead}

<meta name="generator" content="SwiftyEdit" />