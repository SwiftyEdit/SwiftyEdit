<?php

/**
 * TinyMCE editor plugin - footer init.
 * Registers the editor with the ACP editor dispatcher (window.seEditors), keyed
 * by its editor id. The dispatcher lives in acp/index.php and calls the
 * attach/detach callbacks when the user switches editor.
 *
 * Provided by acp/index.php / acp/core/editors.php (in scope here):
 * @var array  $se_editor_current   this editor plugin's info.json "editor" block
 * @var string $editor_css_url          URL of the active theme's editor content CSS
 * @var string $editor_img_list_url     theme image-list endpoint for TinyMCE
 * @var string $editor_link_list_url    theme link-list endpoint for TinyMCE
 * @var string $tinyMCE_config_override optional full config shipped by the theme
 *
 * When the active theme ships a tinyMCE_config.js it fully replaces the
 * built-in config below (see acp/core/editors.php).
 */

$se_tinymce_has_override = isset($tinyMCE_config_override) && trim($tinyMCE_config_override) !== '';
$se_tinymce_id = $se_editor_current['id'] ?? 'tinymce';

?>
<script type="text/javascript">
    (function () {
        window.seEditors = window.seEditors || {};

<?php if (!$se_tinymce_has_override): ?>
        var tinymceConfig = {
            selector: 'textarea.mceEditor',
            license_key: 'gpl',
            base_url: '/assets/editors/tinymce/',
            language: languagePack,
            language_url: '/assets/editors/tinymce/langs/' + languagePack + '.js',
            skin: tinymce_skin,
            schema: 'html5',
            element_format: "html",
            allow_html_in_named_anchor: true,
            entity_encoding: "raw",
            promotion: false,
            menubar: "edit insert format",
            removed_menuitems: 'fontfamily fontsize',
            toolbar_items_size: 'small',
            content_css: "<?php echo $editor_css_url; ?>",
            body_class: 'mce-content-body',
            plugins: [
                'lists', 'advlist', 'autolink', 'link', 'image', 'charmap', 'preview', 'anchor',
                'searchreplace', 'visualblocks', 'code', 'fullscreen', 'wordcount',
                'media', 'table'
            ],
            toolbar1: "styles | bold italic underline subscript superscript removeformat | alignleft aligncenter alignright | bullist numlist | table | link unlink anchor image | fullscreen visualblocks  code",
            image_list: "<?php echo $editor_img_list_url; ?>",
            image_advtab: true,
            image_title: true,
            link_list: "<?php echo $editor_link_list_url; ?>",
            convert_urls: false,
            setup: function (editor) {
                editor.on('change', function () {
                    tinymce.triggerSave();
                });
            },
            style_formats: [
                {
                    title: 'Headlines', items: [
                        {title: 'Headline H1', block: 'h1'},
                        {title: 'Headline H2', block: 'h2'},
                        {title: 'Headline H3', block: 'h3'},
                        {title: 'Headline H4', block: 'h4'},
                        {title: 'Headline H5', block: 'h5'},
                        {title: 'Headline H6', block: 'h6'}
                    ]
                },
                {
                    title: 'Typo', items: [
                        {title: 'Absatz', block: 'p'},
                        {title: 'Lead paragraph', block: 'p', classes: 'lead'}
                    ]
                },
                {
                    title: 'Links', items: [
                        {title: 'btn', selector: 'a', classes: 'btn btn-secondary'},
                        {title: 'btn-primary', selector: 'a', classes: 'btn btn-primary'},
                        {title: 'btn-info', selector: 'a', classes: 'btn btn-info'},
                        {title: 'btn-success', selector: 'a', classes: 'btn btn-success'},
                        {title: 'btn-warning', selector: 'a', classes: 'btn btn-warning'},
                        {title: 'btn-danger', selector: 'a', classes: 'btn btn-danger'}
                    ]
                },
                {
                    title: 'Badge/Alerts', items: [
                        {title: 'Badge', inline: 'span', classes: 'badge bg-secondary'},
                        {title: 'Badge Success', inline: 'span', classes: 'badge bg-success'},
                        {title: 'Badge Warning', inline: 'span', classes: 'badge bg-warning text-dark'},
                        {title: 'Badge Danger', inline: 'span', classes: 'badge bg-danger'},
                        {title: 'Alert danger', block: 'div', classes: 'alert alert-danger'},
                        {title: 'Alert Success', block: 'div', classes: 'alert alert-success'},
                        {title: 'Alert info', block: 'div', classes: 'alert alert-info'}
                    ]
                },
                {
                    title: 'IMG', items: [
                        {title: 'fluid', selector: 'img', classes: 'img-fluid'},
                        {title: 'rounded', selector: 'img', classes: 'rounded'}
                    ]
                },
                {
                    title: 'Code', items: [
                        {title: 'Code <pre>', block: 'pre', classes: 'code'},
                        {title: 'Code <code>', inline: 'code', classes: 'code'}
                    ]
                }
            ],
            width: "100%",
            height: 480,
            remove_script_host: true,
            rel_list: [
                {title: 'Keine', value: ''},
                {title: 'Lightbox', value: 'lightbox'}
            ],
            extended_valid_elements: "*[*]",
            visual: true,
            paste_as_text: true
        };
<?php endif; ?>

        window.seEditors['<?php echo $se_tinymce_id; ?>'] = {
            cls: 'mceEditor',
            attach: function ($ta) {
                /*
                 * Initialise every WYSIWYG textarea, not only the content switch
                 * textarea: standalone fields (teasers, product/feature texts)
                 * carry a plain "mceEditor"/"mceEditor_small" class and must also
                 * become editors when the WYSIWYG mode is active.
                 */
<?php if ($se_tinymce_has_override): ?>
                <?php echo $tinyMCE_config_override; ?>
<?php else: ?>
                $('textarea.mceEditor, textarea.mceEditor_small').tinymce(tinymceConfig);
<?php endif; ?>
            },
            detach: function ($ta) {
                if (window.tinymce && tinymce.get().length > 0) {
                    tinymce.remove();
                    $('div.mceEditor').remove();
                }
            }
        };
    })();
</script>
