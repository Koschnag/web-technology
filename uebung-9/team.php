<?php
/***********************************************************
 * team.php
 * 
 * Ein einziges Skript:
 * 1) DB-Verbindung
 * 2) Funktion berechneMonate($jahr)
 * 3) Interface ITeamRepository
 * 4) TeamRepository (Implementierung)
 * 5) TeamViewModel
 * 6) "Composition Root" + HTML-Ausgabe
 * 
 * MVVM, DIP, IoC, "Poor Man's DI"
 ************************************************************/

/* --- 1) DB-VERBINDUNG --- */
$dbHost = "localhost";      // <--- Anpassen
$dbUser = "root";           // <--- Anpassen
$dbPass = "";               // <--- Anpassen
$dbName = "deine_datenbank"; // <--- Anpassen

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_error) {
    die("Datenbank-Verbindungsfehler: " . $mysqli->connect_error);
}

/* --- 2) FUNKTION: berechneMonate($jahr) ---
   Berechnet die seit dem 1. Januar des Eintrittsjahres
   bis zum aktuellen Monat vergangenen Monate */
function berechneMonate(int $jahr): int
{
    $aktuellesJahr  = (int)date('Y');
    $aktuellerMonat = (int)date('n'); // Monat 1..12

    // Jahresdifferenz
    $jahresDiff = $aktuellesJahr - $jahr;

    // Monate = "volle Jahre * 12" + "aktuellerMonat - 1"
    // (Weil Januar = Monat 1 => 0 Monate Differenz)
    $monate = ($jahresDiff * 12) + ($aktuellerMonat - 1);

    // Optional: Falls Eintrittsjahr in der Zukunft liegt,
    // könnte $monate negativ sein. Hier absichern:
    if ($monate < 0) {
        $monate = 0;
    }

    return $monate;
}

/* --- 3) INTERFACE: ITeamRepository (DIP) --- */
interface ITeamRepository
{
    public function getCount(): int;
    public function getAllAlphabetical(): array;
    public function getAllBeforeYear(int $year): array;
    public function getAllSortedByYearDesc(): array;
    public function getAllSortedByYearAsc(): array;
}

/* --- 4) TeamRepository (Implementierung des Interfaces) --- */
class TeamRepository implements ITeamRepository
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getCount(): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM team";
        $result = $this->conn->query($sql);
        if (!$result) {
            die("Fehler in getCount(): " . $this->conn->error);
        }
        $row = $result->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    public function getAllAlphabetical(): array
    {
        $sql = "SELECT id, nachname, vorname, eintrittsjahr
                FROM team
                ORDER BY nachname, vorname";
        $result = $this->conn->query($sql);
        if (!$result) {
            die("Fehler in getAllAlphabetical(): " . $this->conn->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllBeforeYear(int $year): array
    {
        // Beispiel für Prepared Statements (Sicherheit)
        $stmt = $this->conn->prepare("
            SELECT id, nachname, vorname, eintrittsjahr
            FROM team
            WHERE eintrittsjahr < ?
            ORDER BY eintrittsjahr ASC
        ");
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllSortedByYearDesc(): array
    {
        $sql = "SELECT id, nachname, vorname, eintrittsjahr
                FROM team
                ORDER BY eintrittsjahr DESC";
        $result = $this->conn->query($sql);
        if (!$result) {
            die("Fehler in getAllSortedByYearDesc(): " . $this->conn->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllSortedByYearAsc(): array
    {
        $sql = "SELECT id, nachname, vorname, eintrittsjahr
                FROM team
                ORDER BY eintrittsjahr ASC";
        $result = $this->conn->query($sql);
        if (!$result) {
            die("Fehler in getAllSortedByYearAsc(): " . $this->conn->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

/* --- 5) ViewModel (MVVM-Logik, nutzt Repository) --- */
class TeamViewModel
{
    private ITeamRepository $repo;

    // Konstruktor-Injection: "Poor Man's DI"
    public function __construct(ITeamRepository $repo)
    {
        $this->repo = $repo;
    }

    // Anzahl Teammitglieder (String)
    public function getCountString(): string
    {
        $count = $this->repo->getCount();
        return "Anzahl Team-Mitglieder: $count";
    }

    // Alphabetisch sortierte Gesamtliste
    public function getAlphabetical(): array
    {
        return $this->repo->getAllAlphabetical();
    }

    // Nur Mitglieder, die vor 2018 eingetreten sind
    public function getLongTermMembers(): array
    {
        return $this->repo->getAllBeforeYear(2018);
    }

    // Nach Eintrittsjahr absteigend
    public function getByYearDescending(): array
    {
        return $this->repo->getAllSortedByYearDesc();
    }

    // Nach Eintrittsjahr aufsteigend
    public function getByYearAscending(): array
    {
        return $this->repo->getAllSortedByYearAsc();
    }
}

/* --- 6) "Composition Root": Objekt-Erzeugung + HTML-Ausgabe --- */

// a) Repository und ViewModel instanzieren
$repo = new TeamRepository($mysqli);
$vm   = new TeamViewModel($repo);

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Team-Übersicht (MVVM)</title>
</head>
<body>
    <h1>Support-Team-Liste</h1>

    <!-- A) Anzahl der Mitarbeiter -->
    <h2>Anzahl der Mitarbeiter</h2>
    <p><?= htmlspecialchars($vm->getCountString()); ?></p>

    <!-- B) Alphabetisch (Nachname, Vorname) -->
    <h2>Alphabetische Liste</h2>
    <ul>
        <?php foreach ($vm->getAlphabetical() as $person): ?>
            <?php
                // Eintrittsjahr
                $jahr = (int)$person['eintrittsjahr'];
                // Berechne Monate, um Jahre zu erhalten
                $monate = berechneMonate($jahr);
                $jahre  = floor($monate / 12);
            ?>
            <li>
                <?= htmlspecialchars($person['nachname']) ?>,
                <?= htmlspecialchars($person['vorname']) ?> 
                (<?= $jahr ?>) –
                (seit <?= $jahre ?> Jahren im Unternehmen)
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- C) Nur langjährige Teammitglieder (Eintritt vor 2018) -->
    <h2>Langjährige Mitglieder (vor 2018)</h2>
    <ul>
        <?php foreach ($vm->getLongTermMembers() as $person): ?>
            <?php
                $jahr = (int)$person['eintrittsjahr'];
                $monate = berechneMonate($jahr);
                $jahre  = floor($monate / 12);
            ?>
            <li>
                <?= htmlspecialchars($person['nachname']) ?>,
                <?= htmlspecialchars($person['vorname']) ?>
                (<?= $jahr ?>) –
                (seit <?= $jahre ?> Jahren im Unternehmen)
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- D) Sortiert nach Eintrittsjahr (absteigend) -->
    <h2>Nach Eintrittsjahr absteigend</h2>
    <ul>
        <?php foreach ($vm->getByYearDescending() as $person): ?>
            <?php
                $jahr = (int)$person['eintrittsjahr'];
                $monate = berechneMonate($jahr);
                $jahre  = floor($monate / 12);
            ?>
            <li>
                <?= htmlspecialchars($person['nachname']) ?>,
                <?= htmlspecialchars($person['vorname']) ?>
                (<?= $jahr ?>) –
                (seit <?= $jahre ?> Jahren im Unternehmen)
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- E) Sortiert nach Eintrittsjahr (aufsteigend) -->
    <h2>Nach Eintrittsjahr aufsteigend</h2>
    <ul>
        <?php foreach ($vm->getByYearAscending() as $person): ?>
            <?php
                $jahr = (int)$person['eintrittsjahr'];
                $monate = berechneMonate($jahr);
                $jahre  = floor($monate / 12);
            ?>
            <li>
                <?= htmlspecialchars($person['nachname']) ?>,
                <?= htmlspecialchars($person['vorname']) ?>
                (<?= $jahr ?>) –
                (seit <?= $jahre ?> Jahren im Unternehmen)
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- F) Nur Vor- und Nachnamen -->
    <h2>Nur Vor- und Nachnamen</h2>
    <ul>
        <?php foreach ($vm->getAlphabetical() as $person): ?>
            <?php
                $jahr = (int)$person['eintrittsjahr'];
                $monate = berechneMonate($jahr);
                $jahre  = floor($monate / 12);
            ?>
            <li>
                <?= htmlspecialchars($person['vorname']) ?>
                <?= htmlspecialchars($person['nachname']) ?>
                (seit <?= $jahre ?> Jahren im Unternehmen)
            </li>
        <?php endforeach; ?>
    </ul>

</body>
</html>
