// Business Logic (Model)
class PriceModel {
    constructor(price) {
        this.price = price;
    }

    getFormattedPrice() {
        return `nur ${this.price} Euro`;
    }

    getNetPrice() {
        return (this.price / 1.19).toFixed(2);
    }
}

// Business Logic (ViewModel)
class PriceViewModel {
    constructor(priceModel) {
        this.priceModel = priceModel;
    }

    highlightPrice() {
        const priceText = this.priceModel.getFormattedPrice();
        const highlightedText = priceText.replace('nur', '<span class="green">nur</span>');
        return `<span class="highlight">${highlightedText}</span>`;
    }

    getNetPriceText() {
        const netPrice = this.priceModel.getNetPrice();
        return `<em class="grey">nur</em> <strong class="grey">${netPrice}</strong> Euro`;
    }

    getGrossPriceText() {
        return `<em>nur</em> <strong>999</strong> Euro`;
    }
}

// UI Logic (Controller)
class PriceController {
    constructor(viewModel) {
        this.viewModel = viewModel;
        this.isNetPrice = false;
        this.isHighlighted = false;
        this.isCircleVisible = false;
    }

    init() {
        document.getElementById('sonderpreis-link').addEventListener('click', (event) => {
            event.preventDefault();
            this.highlightPrice();
        });

        document.getElementById('toggle-price').addEventListener('click', (event) => {
            this.togglePrice(event);
        });

        const produktbild = document.getElementById('produktbild');
        produktbild.addEventListener('mouseover', () => {
            produktbild.src = 'img/notstromaggregat-rueckseite.jpg';
            this.hideCircle();
        });
        produktbild.addEventListener('mouseout', () => {
            produktbild.src = 'img/notstromaggregat.jpg';
        });

        document.getElementById('lupe').addEventListener('click', () => {
            this.toggleCircle();
        });

        // Show live chat window after 10 seconds
        setTimeout(() => {
            this.showLiveChatWindow();
        }, 10000);
    }

    highlightPrice() {
        this.isHighlighted = true;
        this.isNetPrice = false; // Reset to gross price when highlighting
        this.updatePriceDisplay();
    }

    togglePrice(event) {
        this.isNetPrice = !this.isNetPrice;
        this.isHighlighted = false; // Reset highlighting when toggling price
        this.updatePriceDisplay();
    }

    updatePriceDisplay() {
        const preisElement = document.getElementById('preis');
        const mwstElement = document.getElementById('mwst');
        const toggleButton = document.getElementById('toggle-price');

        if (this.isNetPrice) {
            preisElement.innerHTML = this.viewModel.getNetPriceText();
            mwstElement.innerText = 'zzgl. 19% MwSt.';
            toggleButton.innerText = 'zeige Bruttopreis';
        } else {
            if (this.isHighlighted) {
                preisElement.innerHTML = this.viewModel.highlightPrice();
            } else {
                preisElement.innerHTML = this.viewModel.getGrossPriceText();
            }
            mwstElement.innerText = 'inkl. 19% MwSt.';
            toggleButton.innerText = 'zeige Nettopreis';
        }
    }

    toggleCircle() {
        const circle = document.getElementById('kreis');
        const lupe = document.getElementById('lupe');
        this.isCircleVisible = !this.isCircleVisible;
        if (this.isCircleVisible) {
            circle.style.display = 'block';
            lupe.style.opacity = '0.3';
        } else {
            circle.style.display = 'none';
            lupe.style.opacity = '1';
        }
    }

    hideCircle() {
        const circle = document.getElementById('kreis');
        const lupe = document.getElementById('lupe');
        this.isCircleVisible = false;
        circle.style.display = 'none';
        lupe.style.opacity = '1';
    }

    showLiveChatWindow() {
        const liveChatWindow = document.getElementById('live-chat-window');
        liveChatWindow.style.display = 'block';

        document.getElementById('close-chat').addEventListener('click', () => {
            liveChatWindow.style.display = 'none';
        });

        document.getElementById('no-thanks').addEventListener('click', () => {
            liveChatWindow.style.display = 'none';
        });

        document.getElementById('yes-please').addEventListener('click', () => {
            liveChatWindow.style.display = 'none';
            // Add logic to start the live chat
        });
    }
}

// Dependency Injection and Initialization
document.addEventListener('DOMContentLoaded', () => {
    const priceModel = new PriceModel(999);
    const priceViewModel = new PriceViewModel(priceModel);
    const priceController = new PriceController(priceViewModel);
    priceController.init();
});