function berechneBruttoPreis(preisProPerson, mwst) {
    return preisProPerson * (1 + mwst);
}

function berechneGesamtPreis(teilnehmer, bruttoPreis) {
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
    return gesamtPreis;
}

function frageNachNamen(teilnehmer) {
    let nachnamen = [];
    for (let i = 1; i <= teilnehmer; i++) {
        let nachname = prompt(`Bitte geben Sie den Nachnamen des Teilnehmers ${i} ein:`);
        nachnamen.push(nachname);
    }
    return nachnamen;
}

function frageNachRechnungsadresse() {
    return {
        firmenname: prompt("Bitte geben Sie den Firmennamen ein:"),
        strasse: prompt("Bitte geben Sie die Straße ein:"),
        plz: prompt("Bitte geben Sie die PLZ ein:"),
        ort: prompt("Bitte geben Sie den Ort ein:")
    };
}

function berechneDatumInDreiTagen() {
    let heute = new Date();
    let inDreiTagen = new Date();
    inDreiTagen.setDate(heute.getDate() + 3);
    return inDreiTagen;
}

function formatDatum(datum) {
    let options = { year: 'numeric', month: 'numeric', day: 'numeric' };
    return datum.toLocaleDateString('de-DE', options);
}

function berechnePreis(preisProPerson) {
    const mwst = 0.19;
    const bruttoPreis = berechneBruttoPreis(preisProPerson, mwst);
    alert(`Der Bruttopreis für eine Person beträgt: ${bruttoPreis.toFixed(2)} Euro inkl. MwSt.`);

    let teilnehmer = parseInt(prompt("Bitte geben Sie die Anzahl der Teilnehmer ein:"));
    if (isNaN(teilnehmer) || teilnehmer <= 0) {
        alert("Ungültige Anzahl von Teilnehmern.");
        return;
    }

    const gesamtPreis = berechneGesamtPreis(teilnehmer, bruttoPreis);
    alert(`Der Gesamtpreis beträgt: ${gesamtPreis.toFixed(2)} Euro`);

    const nachnamen = frageNachNamen(teilnehmer);
    console.log("Nachnamen der Teilnehmer:", nachnamen);

    const rechnungsadresse = frageNachRechnungsadresse();
    console.log("Rechnungsadresse:", rechnungsadresse);

    const heutigesDatum = formatDatum(new Date());
    const datumInDreiTagen = formatDatum(berechneDatumInDreiTagen());

    alert(`Vielen Dank für Ihre Bestellung am heutigen ${heutigesDatum}. Die Buchungsbestätigung erhalten Sie spätestens in drei Tagen, d. h. am ${datumInDreiTagen}.`);
}