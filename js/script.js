// --- Product Data ---
const products = [
    { id: 1, name: "Spicy Pepperoni Pizza", category: "Pizza", price: 14.99, rating: 4.8, img: "https://images.unsplash.com/photo-1628840042765-356cda07504e?q=80&w=800&auto=format&fit=crop", desc: "Classic pizza with spicy pepperoni and mozzarella." },
    { id: 2, name: "Classic Cheeseburger", category: "Burger", price: 8.99, rating: 4.5, img: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=800&auto=format&fit=crop", desc: "Juicy beef patty with melted cheese and fresh veggies." },
    { id: 3, name: "Chocolate Lava Cake", category: "Desserts", price: 6.50, rating: 4.9, img: "https://images.unsplash.com/photo-1624353365286-3f8d62daad51?q=80&w=800&auto=format&fit=crop", desc: "Warm chocolate cake with a gooey molten center." },
    { id: 4, name: "Iced Caramel Macchiato", category: "Drinks", price: 4.99, rating: 4.6, img: "https://images.unsplash.com/photo-1497935586351-b67a49e012bf?q=80&w=800&auto=format&fit=crop", desc: "Refreshing iced coffee with caramel drizzle." },
    { id: 5, name: "Veggie Supreme Pizza", category: "Pizza", price: 13.50, rating: 4.4, img: "https://images.unsplash.com/photo-1574071318508-1cdbab80d002?q=80&w=800&auto=format&fit=crop", desc: "Loaded with bell peppers, onions, mushrooms, and olives." },
    { id: 6, name: "Crispy Chicken Burger", category: "Burger", price: 9.50, rating: 4.7, img: "https://images.unsplash.com/photo-1615297928064-24977384d0cb?q=80&w=800&auto=format&fit=crop", desc: "Crunchy fried chicken breast with spicy mayo." }
];

// --- Global Variables ---
let cart = JSON.parse(localStorage.getItem('biteNowCart')) || [];

// --- DOM Elements ---
const productList = document.getElementById('product-list');
const filterBtns = document.querySelectorAll('.filter-btn');
const searchInput = document.getElementById('search-input');
const cartCount = document.getElementById('cart-count');
const cartItemsContainer = document.getElementById('cart-items');
const cartTotal = document.getElementById('cart-total');

// --- Initialize App ---
document.addEventListener('DOMContentLoaded', () => {
    renderProducts(products);
    updateCartUI();
});

// --- Render Products ---
function renderProducts(items) {
    productList.innerHTML = '';
    
    if (items.length === 0) {
        productList.innerHTML = `<div class="col-12 text-center py-5"><h4 class="text-muted">No products found.</h4></div>`;
        return;
    }

    items.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'col-md-6 col-lg-4';
        productCard.innerHTML = `
            <div class="card food-card shadow-sm h-100">
                <img src="${product.img}" class="card-img-top" alt="${product.name}">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title fw-bold mb-0">${product.name}</h5>
                        <span class="badge bg-success"><i class="fa-solid fa-star me-1"></i>${product.rating}</span>
                    </div>
                    <p class="card-text text-muted small flex-grow-1">${product.desc}</p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="fw-bold fs-5">$${product.price.toFixed(2)}</span>
                        <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="addToCart(${product.id})">
                            <i class="fa-solid fa-plus me-1"></i> Add
                        </button>
                    </div>
                </div>
            </div>
        `;
        productList.appendChild(productCard);
    });
}

// --- Filter Functionality ---
filterBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
        // Active state styling
        filterBtns.forEach(b => {
            b.classList.remove('btn-primary', 'active');
            b.classList.add('btn-outline-primary');
        });
        e.target.classList.remove('btn-outline-primary');
        e.target.classList.add('btn-primary', 'active');

        // Logic
        const category = e.target.getAttribute('data-filter');
        filterAndSearchProducts(category, searchInput.value);
    });
});

// --- Search Functionality ---
searchInput.addEventListener('input', (e) => {
    const activeCategory = document.querySelector('.filter-btn.active').getAttribute('data-filter');
    filterAndSearchProducts(activeCategory, e.target.value);
});

function filterAndSearchProducts(category, searchTerm) {
    let filtered = products;
    
    if (category !== 'All') {
        filtered = filtered.filter(p => p.category === category);
    }
    
    if (searchTerm) {
        filtered = filtered.filter(p => p.name.toLowerCase().includes(searchTerm.toLowerCase()));
    }
    
    renderProducts(filtered);
}

// --- Cart Functionality ---
function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    const existingItem = cart.find(item => item.id === productId);

    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ ...product, quantity: 1 });
    }
    
    saveAndRenderCart();
    
    // Optional: Show small feedback toast here
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveAndRenderCart();
}

function updateQuantity(productId, change) {
    const item = cart.find(i => i.id === productId);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(productId);
        } else {
            saveAndRenderCart();
        }
    }
}

function saveAndRenderCart() {
    localStorage.setItem('biteNowCart', JSON.stringify(cart));
    updateCartUI();
}

function updateCartUI() {
    // Update Badge
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.innerText = totalItems;

    // Render Items
    cartItemsContainer.innerHTML = '';
    let totalPrice = 0;

    if (cart.length === 0) {
        cartItemsContainer.innerHTML = `<div class="text-center text-muted mt-5"><i class="fa-solid fa-cart-arrow-down fs-1 mb-3"></i><p>Your cart is empty.</p></div>`;
    } else {
        cart.forEach(item => {
            totalPrice += item.price * item.quantity;
            const cartItem = document.createElement('div');
            cartItem.className = 'd-flex align-items-center mb-3 pb-3 border-bottom';
            cartItem.innerHTML = `
                <img src="${item.img}" class="cart-item-img me-3" alt="${item.name}">
                <div class="flex-grow-1">
                    <h6 class="mb-0 fw-bold">${item.name}</h6>
                    <div class="text-primary fw-bold small">$${(item.price * item.quantity).toFixed(2)}</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary qty-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                    <span class="fw-bold">${item.quantity}</span>
                    <button class="btn btn-outline-secondary qty-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                </div>
            `;
            cartItemsContainer.appendChild(cartItem);
        });
    }

    // Update Total
    cartTotal.innerText = `$${totalPrice.toFixed(2)}`;
}