document.addEventListener('DOMContentLoaded', function () {
    // Monitor for changes in the cart block
    const observer = new MutationObserver(function (mutationsList) {
        mutationsList.forEach(function (mutation) {
            // Find cart items and check for the "Save" badge
            document.querySelectorAll('.wc-block-cart-items__row').forEach(function (cartItem) {
				
                const badge = cartItem.querySelector('.wc-block-components-product-badge.wc-block-components-sale-badge');
				
                // Check if the badge contains the word "Save"
                if (badge && badge.textContent.includes('Save')) {
					console.log(badge.textContent.includes('Save'))
                    // Find and remove the quantity selector for free products
                    const quantitySelector = cartItem.querySelector('.wc-block-cart-item__quantity');
                    if (quantitySelector) {
                        quantitySelector.remove(); // Remove the quantity selector element
                    }
                }
            });
        });
    });

    // Start observing the cart container for DOM changes
    const cartContainer = document.querySelector('.wc-block-cart');
    if (cartContainer) {
        observer.observe(cartContainer, { childList: true, subtree: true });
    }
});
