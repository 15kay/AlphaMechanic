// Load cart items from localStorage
function loadCartItems() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let cartItemsContainer = document.getElementById('cart-items');
    let totalAmount = 0;
    cartItemsContainer.innerHTML = '';

    cart.forEach((item, index) => {
        totalAmount += item.price * item.quantity;

        cartItemsContainer.innerHTML += `
            <tr>
                <td>${item.name}</td>
                <td>R${item.price}</td>
                <td>
                    <button onclick="decrementItem(${index})">-</button>
                    ${item.quantity}
                    <button onclick="incrementItem(${index})">+</button>
                </td>
                <td>R${item.price * item.quantity}</td>
                <td><button onclick="removeItem(${index})">Remove</button></td>
            </tr>
        `;
    });

    document.getElementById('total-amount').textContent = totalAmount;
    updateCartCount();
}

// Update cart count on the cart icon
function updateCartCount() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let totalItems = cart.reduce((acc, item) => acc + item.quantity, 0);
    document.getElementById('cart-count').textContent = totalItems;
}

// Add to cart (invoked on product page)
function addToCart(productName, productPrice) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let itemIndex = cart.findIndex(item => item.name === productName);

    if (itemIndex !== -1) {
        cart[itemIndex].quantity += 1;
    } else {
        cart.push({ name: productName, price: productPrice, quantity: 1 });
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    alert(`${productName} added to cart!`);
    updateCartCount();
}

// Remove item from cart
function removeItem(index) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCartItems();
}

// Increment item quantity in cart
function incrementItem(index) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart[index].quantity += 1;
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCartItems();
}

// Decrement item quantity in cart
function decrementItem(index) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    if (cart[index].quantity > 1) {
        cart[index].quantity -= 1;
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCartItems();
    }
}

// Load cart items when the page is loaded
window.onload = loadCartItems;
