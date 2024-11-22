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
    const kreis = document.getElementById('kreis');
    if (produktbild.src.includes('notstromaggregat.jpg')) {
        produktbild.src = 'img/notstromaggregat-rueckseite.jpg';
        kreis.style.display = 'none';
    } else {
        produktbild.src = 'img/notstromaggregat.jpg';
    }
}

function verschiebeKreis(x, y, lupeId) {
    const kreis = document.getElementById('kreis');
    const lupe = document.getElementById(lupeId);
    if (kreis.style.display === 'none' || kreis.style.left !== `${x}px` || kreis.style.top !== `${y}px`) {
        kreis.style.left = `${x}px`;
        kreis.style.top = `${y}px`;
        kreis.style.display = 'block';
        lupe.style.opacity = 0.3;
    } else {
        kreis.style.display = 'none';
        lupe.style.opacity = 1;
    }
}

function animateFeatures() {
    const features = [
        { x: 200, y: 50, text: 'zwei Steckdosen' },
        { x: 300, y: 150, text: 'Ein-/Ausschalter' },
        { x: 400, y: 250, text: 'Dieselmotor 5.5e' }
    ];
    let index = 0;
    const interval = setInterval(() => {
        if (index < features.length) {
            verschiebeKreis(features[index].x, features[index].y, `lupe${index + 1}`);
            document.querySelector(`strong:contains(${features[index].text})`).style.color = 'red';
            index++;
        } else {
            clearInterval(interval);
        }
    }, 2000);
}

function showLiveChat() {
    const liveChat = document.getElementById('live-chat');
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    liveChat.style.left = `${(viewportWidth - liveChat.offsetWidth) / 2}px`;
    liveChat.style.top = `${(viewportHeight - liveChat.offsetHeight) / 2}px`;
    liveChat.style.display = 'block';
}

function closeLiveChat() {
    document.getElementById('live-chat').style.display = 'none';
}

function init() {
    document.getElementById('sonderpreis-link').addEventListener('click', highlightPrice);
    document.getElementById('toggle-price-button').addEventListener('click', togglePrice);
    const produktbild = document.getElementById('produktbild');
    produktbild.addEventListener('click', () => {
        toggleImage();
        alert('Klicken Sie erneut auf das Bild, um die Ansicht zu wechseln');
    });
    document.getElementById('lupe1').addEventListener('click', () => verschiebeKreis(200, 50, 'lupe1'));
    document.getElementById('lupe2').addEventListener('click', () => verschiebeKreis(300, 150, 'lupe2'));
    document.getElementById('lupe3').addEventListener('click', () => verschiebeKreis(400, 250, 'lupe3'));
    document.getElementById('animation-link').addEventListener('click', animateFeatures);
    document.getElementById('close-chat').addEventListener('click', closeLiveChat);
    document.getElementById('no-thanks').addEventListener('click', closeLiveChat);

    setTimeout(showLiveChat, 10000);
}

document.addEventListener('DOMContentLoaded', init);