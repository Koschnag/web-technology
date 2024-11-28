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
        });
        produktbild.addEventListener('mouseout', () => {
            produktbild.src = 'img/notstromaggregat.jpg';
        });
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
}

// Dependency Injection and Initialization
document.addEventListener('DOMContentLoaded', () => {
    const priceModel = new PriceModel(999);
    const priceViewModel = new PriceViewModel(priceModel);
    const priceController = new PriceController(priceViewModel);
    priceController.init();
});