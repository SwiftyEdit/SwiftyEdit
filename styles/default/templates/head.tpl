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
<link rel="icon" href="{$page_favicon}">
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

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{$se_page_url}">
<meta property="twitter:title" content="{$page_title}">
<meta property="twitter:description" content="{$page_meta_description|htmlentities}">
<meta property="twitter:image" content="{$page_thumbnail}">

<!-- CSS -->
<link rel="stylesheet" media="screen" href="{$se_inc_dir}/styles/{$se_template}/css/default.css" />

<!-- JavaScript -->
<script type="text/javascript" src="{$se_inc_dir}/styles/{$se_template}/js/main.min.js"></script>


{$page_head_styles}	
{$page_head_enhanced}
{$modul_head_enhanced}
{$prefs_pagesglobalhead}

<meta name="generator" content="SwiftyEdit" />