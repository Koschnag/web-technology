function highlightPrice() {
    document.getElementById('preis').style.color = 'red';
    document.getElementById('preis').style.fontSize = 'larger';
    document.getElementById('preisbereich').childNodes[0].classList.add('gruen');
}

function togglePrice() {
    const preisElement = document.getElementById('preis');
    const preisbereich = document.getElementById('preisbereich');
    const button = preisbereich.querySelector('button');
    const mwstText = preisbereich.childNodes[2];

    if (button.innerText === 'zeige Nettopreis') {
        const bruttoPreis = parseFloat(preisElement.innerText);
        const nettoPreis = (bruttoPreis / 1.19).toFixed(2);
        preisElement.innerText = nettoPreis;
        preisElement.classList.add('nettopreis');
        mwstText.nodeValue = ' Euro zzgl. 19% MwSt. ';
        button.innerText = 'zeige Bruttopreis';
    } else {
        preisElement.innerText = '999';
        preisElement.classList.remove('nettopreis');
        mwstText.nodeValue = ' Euro inkl. 19% MwSt. ';
        button.innerText = 'zeige Nettopreis';
    }
}

function showBack() {
    document.getElementById('produktbild').src = 'img/notstromaggregat-rueckseite.jpg';
}

function showFront() {
    document.getElementById('produktbild').src = 'img/notstromaggregat.jpg';
}