const fs = require('fs');

// Datei lesen
fs.readFile('log.txt', 'utf8', (err, data) => {
    if (err) {
        console.error('Fehler beim Lesen der Datei:', err);
        return;
    }

    // Dateiinhalt in Zeilen aufteilen
    const lines = data.split('\n');
    let sum = 0;
    let count = 0;

    // Durch jede Zeile iterieren
    lines.forEach(line => {
        const num = Number(line.trim());
        if (!isNaN(num) && num !== 42) {
            sum += num;
            count++;
        }
    });

    // Mittelwert berechnen
    const average = count > 0 ? sum / count : 0;
    console.log('Mittelwert:', average);
});