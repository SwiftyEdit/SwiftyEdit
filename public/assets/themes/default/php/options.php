<?php

/* defaultTheme options */

if(defined('SE_SECTION') && SE_SECTION === 'frontend') {

 /**
  * for example manipulate page values
  * $page_title = 'Extended title by defaultTheme options file: ' . $page_title;
  * $smarty->assign('page_title', "$page_title");
  */

    $discord_page = '';
    $facebook_page = '';
    $github_page = '';
    $insta_page = '';
    $linkedin_page = '';
    $slack_page = '';
    $twitch_page = '';
    $twitter_page = '';
    $youtube_page = '';

  $theme_options = se_get_theme_options("default");

  $get_discord_key = array_search("theme_discord", array_column($theme_options, 'theme_label'));
  if(isset($theme_options[$get_discord_key]['theme_value']) && ($theme_options[$get_discord_key]['theme_value'] != '')) {
      $discord_page = $theme_options[$get_discord_key]['theme_value'];
      $discord_link = '<a class="p-1" href="'.$discord_page.'" title="'.$discord_page.'" ><i class="bi bi-discord"></i></a> ';
      $smarty->assign('discord_link', "$discord_link");
  }

   
  $get_fb_key = array_search("theme_facebook", array_column($theme_options, 'theme_label'));
  if(isset($theme_options[$get_fb_key]['theme_value']) && ($theme_options[$get_fb_key]['theme_value'] != '')) {
      $facebook_page = $theme_options[$get_fb_key]['theme_value'];
      $fb_link = '<a class="p-1" href="'.$facebook_page.'" title="'.$facebook_page.'"><i class="bi bi-facebook"></i></a> ';
      $smarty->assign('fb_link', "$fb_link");
  }

  
  $get_github_key = array_search("theme_github", array_column($theme_options, 'theme_label'));
    if(isset($theme_options[$get_github_key]['theme_value']) && ($theme_options[$get_github_key]['theme_value'] != '')) {
        $github_page = $theme_options[$get_github_key]['theme_value'];
        $github_link = '<a class="p-1" href="'.$github_page.'" title="'.$github_page.'"><i class="bi bi-github"></i></a> ';
        $smarty->assign('github_link', "$github_link");
    }

  
  $get_insta_key = array_search("theme_instagram", array_column($theme_options, 'theme_label'));
    if(isset($theme_options[$get_insta_key]['theme_value']) && ($theme_options[$get_insta_key]['theme_value'] != '')) {
        $insta_page = $theme_options[$get_insta_key]['theme_value'];
        $insta_link = '<a class="p-1" href="'.$insta_page.'" title="'.$insta_page.'"><i class="bi bi-instagram"></i></a> ';
        $smarty->assign('insta_link', "$insta_link");
    }

  
  $get_linkedin_key = array_search("theme_linkedin", array_column($theme_options, 'theme_label'));
    if(isset($theme_options[$get_linkedin_key]['theme_value']) && ($theme_options[$get_linkedin_key]['theme_value'] != '')) {
        $linkedin_page = $theme_options[$get_linkedin_key]['theme_value'];
        $linkedin_link = '<a class="p-1" href="'.$linkedin_page.'" title="'.$linkedin_page.'"><i class="bi bi-linkedin"></i></a> ';
        $smarty->assign('linkedin_link', "$linkedin_link");
    }

  
  $get_slack_key = array_search("theme_slack", array_column($theme_options, 'theme_label'));
    if(isset($theme_options[$get_slack_key]['theme_value']) && ($theme_options[$get_slack_key]['theme_value'] != '')) {
        $slack_page = $theme_options[$get_slack_key]['theme_value'];
        $slack_link = '<a class="p-1" href="'.$slack_page.'" title="'.$slack_page.'"><i class="bi bi-slack"></i></a> ';
        $smarty->assign('slack_link', "$slack_link");
    }

  
  $get_twitch_key = array_search("theme_twitch", array_column($theme_options, 'theme_label'));
    if(isset($theme_options[$get_twitch_key]['theme_value']) && ($theme_options[$get_twitch_key]['theme_value'] != '')) {
        $twitch_page = $theme_options[$get_twitch_key]['theme_value'];
        $twitch_link = '<a class="p-1" href="'.$twitch_page.'" title="'.$twitch_page.'"><i class="bi bi-twitch"></i></a> ';
        $smarty->assign('twitch_link', "$twitch_link");
    }

  
  $get_twitter_key = array_search("theme_twitter", array_column($theme_options, 'theme_label'));
    if(isset($theme_options[$get_twitter_key]['theme_value']) && ($theme_options[$get_twitter_key]['theme_value'] != '')) {
        $twitter_page = $theme_options[$get_twitter_key]['theme_value'];
        $twitter_link = '<a class="p-1" href="'.$twitter_page.'" title="'.$twitter_page.'"><i class="bi bi-twitter"></i></a> ';
        $smarty->assign('twitter_link', "$twitter_link");
    }

  
  $get_youtube_key = array_search("theme_youtube", array_column($theme_options, 'theme_label'));
    if(isset($theme_options[$get_youtube_key]['theme_value']) && ($theme_options[$get_youtube_key]['theme_value'] != '')) {
        $youtube_page = $theme_options[$get_youtube_key]['theme_value'];
        $youtube_link = '<a class="p-1" href="'.$youtube_page.'" title="'.$youtube_page.'"><i class="bi bi-youtube"></i></a> ';
        $smarty->assign('youtube_link', "$youtube_link");
    }

  
  $sm_string = '';
  $sm_string = $discord_page.$facebook_page.$github_page.$insta_page.$linkedin_page.$slack_page.$twitch_page.$twitter_page.$youtube_page;
  if($sm_string != '') {
	  $smarty->assign('social_media_block', "show");
  }



 
} elseif(defined('SE_SECTION') && SE_SECTION == 'backend') {

	/**
	 * Theme options for acp > system > layout & design
	 */

	 $theme_options = array(
		 "discord" => "Discord (URL)",
		 "facebook" => "Facebook Page or Account (URL)",
		 "github" => "GitHub (URL)",
		 "instagram" => "Instagram (URL)",
		 "linkedin" => "LinkedIn (URL)",
		 "slack" => "Slack (URL)",
		 "twitch" => "Twitch (URL)",
		 "x" => "X Account (URL)",
		 "youtube" => "YouTube (URL)"
	 );
	
}

?>