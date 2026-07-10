<?php

/**
 * TinyMCE editor plugin - overview page in the addon manager.
 */

echo '<h2>TinyMCE Editor</h2>';
echo '<p>' . ($lang['nav_overview'] ?? 'Overview') . '</p>';
echo '<p>This plugin provides the WYSIWYG (TinyMCE) editor for content editing. '
    . 'It is a core editor and shipped with SwiftyEdit by default.</p>';
echo '<p>Browser assets are served from <code>/assets/editors/tinymce/</code>.</p>';
