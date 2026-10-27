<?php
declare(strict_types=1);

page_head('home', [ld_event(), ld_website()], 'home');
if (DESIGN === 'classic'): ?>
<div class="layout">
<header class="hero">
  <div class="hero__core">
    <picture class="hero__art">
      <source media="(max-aspect-ratio: 3/5)"
              srcset="<?= e(public_url('/img/art-9x16-800.webp')) ?> 800w, <?= e(public_url('/img/art-9x16-1200.webp')) ?> 1200w, <?= e(public_url('/img/art-9x16-1800.webp')) ?> 1800w"
              sizes="100vw" width="1800" height="3200">
      <img src="<?= e(public_url('/img/art-4x5-1400.webp')) ?>"
           srcset="<?= e(public_url('/img/art-4x5-1400.webp')) ?> 1400w, <?= e(public_url('/img/art-4x5-2000.webp')) ?> 2000w, <?= e(public_url('/img/art-4x5-2806.webp')) ?> 2806w"
           sizes="(max-aspect-ratio: 4/5) 100vw, max(80svh, 32rem)"
           width="2806" height="3506" alt="" fetchpriority="high">
    </picture>

    <h1 class="visually-hidden"><?= e(EVENT['name']) ?> – <?= e(L(EVENT['subtitle'])) ?> <?= t('in Essen', 'in Essen, Germany') ?></h1>
    <p class="brush-text hero__place"><?= icon('pin') ?> <?= e(VENUE['city']) ?></p>
    <p class="brush-text hero__date"><?= event_dates(true) ?></p>

    <div class="hero__stage">
<?php event_facts('hero'); ?>
    </div>
  </div>
</header>
<?php endif; ?>

<main id="inhalt">
<?php if (DESIGN === 'scene'): /* header band comes from page_head(); facts as text below it */ ?>
  <section class="section welcome" aria-labelledby="titel">
    <h1 id="titel"><?= e(EVENT['name']) ?> <span class="welcome__sub"><?= e(L(EVENT['subtitle'])) ?> <?= t('in Essen', 'in Essen, Germany') ?></span></h1>
    <p class="welcome__when">
      <span class="welcome__place"><?= icon('pin') ?> <?= e(VENUE['city']) ?></span>
      <span><?= event_dates(true) ?></span>
    </p>
<?php event_facts('welcome'); ?>
  </section>
<?php endif; ?>
  <section class="section" aria-labelledby="festival">
    <h2 id="festival"><?= t('Drei Tage Acro im Ruhrgebiet', 'Three days of acro in the Ruhr area') ?></h2>
    <p class="lead"><?= t(
        'Die Ruhr Acro Convention ist ein Acroyoga Festival in Essen: Akrobatinnen, Akrobaten und AcroYogis aus dem Ruhrgebiet und weit darüber hinaus treffen sich ein Wochenende lang zum Lernen, Üben und gemeinsam Fliegen.',
        'The Ruhr Acro Convention is an acroyoga festival in Essen, Germany: acrobats and acroyogis from the Ruhr area and far beyond meet for a weekend of learning, practising and flying together.'
    ) ?></p>
    <ul class="offer">
      <li class="accent-mint">
        <img src="<?= e(public_url('/img/medal-workshops.webp')) ?>" width="240" height="239" alt="" loading="lazy" decoding="async">
        <h3>Workshops</h3>
        <p><?= t('8 Workshop-Blöcke, mehrere parallel, mit unterschiedlichen Schwerpunkten und Levels.',
                 '8 workshop blocks, several in parallel, with different focuses and levels.') ?></p>
      </li>
      <li class="accent-gold">
        <img src="<?= e(public_url('/img/medal-community.webp')) ?>" width="240" height="240" alt="" loading="lazy" decoding="async">
        <h3>Community</h3>
        <p><?= t('Welcome und Closing Circle, gemeinsame Pausen, neue Trainingspartner. Respekt verbindet.',
                 'Welcome and closing circle, shared breaks, new training partners. Respect connects.') ?></p>
      </li>
      <li class="accent-lilac">
        <img src="<?= e(public_url('/img/medal-jams.webp')) ?>" width="240" height="239" alt="" loading="lazy" decoding="async">
        <h3>Jams</h3>
        <p><?= t('In jeder Mittags- und Abendpause wird gejammt: freies Training mit Longen für Fortgeschrittene.',
                 'Every lunch and evening break is jam time: open training with lunges (safety lines) for advanced moves.') ?></p>
      </li>
    </ul>
  </section>

  <section class="section" aria-labelledby="level">
    <h2 id="level"><?= t('Für alle Level', 'For all levels') ?></h2>
    <div class="narrow">
      <p class="lead"><?= t(
          'Acroyoga für Anfänger und Fortgeschrittene: Jeder Workshop ist einer von vier Stufen zugeordnet. Bei der Anmeldung gibst du dein Level an, damit das Programm zur Gruppe passt. Ein fester Trainingspartner ist nicht nötig. Für Einsteiger wird es passende Angebote geben; ganz ohne Vorerfahrung empfiehlt sich ein Acroyoga Workshop vor der Convention.',
          'Acroyoga for beginners and advanced practitioners: every workshop is assigned to one of four levels. You state your level when booking so the programme fits the group. You do not need a fixed training partner. There will be suitable sessions for beginners; with no experience at all, an acroyoga workshop before the convention is recommended.'
      ) ?></p>
      <ol class="levels">
        <li><span class="meter" data-level="1" aria-hidden="true"><i></i><i></i><i></i><i></i></span><?= t('Anfänger', 'Beginner') ?></li>
        <li><span class="meter" data-level="2" aria-hidden="true"><i></i><i></i><i></i><i></i></span><?= t('Mittel', 'Intermediate') ?></li>
        <li><span class="meter" data-level="3" aria-hidden="true"><i></i><i></i><i></i><i></i></span><?= t('Fortgeschritten', 'Advanced') ?></li>
        <li><span class="meter" data-level="4" aria-hidden="true"><i></i><i></i><i></i><i></i></span><?= t('Extrem', 'Extreme') ?></li>
      </ol>
      <p class="intro"><?= t('Geplante Inhalte:', 'Planned content:') ?></p>
      <ul class="chips">
        <li>Standing Acro</li><li>Acroyoga</li><li>Hand to Hand</li><li>Foot to Hand</li><li>Pops</li><li>Whips</li><li>Icarians</li><li>Flows</li>
      </ul>
      <dl class="cards">
        <div>
          <dt>Standing Acro</dt>
          <dd><?= t('Die Base steht und trägt den Flyer, oft auf Schultern oder Händen.',
                    'The base stands and carries the flyer, often on shoulders or hands.') ?></dd>
        </div>
        <div>
          <dt>Acroyoga</dt>
          <dd><?= t('Die Base liegt am Boden und trägt den Flyer auf Füßen und Händen.',
                    'The base lies on the floor and carries the flyer on feet and hands.') ?></dd>
        </div>
      </dl>
      <p class="more"><a class="link" href="<?= e(page_url('tickets')) ?>"><?= t('Tickets und Preise', 'Tickets and prices') ?></a></p>
    </div>
  </section>
</main>
<?php if (DESIGN === 'classic'): ?>
</div>
<?php endif;
page_foot();
