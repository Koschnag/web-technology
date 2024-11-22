// Model
const gebuehrenModel = {
    berechneBruttoPreis(preisProPerson, mwst) {
        return preisProPerson * (1 + mwst);
    },
    berechneGesamtPreis(teilnehmer, bruttoPreis, rabatte) {
        let gesamtPreis = 0;
        for (let i = 1; i <= teilnehmer; i++) {
            if (i >= rabatte.abAchtPersonen) {
                gesamtPreis += bruttoPreis * rabatte.abAchtPersonenRabatt;
            } else if (i >= rabatte.abFuenfPersonen) {
                gesamtPreis += bruttoPreis * rabatte.abFuenfPersonenRabatt;
            } else if (i >= rabatte.abDreiPersonen) {
                gesamtPreis += bruttoPreis * rabatte.abDreiPersonenRabatt;
            } else {
                gesamtPreis += bruttoPreis;
            }
        }
        return gesamtPreis;
    },
    frageNachNamen(teilnehmer, promptFn) {
        let nachnamen = [];
        for (let i = 1; i <= teilnehmer; i++) {
            let nachname = promptFn(`Bitte geben Sie den Nachnamen des Teilnehmers ${i} ein:`);
            nachnamen.push(nachname);
        }
        return nachnamen;
    },
    frageNachRechnungsadresse(promptFn) {
        return {
            firmenname: promptFn("Bitte geben Sie den Firmennamen ein:"),
            strasse: promptFn("Bitte geben Sie die Straße ein:"),
            plz: promptFn("Bitte geben Sie die PLZ ein:"),
            ort: promptFn("Bitte geben Sie den Ort ein:")
        };
    },
    berechneDatumInDreiTagen(dateFn) {
        let heute = dateFn();
        let inDreiTagen = dateFn();
        inDreiTagen.setDate(heute.getDate() + 3);
        return inDreiTagen;
    },
    formatDatum(datum, locale) {
        let options = { year: 'numeric', month: 'numeric', day: 'numeric' };
        return datum.toLocaleDateString(locale, options);
    }
};

// ViewModel
const viewModel = {
    berechnePreis(preisProPerson) {
        const mwst = 0.19;
        const rabatte = {
            abDreiPersonen: 3,
            abDreiPersonenRabatt: 0.7,
            abFuenfPersonen: 5,
            abFuenfPersonenRabatt: 0.5,
            abAchtPersonen: 8,
            abAchtPersonenRabatt: 0.4
        };
        const locale = 'de-DE';
        const promptFn = prompt;
        const dateFn = () => new Date();

        const bruttoPreis = gebuehrenModel.berechneBruttoPreis(preisProPerson, mwst);
        alert(`Der Bruttopreis für eine Person beträgt: ${bruttoPreis.toFixed(2)} Euro inkl. MwSt.`);

        let teilnehmer = parseInt(promptFn("Bitte geben Sie die Anzahl der Teilnehmer ein:"));
        if (isNaN(teilnehmer) || teilnehmer <= 0) {
            alert("Ungültige Anzahl von Teilnehmern.");
            return;
        }

        const gesamtPreis = gebuehrenModel.berechneGesamtPreis(teilnehmer, bruttoPreis, rabatte);
        alert(`Der Gesamtpreis beträgt: ${gesamtPreis.toFixed(2)} Euro`);

        const nachnamen = gebuehrenModel.frageNachNamen(teilnehmer, promptFn);
        console.log("Nachnamen der Teilnehmer:", nachnamen);

        const rechnungsadresse = gebuehrenModel.frageNachRechnungsadresse(promptFn);
        console.log("Rechnungsadresse:", rechnungsadresse);

        const heutigesDatum = gebuehrenModel.formatDatum(dateFn(), locale);
        const datumInDreiTagen = gebuehrenModel.formatDatum(gebuehrenModel.berechneDatumInDreiTagen(dateFn), locale);

        alert(`Vielen Dank für Ihre Bestellung am heutigen ${heutigesDatum}. Die Buchungsbestätigung erhalten Sie spätestens in drei Tagen, d. h. am ${datumInDreiTagen}.`);
    }
};