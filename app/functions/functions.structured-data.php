<?php

/**
 * structured data (schema.org JSON-LD) for the frontend
 *
 * The handlers describe the displayed content in $structuredDataContext
 * (type + data), app/app.php turns it into a @graph with the website, the
 * organization from /admin/settings/ and the content itself. Themes and
 * plugins can change the graph with the frontend filter "structured_data.graph".
 */

/**
 * schema.org types an editor can choose for a page (page_schema_type),
 * '' means WebPage
 *
 * @return string[]
 */
function se_structured_data_page_types(): array {
    return ['AboutPage', 'ContactPage', 'FAQPage', 'CollectionPage', 'ItemPage', 'ProfilePage', 'SearchResultsPage'];
}

/**
 * schema.org types for the organization in /admin/settings/ - Organization is
 * the generic type (projects, clubs, ...), Store and LocalBusiness have a
 * physical location, OnlineStore sells online only
 *
 * @return string[]
 */
function se_structured_data_org_types(): array {
    return ['Organization', 'Corporation', 'NGO', 'EducationalOrganization', 'OnlineStore', 'LocalBusiness', 'Store', 'Person'];
}

/**
 * plain text of a stored (html-encoded) value
 */
function se_structured_data_text(mixed $value): string {
    $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');
}

/**
 * absolute url of a path or url
 */
function se_structured_data_url(string $path, string $base_url): string {
    if($path === '' || preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return rtrim($base_url, '/').'/'.ltrim(str_replace('../', '/', $path), '/');
}

/**
 * removes empty values (recursively), so the output contains only what is known
 */
function se_structured_data_clean(array $node): array {
    foreach($node as $key => $value) {
        if(is_array($value)) {
            $value = se_structured_data_clean($value);
            $node[$key] = $value;
        }
        if($value === '' || $value === null || $value === []) {
            unset($node[$key]);
        }
    }
    return $node;
}

/**
 * builds the @graph for the current request
 *
 * @param array $context $structuredDataContext ['type' => ..., 'data' => [...], 'image' => absolute url (optional)]
 * @param array $se_settings
 * @param string $base_url $se_base_url
 * @param string $language language of the page
 * @return array list of schema.org nodes
 */
function se_structured_data_build(array $context, array $se_settings, string $base_url, string $language = ''): array {

    global $se_branding_path;

    $base_url = rtrim($base_url, '/').'/';
    $website_id = $base_url.'#website';
    $org_id = $base_url.'#organization';

    $graph = [];

    /* organization, only if it has a name */

    $org_name = se_structured_data_text($se_settings['org_name'] ?? '');
    if($org_name === '') {
        $org_name = se_structured_data_text($se_settings['pagename'] ?? '');
    }
    $org_type = (string) ($se_settings['org_type'] ?? '');
    if(!in_array($org_type, se_structured_data_org_types(), true)) {
        $org_type = 'Organization';
    }

    $has_org = $org_name !== '';
    if($has_org) {
        $logo = (string) ($se_settings['pagelogo'] ?? '');
        if($logo !== '' && $logo !== 'null') {
            $logo = se_structured_data_url('/'.$se_branding_path.'/'.$logo, $base_url);
        } else {
            $logo = '';
        }

        $same_as = preg_split('/\R/', html_entity_decode((string) ($se_settings['org_sameas'] ?? ''), ENT_QUOTES, 'UTF-8')) ?: [];
        $same_as = array_values(array_unique(array_filter(array_map('trim', $same_as), static fn($url) => preg_match('#^https?://#i', $url))));

        $graph[] = se_structured_data_clean([
            '@type' => $org_type,
            '@id' => $org_id,
            'name' => $org_name,
            'url' => $base_url,
            // a Person has no logo, schema.org uses image there
            ($org_type === 'Person' ? 'image' : 'logo') => $logo,
            'email' => se_structured_data_text($se_settings['org_email'] ?? ''),
            'telephone' => se_structured_data_text($se_settings['org_phone'] ?? ''),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => trim(se_structured_data_text($se_settings['org_street'] ?? '').' '.se_structured_data_text($se_settings['org_street_nbr'] ?? '')),
                'postalCode' => se_structured_data_text($se_settings['org_zip'] ?? ''),
                'addressLocality' => se_structured_data_text($se_settings['org_city'] ?? ''),
                'addressCountry' => se_structured_data_text($se_settings['org_country'] ?? '')
            ],
            'sameAs' => $same_as
        ]);
        // an address with nothing but its @type says nothing
        $last = array_key_last($graph);
        if(isset($graph[$last]['address']) && count($graph[$last]['address']) === 1) {
            unset($graph[$last]['address']);
        }
    }

    /* website */

    $graph[] = se_structured_data_clean([
        '@type' => 'WebSite',
        '@id' => $website_id,
        'name' => se_structured_data_text($se_settings['pagename'] ?? ''),
        'description' => se_structured_data_text($se_settings['pagedescription'] ?? ''),
        'url' => $base_url,
        'inLanguage' => $language,
        'publisher' => $has_org ? ['@id' => $org_id] : null
    ]);

    /* the displayed content */

    $data = is_array($context['data'] ?? null) ? $context['data'] : [];
    $image = (string) ($context['image'] ?? '');

    switch($context['type'] ?? '') {

        case 'Product':
            $graph[] = se_structured_data_clean([
                '@type' => 'Product',
                'name' => se_structured_data_text($data['meta_title'] ?? ''),
                'image' => se_structured_data_url((string) ($data['product_img_src'] ?? ''), $base_url),
                'description' => se_structured_data_text($data['meta_description'] ?? ''),
                'sku' => se_structured_data_text($data['product_number'] ?? ''),
                'offers' => [
                    '@type' => 'Offer',
                    'priceCurrency' => se_structured_data_text($data['product_currency'] ?? ''),
                    'price' => (string) ($data['product_price_gross'] ?? ''),
                    'availability' => 'https://schema.org/'.($data['product_availability'] ?? 'InStock'),
                    'url' => (string) ($data['product_canonical_url'] ?? '')
                ]
            ]);
            break;

        case 'BlogPosting':
            $url = (string) ($data['post_canonical_url'] ?? '');
            $graph[] = se_structured_data_clean([
                '@type' => 'BlogPosting',
                'headline' => se_structured_data_text($data['post_title'] ?? ''),
                'description' => se_structured_data_text($data['post_meta_description'] ?? ''),
                'image' => $image,
                'datePublished' => !empty($data['post_releasedate']) ? date('c', (int) $data['post_releasedate']) : '',
                'dateModified' => !empty($data['post_lastedit']) ? date('c', (int) $data['post_lastedit']) : '',
                'author' => se_structured_data_text($data['post_author'] ?? '') !== ''
                    ? ['@type' => 'Person', 'name' => se_structured_data_text($data['post_author'])]
                    : null,
                'publisher' => $has_org ? ['@id' => $org_id] : null,
                'mainEntityOfPage' => $url,
                'url' => $url,
                'inLanguage' => $language,
                'isPartOf' => ['@id' => $website_id]
            ]);
            break;

        case 'Event':
            $street = trim(se_structured_data_text($data['event_street'] ?? '').' '.se_structured_data_text($data['event_street_nbr'] ?? ''));
            $address = se_structured_data_clean([
                'streetAddress' => $street,
                'postalCode' => se_structured_data_text($data['event_zip'] ?? ''),
                'addressLocality' => se_structured_data_text($data['event_city'] ?? '')
            ]);
            $location = null;
            if($address !== []) {
                $location = [
                    '@type' => 'Place',
                    'name' => se_structured_data_text($data['event_city'] ?? '') ?: $street,
                    'address' => ['@type' => 'PostalAddress'] + $address
                ];
            }
            $graph[] = se_structured_data_clean([
                '@type' => 'Event',
                'name' => se_structured_data_text($data['title'] ?? ''),
                'description' => se_structured_data_text($data['meta_description'] ?? ''),
                'image' => $image,
                'startDate' => !empty($data['event_startdate']) ? date('c', (int) $data['event_startdate']) : '',
                'endDate' => !empty($data['event_enddate']) ? date('c', (int) $data['event_enddate']) : '',
                'eventStatus' => 'https://schema.org/EventScheduled',
                'location' => $location,
                'organizer' => $has_org ? ['@id' => $org_id] : null,
                'url' => (string) ($data['canonical_url'] ?? ''),
                'inLanguage' => $language
            ]);
            break;

        case 'WebPage':
            $page_type = (string) ($data['page_schema_type'] ?? '');
            if(!in_array($page_type, se_structured_data_page_types(), true)) {
                $page_type = 'WebPage';
            }
            $graph[] = se_structured_data_clean([
                '@type' => $page_type,
                'name' => se_structured_data_text($data['page_title'] ?? ''),
                'description' => se_structured_data_text($data['page_meta_description'] ?? ''),
                'url' => $base_url.ltrim((string) ($data['page_permalink'] ?? ''), '/'),
                'inLanguage' => $language,
                'isPartOf' => ['@id' => $website_id],
                'about' => $has_org && $page_type === 'AboutPage' ? ['@id' => $org_id] : null
            ]);
            break;
    }

    return se_apply_filters('frontend', 'structured_data.graph', $graph, $context);
}
