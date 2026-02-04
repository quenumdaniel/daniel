<?php
$basePath = __DIR__;
$databasePath = $basePath . '/data/database.sqlite';
$schemaPath = $basePath . '/sql/schema.sql';

if (!is_dir($basePath . '/data')) {
  mkdir($basePath . '/data', 0777, true);
}

if (!file_exists($databasePath)) {
  $db = new PDO('sqlite:' . $databasePath);
  $schema = file_get_contents($schemaPath);
  $db->exec($schema);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comptabilité Église - Tableau de bord</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <header class="hero">
    <div>
      <h1>Comptabilité de l'Église</h1>
      <p>Suivi par dimanche, par mois, bilan et tendance d'évolution.</p>
    </div>
    <div class="hero-meta">
      <div>
        <span class="label">Solde actuel</span>
        <h2 id="current-balance">0</h2>
      </div>
      <div>
        <span class="label">Progression mensuelle</span>
        <h3 id="month-trend">--</h3>
      </div>
    </div>
  </header>

  <main>
    <section class="grid">
      <article class="card">
        <h2>Ajouter un dimanche</h2>
        <form id="service-form">
          <label>Date du culte
            <input type="date" name="service_date" required>
          </label>
          <label>Thème / Culte
            <input type="text" name="theme" placeholder="Culte de louange" required>
          </label>
          <label>Notes
            <textarea name="notes" rows="3" placeholder="Remarques, offrandes spéciales..."></textarea>
          </label>
          <button type="submit">Enregistrer</button>
        </form>
      </article>

      <article class="card">
        <h2>Ajouter une transaction</h2>
        <form id="transaction-form">
          <label>Type
            <select name="type" required>
              <option value="income">Entrée</option>
              <option value="expense">Sortie</option>
            </select>
          </label>
          <label>Date
            <input type="date" name="txn_date" required>
          </label>
          <label>Catégorie
            <input type="text" name="category" placeholder="Offrandes, Dons, Electricité..." required>
          </label>
          <label>Montant
            <input type="number" name="amount" step="0.01" min="0" required>
          </label>
          <label>Dimanche (facultatif)
            <select name="service_id" id="service-select">
              <option value="">-- Aucun --</option>
            </select>
          </label>
          <label>Description
            <textarea name="description" rows="2" placeholder="Détails supplémentaires..."></textarea>
          </label>
          <button type="submit">Ajouter</button>
        </form>
      </article>
    </section>

    <section class="grid">
      <article class="card wide">
        <div class="card-header">
          <h2>Résumé par dimanche</h2>
          <button class="ghost" id="refresh-services">Actualiser</button>
        </div>
        <div class="table" id="services-table">
          <div class="table-row table-header">
            <span>Date</span>
            <span>Thème</span>
            <span>Entrées</span>
            <span>Sorties</span>
            <span>Solde</span>
          </div>
        </div>
      </article>

      <article class="card wide">
        <div class="card-header">
          <h2>Bilan mensuel</h2>
          <button class="ghost" id="refresh-months">Actualiser</button>
        </div>
        <div class="table" id="months-table">
          <div class="table-row table-header">
            <span>Mois</span>
            <span>Entrées</span>
            <span>Sorties</span>
            <span>Net</span>
          </div>
        </div>
      </article>
    </section>

    <section class="grid">
      <article class="card">
        <h2>Vue d'avancement / régression</h2>
        <ul id="trend-list" class="trend-list"></ul>
      </article>
      <article class="card">
        <h2>Dernières transactions</h2>
        <ul id="recent-list" class="recent-list"></ul>
      </article>
    </section>
  </main>

  <footer>
    <p>Application locale PHP / SQLite pour la comptabilité d'une église.</p>
  </footer>

  <script src="assets/app.js"></script>
</body>
</html>
