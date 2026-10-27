<?php
declare(strict_types=1);

page_head('impressum');
$host = ext(EVENT['organizer_url'], L(EVENT['organizer']));
$booking = ext(EVENT['booking_url'], 'buchsys.de');
?>
<main id="inhalt">
  <section class="section" aria-labelledby="titel">
    <h1 id="titel"><?= e(PAGES['impressum'][lang()][1]) ?></h1>
    <div class="prose">
<?php if (lang() === 'en'): ?>
      <p class="fineprint">This is a translation for convenience. The <a href="<?= e(page_url('impressum', 'de')) ?>" lang="de">German Impressum</a> is legally binding.</p>
<?php endif; ?>
      <h2><?= t('Angaben gemäß § 5 DDG', 'Information pursuant to § 5 DDG (German Digital Services Act)') ?></h2>
      <address>
        <strong><?= e(PROVIDER['name']) ?></strong><br>
        <?= e(PROVIDER['street']) ?><br>
        <?= e(PROVIDER['city']) ?><br>
        <?= t('Deutschland', 'Germany') ?>
      </address>
      <p>
<?php if (PROVIDER['phone'] ?? ''): ?>
        <?= t('Telefon', 'Phone') ?>: <a href="tel:<?= e(preg_replace('~[^+0-9]~', '', PROVIDER['phone'])) ?>"><?= e(PROVIDER['phone']) ?></a><br>
<?php endif; ?>
        <?= t('E-Mail', 'Email') ?>: <a href="mailto:<?= e(PROVIDER['email']) ?>"><?= e(PROVIDER['email']) ?></a>
      </p>
<?php if (PROVIDER['vat_id'] ?? ''): ?>
      <p><?= t('Umsatzsteuer-Identifikationsnummer gemäß § 27a UStG', 'VAT identification number pursuant to § 27a UStG') ?>: <?= e(PROVIDER['vat_id']) ?></p>
<?php endif; ?>

<?php if (lang() === 'en'): ?>
      <h2>Website operator and event host</h2>
      <p><?= e(PROVIDER['name']) ?> organises the Ruhr Acro Convention 2027 and operates this website. The event is hosted by the <?= $host ?>.</p>

      <h2>No sales through this website</h2>
      <p>This website provides information only. No goods or services are sold and no contracts are concluded through it. Tickets are sold exclusively by the university sports of the University of Duisburg-Essen via its booking system <?= $booking ?>; its terms apply to booking, payment and questions about your booking.</p>

      <h2>Consumer dispute resolution</h2>
      <p>We are neither obliged nor willing to take part in dispute resolution proceedings before a consumer arbitration board.</p>

      <h2>Liability for content and links</h2>
      <p>As a service provider we are responsible for our own content on these pages under general law. The operators of linked external websites are solely responsible for their content; no legal violations were apparent at the time of linking.</p>

      <h2>Image rights</h2>
      <p>Illustrations and poster: Ruhr Acro Convention.</p>
<?php else: ?>
      <h2>Betreiber der Website und Veranstalter</h2>
      <p><?= e(PROVIDER['name']) ?> organisiert die Ruhr Acro Convention 2027 und betreibt diese Website. Veranstalter der Convention ist der <?= $host ?>.</p>

      <h2>Kein Verkauf über diese Website</h2>
      <p>Diese Website dient ausschließlich der Information. Über sie werden weder Waren noch Dienstleistungen verkauft und keine Verträge geschlossen. Tickets verkauft ausschließlich der Hochschulsport der Universität Duisburg-Essen über sein Buchungssystem <?= $booking ?>; für Buchung, Bezahlung und Rückfragen zur Buchung gelten dessen Bedingungen.</p>

      <h2>Verbraucherstreitbeilegung</h2>
      <p>Wir sind nicht verpflichtet und nicht bereit, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>

      <h2>Haftung für Inhalte und Links</h2>
      <p>Als Diensteanbieter sind wir für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Für Inhalte externer Links sind ausschließlich deren Betreiber verantwortlich; zum Zeitpunkt der Verlinkung waren keine Rechtsverstöße erkennbar.</p>

      <h2>Bildrechte</h2>
      <p>Illustrationen und Poster: Ruhr Acro Convention.</p>
<?php endif; ?>
    </div>
  </section>
</main>
<?php page_foot();
