<?php
declare(strict_types=1);

page_head('datenschutz');
$retention = L(HOSTING['log_retention']);
$mail = '<a href="mailto:' . e(PROVIDER['email']) . '">' . e(PROVIDER['email']) . '</a>';
?>
<main id="inhalt">
  <section class="section" aria-labelledby="titel">
    <h1 id="titel"><?= tr('Datenschutz&shy;erklärung', 'Privacy policy') ?></h1>
    <div class="prose">
<?php if (lang() === 'en'): ?>
      <p class="fineprint">This is a translation for convenience. The <a href="<?= e(page_url('datenschutz', 'de')) ?>" lang="de">German privacy policy</a> is legally binding.</p>

      <h2>In short</h2>
      <p>This website collects no data of its own: no cookies, no forms, no tracking or analytics, no content from third parties. Fonts and images are served from our own web space. The only personal data processed is what every web server needs to deliver a page.</p>

      <h2>Controller</h2>
      <address>
        <?= e(PROVIDER['name']) ?><br>
        <?= e(PROVIDER['street']) ?>, <?= e(PROVIDER['city']) ?><br>
        Email: <?= $mail ?>
      </address>

      <h2>Server log files</h2>
      <p>To deliver a page to your device, the web server necessarily processes your IP address. For each request the server logs (access and error logs) usually record date and time, the requested address, the status code, the amount of data transferred, the referrer and the browser identifier; the IP address is stored there in anonymised form only. The purpose is delivering the website and operating it securely. Legal basis: Art. 6(1)(f) GDPR (legitimate interest in secure operation). The log files are deleted automatically after <?= e($retention) ?>.</p>

      <h2>Hosting</h2>
      <p>The website is hosted by <?= e(HOSTING['name']) ?>, <?= e(HOSTING['address']) ?>, Germany, which processes the data described above on our behalf under a data processing agreement (Art. 28 GDPR). Processing takes place exclusively in the European Union or the European Economic Area; data is not transferred to third countries.</p>

      <h2>Links to other services</h2>
      <p>The links “Book now” (university sports of the University of Duisburg-Essen, buchsys.de), “Plan your route” (OpenStreetMap) and to our Instagram profile open the respective service in a new tab. No data is transferred to these providers until you click a link; from then on their privacy policies apply. Bookings and the data required for them are processed by the university sports of the University of Duisburg-Essen, not by this website.</p>

      <h2>Your rights</h2>
      <p>You have the right of access (Art. 15 GDPR), rectification (Art. 16), erasure (Art. 17), restriction of processing (Art. 18), data portability (Art. 20) and objection (Art. 21). You may also lodge a complaint with a data protection supervisory authority (Art. 77 GDPR), e.g. the State Commissioner for Data Protection and Freedom of Information of North Rhine-Westphalia. Please send requests to the email address above.</p>

      <p class="fineprint">As of October 2026</p>
<?php else: ?>
      <h2>Kurz gesagt</h2>
      <p>Diese Website erhebt selbst keine Daten: keine Cookies, keine Formulare, kein Tracking und keine Analyse, keine Inhalte von Dritten. Schriften und Bilder liegen auf unserem eigenen Webspace. Verarbeitet werden nur die Daten, die jeder Webserver zum Ausliefern einer Seite braucht.</p>

      <h2>Verantwortlicher</h2>
      <address>
        <?= e(PROVIDER['name']) ?><br>
        <?= e(PROVIDER['street']) ?>, <?= e(PROVIDER['city']) ?><br>
        E-Mail: <?= $mail ?>
      </address>

      <h2>Server-Logdateien</h2>
      <p>Um eine Seite an dein Gerät auszuliefern, verarbeitet der Webserver zwangsläufig deine IP-Adresse. In den Server-Protokollen (Zugriffs- und Fehlerprotokolle) werden zu jedem Abruf in der Regel Datum und Uhrzeit, die abgerufene Adresse, der Statuscode, die übertragene Datenmenge, der Referrer und die Browser-Kennung gespeichert; die IP-Adresse wird dort nur anonymisiert gespeichert. Zweck ist die Auslieferung und der sichere Betrieb der Website. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO (berechtigtes Interesse an einem sicheren Betrieb). Die Protokolle werden nach <?= e($retention) ?> automatisch gelöscht.</p>

      <h2>Hosting</h2>
      <p>Gehostet wird die Website bei der <?= e(HOSTING['name']) ?>, <?= e(HOSTING['address']) ?>, die die oben genannten Daten in unserem Auftrag auf Grundlage eines Vertrags zur Auftragsverarbeitung verarbeitet (Art. 28 DSGVO). Die Verarbeitung findet ausschließlich in der Europäischen Union bzw. im Europäischen Wirtschaftsraum statt; eine Übermittlung in Drittländer erfolgt nicht.</p>

      <h2>Links zu anderen Angeboten</h2>
      <p>Die Links „Jetzt buchen“ (Hochschulsport der Universität Duisburg-Essen, buchsys.de), „Route planen“ (OpenStreetMap) und zu unserem Instagram-Profil öffnen das jeweilige Angebot in einem neuen Tab. Daten werden erst dann an den jeweiligen Anbieter übertragen, wenn du den Link anklickst; ab dann gelten dessen Datenschutzbestimmungen. Die Buchung und die dafür nötigen Angaben verarbeitet der Hochschulsport der Universität Duisburg-Essen, nicht diese Website.</p>

      <h2>Deine Rechte</h2>
      <p>Du hast das Recht auf Auskunft (Art. 15 DSGVO), Berichtigung (Art. 16), Löschung (Art. 17), Einschränkung der Verarbeitung (Art. 18), Datenübertragbarkeit (Art. 20) und Widerspruch (Art. 21). Außerdem kannst du dich bei einer Datenschutz-Aufsichtsbehörde beschweren (Art. 77 DSGVO), z. B. bei der Landesbeauftragten für Datenschutz und Informationsfreiheit Nordrhein-Westfalen. Anfragen richtest du an die oben genannte E-Mail-Adresse.</p>

      <p class="fineprint">Stand: Oktober 2026</p>
<?php endif; ?>
    </div>
  </section>
</main>
<?php page_foot();
