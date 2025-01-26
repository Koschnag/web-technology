<?php
/*****************************************************
 * team.php
 * (1) Stellt die DB-Verbindung her.
 * (2) Enthält Interface, Repository, ViewModel.
 * (3) Ruft die Daten ab und gibt sie im HTML aus.
 *****************************************************/

/* --- 1) DB-VERBINDUNG --- */
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "deine_datenbank"; // Bitte anpassen!

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_error) {
    die("Datenbank-Verbindungsfehler: " . $mysqli->connect_error);
}

/* --- 2) INTERFACE & KLASSEN (DIP, MVVM) --- */

// Data Access Layer (Model-Interface):
interface ITeamRepository
{
    public function getCount(): int;
    public function getAllAlphabetical(): array;
    public function getAllBeforeYear(int $year): array;
    public function getAllSortedByYearDesc(): array;
    public function getAllSortedByYearAsc(): array;
}

// Konkrete Implementierung des Repositories (DB-Zugriff):
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
        $sql = "SELECT id, nachname, vorname, eintrittsjahr FROM team
                ORDER BY nachname, vorname";
        $result = $this->conn->query($sql);
        if (!$result) {
            die("Fehler in getAllAlphabetical(): " . $this->conn->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllBeforeYear(int $year): array
    {
        // Beispiel mit Prepared Statement:
        $stmt = $this->conn->prepare(
            "SELECT id, nachname, vorname, eintrittsjahr 
             FROM team 
             WHERE eintrittsjahr < ?
             ORDER BY eintrittsjahr ASC"
        );
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

// ViewModel („Geschäftslogik“ / Datenaufbereitung für die Ausgabe):
class TeamViewModel
{
    private ITeamRepository $repo;

    // Konstruktor-Injection (Poor Man’s DI):
    public function __construct(ITeamRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getCountString(): string
    {
        $count = $this->repo->getCount();
        return "Anzahl Team-Mitglieder: $count";
    }

    // Alphabetisch sortiert
    public function getAlphabetical(): array
    {
        return $this->repo->getAllAlphabetical();
    }

    // Langjährige: Eintritt vor 2018
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

/* --- 3) COMPOSITION ROOT (Erzeugung + HTML-Ausgabe) --- */

// Repository und ViewModel instanzieren
$repo = new TeamRepository($mysqli);
$vm   = new TeamViewModel($repo);

// HTML-Ausgabe
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Team Übersicht</title>
</head>
<body>
    <h1>Team-Übersicht</h1>

    <!-- (A) Anzahl der Mitarbeiter -->
    <section>
        <h2>Anzahl Teammitglieder</h2>
        <p><?= htmlspecialchars($vm->getCountString()); ?></p>
    </section>

    <!-- (B) Alphabetisch nach Nachname, Vorname -->
    <section>
        <h2>Alphabetische Liste</h2>
        <ul>
            <?php foreach ($vm->getAlphabetical() as $person): ?>
                <li>
                    <?= htmlspecialchars($person['nachname']) ?>,
                    <?= htmlspecialchars($person['vorname']) ?>
                    (<?= (int)$person['eintrittsjahr'] ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- (C) Nur langjährige Teammitglieder (Eintritt < 2018) -->
    <section>
        <h2>Langjährige Mitglieder (vor 2018 eingetreten)</h2>
        <ul>
            <?php foreach ($vm->getLongTermMembers() as $person): ?>
                <li>
                    <?= htmlspecialchars($person['nachname']) ?>,
                    <?= htmlspecialchars($person['vorname']) ?>
                    (<?= (int)$person['eintrittsjahr'] ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- (D) Nach Eintrittsjahr absteigend -->
    <section>
        <h2>Sortiert nach Eintrittsjahr (absteigend)</h2>
        <ul>
            <?php foreach ($vm->getByYearDescending() as $person): ?>
                <li>
                    <?= htmlspecialchars($person['nachname']) ?>,
                    <?= htmlspecialchars($person['vorname']) ?>
                    (<?= (int)$person['eintrittsjahr'] ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- (E) Nach Eintrittsjahr aufsteigend -->
    <section>
        <h2>Sortiert nach Eintrittsjahr (aufsteigend)</h2>
        <ul>
            <?php foreach ($vm->getByYearAscending() as $person): ?>
                <li>
                    <?= htmlspecialchars($person['nachname']) ?>,
                    <?= htmlspecialchars($person['vorname']) ?>
                    (<?= (int)$person['eintrittsjahr'] ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- (F) Nur Vor- und Nachnamen -->
    <section>
        <h2>Nur Vor- und Nachnamen</h2>
        <ul>
            <?php foreach ($vm->getAlphabetical() as $person): ?>
                <li>
                    <?= htmlspecialchars($person['vorname']) ?>
                    <?= htmlspecialchars($person['nachname']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

</body>
</html>
