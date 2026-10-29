<?php
declare(strict_types=1);

page_head('anreise', [ld_breadcrumb('anreise')]);
?>
<main id="inhalt">
  <section class="section" aria-labelledby="titel">
    <h1 id="titel"><?= e(PAGES['anreise'][lang()][1]) ?></h1>
    <div class="prose">
      <address>
        <strong><?= e(VENUE['name']) ?></strong><br>
        <?= e(VENUE['street']) ?>, <?= e(VENUE['zip'] . ' ' . VENUE['city']) ?><br>
        <?= e(L(VENUE['entrance'])) ?>
      </address>
      <p><?= t('Die Halle liegt am Campus Essen der Universität Duisburg-Essen in der Essener Innenstadt, rund 150 m östlich des Viehofer Platzes. Der Zugang erfolgt über die Waldthausenstraße.',
               'The hall is on the Essen campus of the University of Duisburg-Essen in Essen city centre, about 150 m east of Viehofer Platz. The entrance is on Waldthausenstraße.') ?></p>
      <p><?= ext(osm_url(), tr('Route planen', 'Plan your route'), 'btn', icon('map')) ?></p>

      <h2><?= t('Parken', 'Parking') ?></h2>
      <p><?= t('Rund 40 öffentliche, kostenlose Parkplätze liegen höchstens eine Minute zu Fuß entfernt, teilweise direkt vor der Halle; in der Umgebung gibt es weitere. Für die Convention sind keine Parkplätze reserviert.',
               'About 40 free public parking spaces are at most a one-minute walk away, some right in front of the hall; there are more in the surrounding area. No parking spaces are reserved for the convention.') ?></p>

      <h2><?= t('Gut zu wissen für dein Wochenende', 'Good to know for your weekend') ?></h2>
      <ul>
<?php if (lang() === 'en'): ?>
        <li><strong>Check-in:</strong> Friday 16:00–17:20, Saturday from 9:10, Sunday from 9:20. On Sunday the convention ends at about 17:30 after the closing circle.</li>
        <li><strong>Food:</strong> snacks are available all weekend. You can bring your own food for lunch and evening breaks; there is an area on site to eat together. Restaurants, takeaways and shops are right nearby.</li>
        <li><strong>Training:</strong> breaks leave time for jams and open training; several lunges (safety lines) are available for more advanced moves.</li>
<?php else: ?>
        <li><strong>Check-in:</strong> Freitag 16:00–17:20, Samstag ab 9:10, Sonntag ab 9:20. Am Sonntag endet die Convention gegen 17:30 nach dem Closing Circle.</li>
        <li><strong>Essen:</strong> Snacks gibt es das ganze Wochenende. Für Mittags- und Abendpausen kannst du eigenes Essen mitbringen; vor Ort gibt es einen Bereich zum gemeinsamen Essen. In der direkten Umgebung gibt es Restaurants, Imbisse und Einkaufsmöglichkeiten.</li>
        <li><strong>Training:</strong> In den Pausen ist Zeit für Jams und freies Training; für fortgeschrittenere Elemente stehen mehrere Longen zur Verfügung.</li>
<?php endif; ?>
      </ul>
    </div>
  </section>
</main>
<?php page_foot();
