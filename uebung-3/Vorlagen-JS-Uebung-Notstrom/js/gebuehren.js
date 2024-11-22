function berechnePreis(preisProPerson) {
    const mwst = 0.19;
    const bruttoPreis = preisProPerson * (1 + mwst);
    alert("Der Bruttopreis für eine Person beträgt: " + bruttoPreis.toFixed(2) + " Euro inkl. MwSt.");

    let teilnehmer = prompt("Bitte geben Sie die Anzahl der Teilnehmer ein:");
    teilnehmer = parseInt(teilnehmer);
    if (isNaN(teilnehmer) || teilnehmer <= 0) {
        alert("Ungültige Anzahl von Teilnehmern.");
        return;
    }

    let gesamtPreis = 0;
    for (let i = 1; i <= teilnehmer; i++) {
        if (i >= 8) {
            gesamtPreis += bruttoPreis * 0.4;
        } else if (i >= 5) {
            gesamtPreis += bruttoPreis * 0.5;
        } else if (i >= 3) {
            gesamtPreis += bruttoPreis * 0.7;
        } else {
            gesamtPreis += bruttoPreis;
        }
    }

    alert("Der Gesamtpreis beträgt: " + gesamtPreis.toFixed(2) + " Euro");

    let nachnamen = [];
    for (let i = 1; i <= teilnehmer; i++) {
        let nachname = prompt("Bitte geben Sie den Nachnamen des Teilnehmers " + i + " ein:");
        nachnamen.push(nachname);
    }

    console.log("Nachnamen der Teilnehmer:", nachnamen);

    let rechnungsadresse = {
        firmenname: prompt("Bitte geben Sie den Firmennamen ein:"),
        strasse: prompt("Bitte geben Sie die Straße ein:"),
        plz: prompt("Bitte geben Sie die PLZ ein:"),
        ort: prompt("Bitte geben Sie den Ort ein:")
    };

    console.log("Rechnungsadresse:", rechnungsadresse);

    let heute = new Date();
    let inDreiTagen = new Date();
    inDreiTagen.setDate(heute.getDate() + 3);

    let options = { year: 'numeric', month: 'numeric', day: 'numeric' };
    let heutigesDatum = heute.toLocaleDateString('de-DE', options);
    let datumInDreiTagen = inDreiTagen.toLocaleDateString('de-DE', options);

    alert(`Vielen Dank für Ihre Bestellung am heutigen ${heutigesDatum}. Die Buchungsbestätigung erhalten Sie spätestens in drei Tagen, d. h. am ${datumInDreiTagen}.`);
}