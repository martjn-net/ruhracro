<?php
// Template for web/lib/deploy.php (untracked). Without it the site runs as
// production at https://ruhracro.de in the classic design. Example: preview
// of the scene design in a sub-folder.
return [
    'site_url' => 'https://example.org/acro', // full URL incl. sub-folder, no trailing slash
    'preview'  => true,                        // noindex + preview banner
    'design'   => 'scene',                     // 'classic' (poster hero) or 'scene' (header/footer bands)
];
