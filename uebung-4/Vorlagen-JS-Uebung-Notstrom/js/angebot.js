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

function toggleImage() {
    const produktbild = document.getElementById('produktbild');
    if (produktbild.src.includes('notstromaggregat.jpg')) {
        produktbild.src = 'img/notstromaggregat-rueckseite.jpg';
    } else {
        produktbild.src = 'img/notstromaggregat.jpg';
    }
}

function init() {
    document.getElementById('sonderpreis-link').addEventListener('click', highlightPrice);
    document.getElementById('toggle-price-button').addEventListener('click', togglePrice);
    const produktbild = document.getElementById('produktbild');
    produktbild.addEventListener('click', () => {
        toggleImage();
        alert('Klicken Sie erneut auf das Bild, um die Ansicht zu wechseln');
    });
}

document.addEventListener('DOMContentLoaded', init);