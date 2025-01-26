<?php
/***********************************************************
 * team.php
 * 
 * EIN SKRIPT MIT ZWEI REPOSITORYS:
 * 1) TeamRepository (echte DB)
 * 2) TeamMockRepository (5 Dummy-Einträge)
 *
 * MVVM, DIP, IoC, "Poor Man's DI"
 ************************************************************/

/* --- 1) OPTIONALE DB-VERBINDUNG --- */
$dbHost = "localhost";       // Anpassen (falls DB genutzt wird)
$dbUser = "root";            // Anpassen
$dbPass = "";                // Anpassen
$dbName = "deine_datenbank"; // Anpassen

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_error) {
    die("Datenbank-Verbindungsfehler: " . $mysqli->connect_error);
}

/* --- 2) FUNKTION: berechneMonate($jahr) ---
   Berechnet die Monate seit dem 1. Januar des Eintrittsjahres
   bis zum aktuellen Monat, um daraus Jahre abzuleiten. */
function berechneMonate(int $jahr): int
{
    $aktuellesJahr  = (int)date('Y');
    $aktuellerMonat = (int)date('n'); // Monat (1..12)

    $jahresDiff = $aktuellesJahr - $jahr;
    $monate = ($jahresDiff * 12) + ($aktuellerMonat - 1);

    // Falls Eintrittsjahr in der Zukunft, setze auf 0
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

/* ***********************************************************************
 * 4) KONKRETE IMPLEMENTIERUNGEN DIESES INTERFACES
 *    -> a) TeamRepository (echte DB)
 *    -> b) TeamMockRepository (dummy-Daten)
 *********************************************************************** */

/* --- 4a) TeamRepository (ECHTE DATENBANK) --- */
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

/* --- 4b) TeamMockRepository (DUMMY / MOCK) --- */
class TeamMockRepository implements ITeamRepository
{
    // Wir definieren 5 fiktive Datensätze
    private array $mockData = [
        // (id, nachname, vorname, eintrittsjahr)
        [ 'id'=>1, 'nachname'=>'Mueller',  'vorname'=>'Anna',   'eintrittsjahr'=>2010 ],
        [ 'id'=>2, 'nachname'=>'Schmidt',  'vorname'=>'Markus', 'eintrittsjahr'=>2021 ],
        [ 'id'=>3, 'nachname'=>'Weber',    'vorname'=>'Petra',  'eintrittsjahr'=>2015 ],
        [ 'id'=>4, 'nachname'=>'Mayer',    'vorname'=>'Martin', 'eintrittsjahr'=>2008 ],
        [ 'id'=>5, 'nachname'=>'Paulus',   'vorname'=>'Paul',   'eintrittsjahr'=>2000 ],
    ];

    public function getCount(): int
    {
        return count($this->mockData);
    }

    // Alphabetisch sortieren (Nachname, Vorname)
    public function getAllAlphabetical(): array
    {
        $data = $this->mockData;
        usort($data, function($a, $b){
            $cmp1 = strcmp($a['nachname'], $b['nachname']);
            if ($cmp1 !== 0) return $cmp1;
            return strcmp($a['vorname'], $b['vorname']);
        });
        return $data;
    }

    // Alle, die vor $year eingetreten sind
    public function getAllBeforeYear(int $year): array
    {
        $data = array_filter($this->mockData, function($row) use ($year) {
            return $row['eintrittsjahr'] < $year;
        });
        // Sortierung optional: nach eintrittsjahr ASC
        usort($data, function($a, $b){
            return $a['eintrittsjahr'] <=> $b['eintrittsjahr'];
        });
        return $data;
    }

    // Nach Eintrittsjahr DESC
    public function getAllSortedByYearDesc(): array
    {
        $data = $this->mockData;
        usort($data, function($a, $b){
            return $b['eintrittsjahr'] <=> $a['eintrittsjahr'];
        });
        return $data;
    }

    // Nach Eintrittsjahr ASC
    public function getAllSortedByYearAsc(): array
    {
        $data = $this->mockData;
        usort($data, function($a, $b){
            return $a['eintrittsjahr'] <=> $b['eintrittsjahr'];
        });
        return $data;
    }
}

/* --- 5) ViewModel --- */
class TeamViewModel
{
    private ITeamRepository $repo;

    public function __construct(ITeamRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getCountString(): string
    {
        $cnt = $this->repo->getCount();
        return "Anzahl Team-Mitglieder: $cnt";
    }

    public function getAlphabetical(): array
    {
        return $this->repo->getAllAlphabetical();
    }

    public function getLongTermMembers(): array
    {
        return $this->repo->getAllBeforeYear(2018);
    }

    public function getByYearDescending(): array
    {
        return $this->repo->getAllSortedByYearDesc();
    }

    public function getByYearAscending(): array
    {
        return $this->repo->getAllSortedByYearAsc();
    }
}

/* --- 6) Composition Root: Wähle DB oder Mock, erstelle VM, HTML-Ausgabe --- */

// a) Schalter: TRUE => MOCK-Daten, FALSE => echte DB
$useMock = false; // <--- HIER ändern: true = 5 Dummys, false = DB

// b) Repository erstellen
if ($useMock) {
    $repo = new TeamMockRepository();
} else {
    $repo = new TeamRepository($mysqli);
}

// c) ViewModel
$vm = new TeamViewModel($repo);

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Team-Übersicht (MVVM)</title>
</head>
<body>
    <h1>Support-Team-Liste</h1>

    <!-- Info: nutzen wir Mock oder DB? -->
    <p>
        <strong>Modus:</strong> 
        <?php if ($useMock): ?>
            MOCK (5 Dummy-Einträge)
        <?php else: ?>
            Echte DB-Verbindung
        <?php endif; ?>
    </p>

    <!-- A) Anzahl der Mitarbeiter -->
    <h2>Anzahl der Mitarbeiter</h2>
    <p><?= htmlspecialchars($vm->getCountString()); ?></p>

    <!-- B) Alphabetisch (Nachname, Vorname) -->
    <h2>Alphabetische Liste</h2>
    <ul>
        <?php foreach ($vm->getAlphabetical() as $person): ?>
            <?php
                $jahr   = (int)$person['eintrittsjahr'];
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

    <!-- C) Nur langjährige (Eintritt vor 2018) -->
    <h2>Langjährige Mitglieder (vor 2018)</h2>
    <ul>
        <?php foreach ($vm->getLongTermMembers() as $person): ?>
            <?php
                $jahr   = (int)$person['eintrittsjahr'];
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

    <!-- D) Nach Eintrittsjahr absteigend -->
    <h2>Nach Eintrittsjahr (absteigend)</h2>
    <ul>
        <?php foreach ($vm->getByYearDescending() as $person): ?>
            <?php
                $jahr   = (int)$person['eintrittsjahr'];
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

    <!-- E) Nach Eintrittsjahr aufsteigend -->
    <h2>Nach Eintrittsjahr (aufsteigend)</h2>
    <ul>
        <?php foreach ($vm->getByYearAscending() as $person): ?>
            <?php
                $jahr   = (int)$person['eintrittsjahr'];
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
                $jahr   = (int)$person['eintrittsjahr'];
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
