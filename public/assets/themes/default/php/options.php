<?php

/**
 * default Theme options
 * @var object $smarty
 * @var string $se_base_url
 * @var string $first_product_img_src
 * @var string $target_page
 *
 * @var array $theme_values data from -> edit page - theme tab
 * @var array $category_template_data data from -> edit category - theme tab
 */

if(defined('SE_SECTION') && SE_SECTION === 'frontend') {

 /**
  * for example manipulate page values
  * $page_title = 'Extended title by defaultTheme options file: ' . $page_title;
  * $smarty->assign('page_title', "$page_title");
  */

  /**
   * social media links of the theme options (/admin/addons/theme/default/)
   * smarty variable => [option labels, icon] - "x" was called "twitter" before,
   * installations that haven't saved the theme options since still store the old name
   */
  $theme_options = se_get_theme_options("default");
  $theme_option_values = array_column($theme_options, 'theme_value', 'theme_label');

  $social_media_links = [
      'discord_link' => [['theme_discord'], 'discord'],
      'fb_link' => [['theme_facebook'], 'facebook'],
      'github_link' => [['theme_github'], 'github'],
      'insta_link' => [['theme_instagram'], 'instagram'],
      'linkedin_link' => [['theme_linkedin'], 'linkedin'],
      'slack_link' => [['theme_slack'], 'slack'],
      'twitch_link' => [['theme_twitch'], 'twitch'],
      'twitter_link' => [['theme_x', 'theme_twitter'], 'twitter-x'],
      'youtube_link' => [['theme_youtube'], 'youtube']
  ];

  $show_social_media = false;
  foreach($social_media_links as $smarty_var => [$option_labels, $icon_name]) {
      $url = '';
      foreach($option_labels as $option_label) {
          $url = trim((string) ($theme_option_values[$option_label] ?? ''));
          if($url != '') {
              break;
          }
      }
      if($url == '') {
          continue;
      }
      // values may be stored html-encoded (sanitizeUserInputs) or raw (older versions)
      $url = htmlspecialchars(html_entity_decode($url, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
      $smarty->assign($smarty_var, '<a class="p-1" href="'.$url.'" title="'.$url.'"><i class="bi bi-'.$icon_name.'"></i></a> ');
      $show_social_media = true;
  }
  if($show_social_media) {
      $smarty->assign('social_media_block', "show");
  }


  // $structuredDataContext
    if (!empty($structuredDataContext['type']) && !empty($structuredDataContext['data'])) {
        switch ($structuredDataContext['type']) {
            case 'Product':
                $data = $structuredDataContext['data'];
                $page_json_ld = [
                    "@context" => "https://schema.org",
                    "@type" => "Product",
                    "name" => html_entity_decode($data['meta_title']) ?? '',
                    "image" => rtrim($se_base_url, '/') . $first_product_img_src ?? [],
                    "description" => html_entity_decode($product_data['meta_description']) ?? '',
                    "sku" => $data['product_number'] ?? '',
                    "offers" => [
                        "@type" => "Offer",
                        "priceCurrency" => $data['product_currency'],
                        "price" => $data['product_price_gross'] ?? '',
                        "availability" => "https://schema.org/" . ($data['product_availability'] ?? 'InStock'),
                        "url" => $se_base_url.$target_page.$product_data['slug'] ?? ''
                    ]
                ];
                break;

            case 'WebPage':
                $data = $structuredDataContext['data'];
                $page_json_ld = [
                    "@context" => "https://schema.org",
                    "@type" => "WebPage",
                    "name" => $data['paget_title'] ?? '',
                    "description" => $data['page_meta_description'] ?? '',
                    "url" => $se_base_url.$data['page_permalink'] ?? ''
                ];
                break;
        }
    }

    /**
     * which optional theme components (CSS+JS) to load on this page, auto-
     * discovered from dist/ (see php/theme-components.php). Pages saved
     * before this option existed (or with "all components" selected) get
     * everything, matching the old single-bundle build.
     */
    require_once __DIR__.'/theme-components.php';

    $theme_dist_dir = __DIR__.'/../dist';
    $load_all_components = ($theme_values['components_mode'] ?? 'all') !== 'custom';

    $se_theme_components = [];
    foreach (se_theme_component_ids($theme_dist_dir) as $id) {
        $se_theme_components[$id] = [
            'enabled' => $load_all_components || ($theme_values['comp_'.$id] ?? '') == '1',
            'has_css' => se_theme_component_has_css($theme_dist_dir, $id),
            'has_js' => se_theme_component_has_js($theme_dist_dir, $id),
        ];
    }
    $smarty->assign('se_theme_components', $se_theme_components);

    /**
     * Shop/blog/event pages reuse the same $page_contents row (and thus the
     * same $theme_values) for the category listing and for a single
     * product/post/event display - the URL just carries an extra slug.
     * Handlers set $display_mode to tell those cases apart; skip the page's
     * own teaser on single-item displays, it belongs to the listing only.
     */
    $is_single_item_display = isset($display_mode) && in_array($display_mode, ['show_product', 'show_post', 'show_event'], true);

    if(!$is_single_item_display && $theme_values['teaser_text'] != '') {
        $teaser_text = html_entity_decode($theme_values['teaser_text'], ENT_QUOTES | ENT_XML1, 'UTF-8');
        $smarty->assign('teaser_text', $teaser_text);
    }

    // overwrite teaser text from $theme_values['teaser_text']; only set by
    // products-list.php/posts-list.php when displaying a category
    if(isset($category_template_data) && is_array($category_template_data) && $category_template_data['teaser_text'] != '') {
        $teaser_text = html_entity_decode($category_template_data['teaser_text'], ENT_QUOTES | ENT_XML1, 'UTF-8');
        $smarty->assign('teaser_text', $teaser_text);
    }

} elseif(defined('SE_SECTION') && SE_SECTION === 'backend') {

	/**
	 * Theme options for /admin/addons/theme/default/
	 */

    $theme_options = [
        "discord" => [
            "label" => "Discord (URL)",
            "type"  => "text"
        ],
        "facebook" => [
            "label" => "Facebook Page or Account (URL)",
            "type"  => "text"
        ],
        "github" => [
            "label" => "GitHub (URL)",
            "type"  => "text"
        ],
        "instagram" => [
            "label" => "Instagram (URL)",
            "type"  => "text"
        ],
        "linkedin" => [
            "label" => "LinkedIn (URL)",
            "type"  => "text"
        ],
        "slack" => [
            "label" => "Slack (URL)",
            "type"  => "text"
        ],
        "twitch" => [
            "label" => "Twitch (URL)",
            "type"  => "text"
        ],
        "x" => [
            "label" => "X Account (URL)",
            "type"  => "text"
        ],
        "youtube" => [
            "label" => "YouTube (URL)",
            "type"  => "text"
        ],
        "include_tracking" => [
            "label" => "Include Tracking Code",
            "type"  => "checkbox"
        ],
        "theme_style" => [
            "label" => "Theme Style",
            "type"  => "select",
            "options" => [
                "light" => "Light",
                "dark" => "Dark",
                "auto" => "Auto"
            ]
        ],
    ];

}