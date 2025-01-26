<?php
/**************************************************************
 * eintragen.php (alles in einer Seite)
 * ------------------------------------------------------------
 * 1) DB-Verbindung
 * 2) Interface & Repository (DIP)
 * 3) ViewModel (MVVM)
 * 4) Composition Root + Controller-Logik
 * 5) HTML-View (Formular & Ausgabe)
 *
 *  - Nur eine Seite ("eintragen.html" wird hier integriert)
 *  - Sicherheitsmaßnahmen: htmlspecialchars, intval, Prepared Statement
 **************************************************************/

/* --- 1) DB-VERBINDUNG --- */
$dbHost = "localhost";       // Anpassen
$dbUser = "root";            // Anpassen
$dbPass = "";                // Anpassen
$dbName = "deine_datenbank"; // Anpassen

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_error) {
    die("DB-Verbindungsfehler: " . $mysqli->connect_error);
}

/* --- 2) INTERFACE für das Repository (DIP) --- */
interface ITeamRepository
{
    /**
     * Speichert einen neuen Mitarbeitereintrag in der Tabelle team.
     * Gibt true zurück bei Erfolg, false bei Fehler.
     */
    public function insertTeamMember(string $vorname, string $nachname, int $eintrittsjahr): bool;
}

/* --- 3) KONKRETES Repository (implements ITeamRepository) --- */
class TeamRepository implements ITeamRepository
{
    private mysqli $conn;

    // Konstruktor (DB-Verbindung injizieren)
    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Fügt einen neuen Datensatz in die Tabelle 'team' ein.
     * nutzt Prepared Statement zum Schutz vor SQL-Injection.
     */
    public function insertTeamMember(string $vorname, string $nachname, int $eintrittsjahr): bool
    {
        $stmt = $this->conn->prepare("
            INSERT INTO team (vorname, nachname, eintrittsjahr)
            VALUES (?, ?, ?)
        ");
        if (!$stmt) {
            // Statement-Vorbereitung fehlgeschlagen
            return false;
        }
        // 'ssi' => 2 strings, 1 integer
        $stmt->bind_param("ssi", $vorname, $nachname, $eintrittsjahr);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;  // true/false
    }
}

/* --- 4) VIEWMODEL (MVVM) --- */
class TeamInsertViewModel
{
    private ITeamRepository $repo;

    public function __construct(ITeamRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Speichert einen Team-Mitarbeiter, gibt true/false zurück
     * je nach Erfolg.
     */
    public function saveTeamMember(string $vorname, string $nachname, int $jahr): bool
    {
        // Hier könnten ggf. weitere Geschäftslogiken, Validierungen etc. stattfinden
        return $this->repo->insertTeamMember($vorname, $nachname, $jahr);
    }
}

/* --- 5) COMPOSITION ROOT + Controller-Logik --- */

// (a) Repository und ViewModel anlegen (Poor Man's DI)
$repo = new TeamRepository($mysqli);
$vm   = new TeamInsertViewModel($repo);

// (b) Variablen für Ausgabe initialisieren
$successMsg = "";
$errorMsg   = "";

// (c) Prüfen, ob Formular abgesendet (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Daten abholen & säubern
    $vorname  = htmlspecialchars($_POST['vorname']  ?? '');
    $nachname = htmlspecialchars($_POST['nachname'] ?? '');
    $jahr     = intval($_POST['jahr'] ?? 0);

    // 2) Plausibilität minimal prüfen
    if ($vorname === '' || $nachname === '' || $jahr < 1900) {
        $errorMsg = "Bitte alle Felder korrekt ausfüllen (und Eintrittsjahr >= 1900)!";
    } else {
        // 3) Speichern via ViewModel
        $result = $vm->saveTeamMember($vorname, $nachname, $jahr);
        if ($result) {
            $successMsg = "Neues Teammitglied erfolgreich eingetragen!";
        } else {
            $errorMsg = "Fehler beim Einfügen in die Datenbank.";
        }
    }
}
// (d) DB-Verbindung wird automatisch am Ende geschlossen
//     oder man könnte $mysqli->close() hier noch aufrufen

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Neues Teammitglied eintragen (Single-Page, MVVM)</title>
    <link href="bootstrap5.0.1/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>

<main>
  <div class="container py-4">

    <!-- NAV-Bar (optional, abgekürzt) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
      <div class="container-fluid">
        <span class="navbar-brand h1">Mustermann GmbH</span>
        <!-- Restliche Navi hier ... -->
      </div>
    </nav>

    <!-- HEADLINE -->
    <div class="bg-white rounded-3 border border-ligh p-4 mb-4 mt-4">
      <h1 class="display-5 fw-bold">Neues Teammitglied eintragen</h1>

      <!-- Meldungen ausgeben -->
      <?php if ($successMsg !== ""): ?>
        <div class="alert alert-success" role="alert">
          <?= htmlspecialchars($successMsg) ?>
        </div>
      <?php endif; ?>

      <?php if ($errorMsg !== ""): ?>
        <div class="alert alert-danger" role="alert">
          <?= htmlspecialchars($errorMsg) ?>
        </div>
      <?php endif; ?>

      <!-- FORMULAR (SELF-SUBMIT) -->
      <form action="" method="post">

        <div class="mb-3">
          <label class="form-label">Vorname:</label>
          <input type="text" name="vorname" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Nachname:</label>
          <input type="text" name="nachname" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Eintrittsjahr:</label>
          <input type="number" name="jahr" class="form-control" min="1900" max="2100" required>
        </div>

        <button type="submit" class="btn btn-primary">absenden</button>
      </form>
    </div>

    <!-- FOOTER -->
    <footer class="pt-3 mt-4 text-muted border-top">
      &copy; 2021 Mustermann GmbH - Demo (Web-Technologien)
    </footer>

  </div>
</main>

<script src="bootstrap5.0.1/js/bootstrap.bundle.min.js"></script>    
</body>
</html>
