<?php
declare(strict_types=1);

page_head('tickets', [ld_breadcrumb('tickets')]);
$eur = fn (int $p): string => lang() === 'en' ? "€$p" : "$p €";
?>
<main id="inhalt">
  <section class="section" aria-labelledby="titel">
    <h1 id="titel">Tickets</h1>
    <p class="lead"><?= t('Die Tickets gelten für das ganze Wochenende. Der Preis richtet sich nach deiner Gruppe.',
                          'Tickets are valid for the whole weekend. The price depends on your group.') ?></p>
    <p class="notice"><?= tr(
        '<strong>Verkauf nur über die Universität.</strong> Veranstalter der Convention ist der ' . e(L(EVENT['organizer'])) . '. Tickets gibt es ausschließlich über dessen Buchungssystem buchsys.de; dort buchst und bezahlst du, und es gelten dessen Bedingungen. Diese Website verkauft nichts, sie leitet dich nur zur Buchung weiter.',
        '<strong>Sold by the university only.</strong> The convention is hosted by the ' . e(L(EVENT['organizer'])) . '. Tickets are available exclusively through its booking system buchsys.de, where you book and pay under its terms. This website sells nothing; it only links you to the booking.'
    ) ?></p>
    <div class="narrow">
      <dl class="cards">
<?php foreach (TICKETS as $t): ?>
        <div<?= $t['available'] ? '' : ' class="sold"' ?>>
          <dt><?= e($t['name']) ?><?= $t['available'] ? '' : ' <span class="sold__tag">· ' . t('ausgebucht', 'sold out') . '</span>' ?></dt>
          <dd><strong><?= t('ab', 'from') ?> <?= e($eur(min($t['prices']))) ?></strong><?= e(implode(' / ', array_map($eur, $t['prices']))) ?> <?= t('je nach Preisgruppe', 'depending on price group') ?></dd>
        </div>
<?php endforeach; ?>
      </dl>
    </div>
    <div class="prose">
      <h2><?= t('Preisgruppen', 'Price groups') ?></h2>
      <ul>
<?php foreach (PRICE_GROUPS as $i => $group): ?>
        <li><strong><?= e(implode(' / ', array_map(fn ($t) => $eur($t['prices'][$i]), TICKETS))) ?></strong> (Early Bird / Regular): <?= e(L($group)) ?></li>
<?php endforeach; ?>
      </ul>
      <h2><?= t('Gut zu wissen', 'Good to know') ?></h2>
      <ul>
<?php if (lang() === 'en'): ?>
        <li>The first 12 tickets were sold at the early bird price; they are gone.</li>
        <li>Tickets are personal. Cancelling later is not possible because the places are firmly planned.</li>
        <li><strong>Helping pays off:</strong> for a helper shift (e.g. setup and teardown, check-in, awareness, teacher care, media) you get €30 back.</li>
        <li><strong>Teach a workshop:</strong> workshop leaders get a third of the ticket price back. Mention it when booking or contact us on Instagram.</li>
        <li><strong>Convention shirt:</strong> you can order a shirt or sports bra for about €15–20 when booking and pick it up on site.</li>
        <li><strong>Food:</strong> snacks are available all weekend. For lunch and evening breaks you can bring your own food; there are restaurants, takeaways and shops nearby.</li>
<?php else: ?>
        <li>Die ersten 12 Tickets gab es zum Early-Bird-Preis; diese sind vergeben.</li>
        <li>Tickets sind personengebunden. Eine spätere Abmeldung ist nicht möglich, weil die Plätze fest eingeplant werden.</li>
        <li><strong>Helfen lohnt sich:</strong> Für eine Helferschicht (z. B. Auf- und Abbau, Check-in, Awareness, Teacher-Betreuung, Medien) bekommst du 30 € zurück.</li>
        <li><strong>Selbst unterrichten:</strong> Workshop-Leitende erhalten ein Drittel des Ticketpreises zurück. Gib das bei der Anmeldung an oder melde dich über Instagram.</li>
        <li><strong>Convention-Shirt:</strong> Shirt oder Sport-BH für etwa 15–20 € kannst du bei der Anmeldung mitbestellen und vor Ort abholen.</li>
        <li><strong>Verpflegung:</strong> Snacks gibt es das ganze Wochenende. Für Mittag und Abend kannst du eigenes Essen mitbringen; in der Umgebung gibt es Restaurants, Imbisse und Einkaufsmöglichkeiten.</li>
<?php endif; ?>
      </ul>
    </div>
    <p class="more"><?= book_button() ?></p>
  </section>
</main>
<?php page_foot();
