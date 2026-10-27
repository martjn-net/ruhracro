<?php
declare(strict_types=1);

page_head('programm', [ld_breadcrumb('programm')]);
?>
<main id="inhalt">
  <section class="section section--wide" aria-labelledby="titel">
    <h1 id="titel"><?= e(PAGES['programm'][lang()][1]) ?></h1>
    <p class="lead"><?= tr(
        'Acroyoga- und Akrobatik-Workshops vom <span class="nobr">' . e(event_dates()) . '</span> in der ' . e(VENUE['name']) . ', Essen. Vor jedem Workshop-Block zeigen die Teacher in kurzen Demos, was dich erwartet; danach wählst du deinen Workshop. Mehrere Workshops laufen parallel.',
        'Acroyoga and acrobatics workshops from <span class="nobr">' . e(event_dates()) . '</span> at the ' . e(VENUE['name']) . ', Essen. Before every workshop block the teachers show in short demos what to expect; then you choose your workshop. Several workshops run in parallel.'
    ) ?></p>
    <div class="program">
<?php render_program(); ?>
    </div>
    <p class="legend"><?= t('Workshops, Teacher und Inhalte der einzelnen Blöcke werden nach und nach veröffentlicht. Änderungen am Programm sind bis zur Veranstaltung möglich.',
                            'Workshops, teachers and the content of each block will be announced step by step. The programme may change until the event.') ?></p>
    <p class="more"><?= book_button() ?></p>
  </section>
</main>
<?php page_foot();
