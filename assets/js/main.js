// Heartfolio Main JavaScript

// Cart functionality
let cart = [];

function addToCart(productId, productName, price, quantity = 1) {
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: price,
            quantity: quantity
        });
    }
    
    updateCartCount();
    showNotification('Product added to cart!');
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCartCount();
    updateCartDisplay();
}

function updateCartCount() {
    const count = cart.reduce((total, item) => total + item.quantity, 0);
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    }
}

function updateCartDisplay() {
    // This would update the cart page display
    // In a real application, this would refresh the cart table
}

function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.backgroundColor = '#3F352D';
    notification.style.color = '#FAF5ED';
    notification.style.padding = '15px 25px';
    notification.style.borderRadius = '5px';
    notification.style.zIndex = '9999';
    notification.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Product image gallery
function changeImage(thumbnail) {
    const mainImage = document.getElementById('main-image');
    if (mainImage) {
        mainImage.src = thumbnail.src;
        
        // Update active class
        document.querySelectorAll('.product-thumbnails img').forEach(img => {
            img.classList.remove('active');
        });
        thumbnail.classList.add('active');
    }
}

// Quantity selector
function increaseQuantity() {
    const input = document.getElementById('quantity');
    if (input) {
        input.value = parseInt(input.value) + 1;
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    if (input && parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = '#D98291';
        } else {
            input.style.borderColor = '#DCC7AD';
        }
    });
    
    return isValid;
}

// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Mobile menu toggle (for responsive design)
function toggleMobileMenu() {
    const navLinks = document.querySelector('.nav-links');
    if (navLinks) {
        navLinks.classList.toggle('active');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    
    // Add event listeners for forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form.id)) {
                e.preventDefault();
                showNotification('Please fill in all required fields');
            }
        });
    });
});

// Search functionality
function searchProducts(query) {
    // In a real application, this would make an AJAX call to search products
    console.log('Searching for:', query);
}

// Category filter
function filterByCategory(category) {
    // In a real application, this would filter products by category
    console.log('Filtering by category:', category);
}

// Sort products
function sortProducts(sortBy) {
    // In a real application, this would sort products
    console.log('Sorting by:', sortBy);
}

// Admin dashboard charts (simple implementation)
function initCharts() {
    // This would initialize chart libraries in a real application
    console.log('Charts initialized');
}

// Order status update (admin)
function updateOrderStatus(orderId, newStatus) {
    // In a real application, this would make an AJAX call to update order status
    console.log('Updating order', orderId, 'to', newStatus);
    showNotification('Order status updated');
}

// Stock update (admin)
function updateStock(productId, newStock) {
    // In a real application, this would make an AJAX call to update stock
    console.log('Updating stock for product', productId, 'to', newStock);
    showNotification('Stock updated');
}

// Product delete (admin)
function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        // In a real application, this would make an AJAX call to delete the product
        console.log('Deleting product', productId);
        showNotification('Product deleted');
    }
}
