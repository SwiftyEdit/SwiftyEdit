<?php

/**
 * ACE editor plugin - overview page in the addon manager.
 */

echo '<h2>ACE Code Editor</h2>';
echo '<p>' . ($lang['nav_overview'] ?? 'Overview') . '</p>';
echo '<p>This plugin provides the source-code editor (ACE) for content editing '
    . 'and powers the read-only code viewers in the backend. It is a core editor '
    . 'and shipped with SwiftyEdit by default.</p>';
echo '<p>Browser assets are served from <code>/assets/editors/ace/</code>.</p>';
