document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!form.checkValidity()) {
                e.preventDefault();
                form.classList.add('was-validated');
            }
        });
    });
});



// Pricing calculation for order_form.php
function setupOrderFormPricing() {
    const form = document.getElementById('orderForm');
    if (!form) {
        console.log('orderForm not found - not on order_form.php');
        return;
    }

    const numFaces = document.getElementById('numFaces');
    const artType = document.getElementById('artType');
    const artSize = document.getElementById('artSize');
    const totalPrice = document.getElementById('totalPrice');

    // Check if all elements are found
    if (!numFaces || !artType || !artSize || !totalPrice) {
        console.error('One or more form elements not found:', {
            numFaces: !!numFaces,
            artType: !!artType,
            artSize: !!artSize,
            totalPrice: !!totalPrice
        });
        return;
    }

    function calculatePrice() {
        const faces = parseInt(numFaces.value) || 0;
        const type = artType.value;
        const size = artSize.value;

        console.log('Calculating price with:', { faces, type, size });

        // Pricing logic in Rupees
        const basePrices = {
            normal_sketch: 2000,
            watercolor: 4000,
            realistic: 7000
        };
        const facePrice = 1500; // ₹1500 per additional face
        const sizeMultipliers = {
            A5: 0.8,
            A4: 1.0,
            A3: 1.3,
            A2: 1.6
        };
        const deliveryFee = 500; // Flat delivery fee

        let price = basePrices[type] || 0;
        console.log('Base price:', price);

        if (faces > 1) {
            const additionalFacesCost = (faces - 1) * facePrice;
            price += additionalFacesCost;
            console.log('Additional faces cost:', additionalFacesCost, 'New price:', price);
        }

        if (size) {
            const multiplier = sizeMultipliers[size] || 1.0;
            price *= multiplier;
            console.log('Size multiplier:', multiplier, 'New price:', price);
        }

        price += deliveryFee;
        console.log('Delivery fee added:', deliveryFee, 'Final price:', price);

        totalPrice.value = price.toFixed(2);
    }

    // Set up event listeners
    numFaces.addEventListener('change', () => {
        console.log('numFaces changed:', numFaces.value);
        calculatePrice();
    });
    artType.addEventListener('change', () => {
        console.log('artType changed:', artType.value);
        calculatePrice();
    });
    artSize.addEventListener('change', () => {
        console.log('artSize changed:', artSize.value);
        calculatePrice();
    });

    // Calculate initial price if all required fields have values
    if (numFaces.value && artType.value && artSize.value) {
        console.log('Calculating initial price on load');
        calculatePrice();
    }
}

// Initialize both functionalities on DOM load
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing scripts');
    // For index.php
    if (document.getElementById('painting-slider')) {
        autoSlide('painting-slider');
    }
    if (document.getElementById('portrait-slider')) {
        autoSlide('portrait-slider');
    }
    // For order_form.php
    setupOrderFormPricing();
});
