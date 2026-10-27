<?php
declare(strict_types=1);

page_head('404');
?>
<main id="inhalt">
  <section class="section">
    <h1><?= t('Seite nicht gefunden', 'Page not found') ?></h1>
    <p class="lead"><?= t('Diese Adresse gibt es nicht (mehr). Programm, Tickets und Anreise findest du oben in der Navigation.',
                          'This address does not exist (any more). Programme, tickets and directions are in the navigation above.') ?></p>
    <p><a class="btn" href="<?= e(page_url('home')) ?>"><?= t('Zur Startseite', 'Go to the home page') ?></a></p>
  </section>
</main>
<?php page_foot();
