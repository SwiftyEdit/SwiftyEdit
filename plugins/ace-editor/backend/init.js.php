<?php

/**
 * ACE editor plugin - footer init.
 *
 * Two responsibilities:
 *  1. Register the source-code editor with the ACP editor dispatcher
 *     (window.seEditors), keyed by its editor id, so users can switch content
 *     editing to the ACE code view.
 *  2. Turn every read-only <textarea data-editor="..."> into an ACE viewer
 *     (used e.g. by the snippet/template source modals). This logic used to
 *     live in the theme's backend.js and moved here with the editor.
 *
 * @var array $se_editor_current  this editor plugin's info.json "editor" block
 * The `ace_theme` JS global is defined in the ACP <head>.
 */

$se_ace_id = $se_editor_current['id'] ?? 'ace';

?>
<script type="text/javascript">
    (function () {
        window.seEditors = window.seEditors || {};

        if (window.ace) {
            ace.config.set('basePath', '/assets/editors/ace');
            ace.config.set('modePath', '/assets/editors/ace');
            ace.config.set('themePath', '/assets/editors/ace');
        }

        // Editable code view for the content editor switch.
        window.seEditors['<?php echo $se_ace_id; ?>'] = {
            cls: 'aceEditor_code',
            attach: function ($ta) {
                $ta.each(function () {
                    var textarea = $(this);
                    var editDiv = $('<div>', {
                        position: 'absolute',
                        'class': textarea.attr('class') + ' aceCodeEditor'
                    }).insertBefore(textarea);
                    textarea.hide();

                    var aceEditor = ace.edit(editDiv[0]);
                    aceEditor.$blockScrolling = Infinity;
                    aceEditor.getSession().setMode('ace/mode/html');
                    aceEditor.getSession().setValue(textarea.val());
                    aceEditor.setTheme('ace/theme/' + ace_theme);
                    aceEditor.getSession().setUseWorker(false);
                    aceEditor.setShowPrintMargin(false);

                    aceEditor.getSession().on('change', function () {
                        textarea.val(aceEditor.getSession().getValue());
                    });
                });
            },
            detach: function ($ta) {
                $('.aceCodeEditor').remove();
                $ta.show();
            }
        };

        // Read-only code viewers: <textarea data-editor="html"> etc.
        $(function () {
            if (!window.ace) {
                return;
            }
            $('textarea[data-editor]').each(function () {
                var textarea = $(this);
                var mode = textarea.data('editor');
                var editDiv = $('<div>', {
                    position: 'absolute',
                    width: '100%',
                    height: '400px',
                    'class': textarea.attr('class')
                }).insertBefore(textarea);
                textarea.css('display', 'none');
                var editor = ace.edit(editDiv[0]);
                editor.$blockScrolling = Infinity;
                editor.getSession().setValue(textarea.val());
                editor.setTheme("ace/theme/" + ace_theme);
                editor.getSession().setMode("ace/mode/" + mode);
                editor.getSession().setUseWorker(false);
                editor.setShowPrintMargin(false);
                editor.setReadOnly(true);
            });
        });
    })();
</script>
