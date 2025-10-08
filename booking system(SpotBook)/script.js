// Function to check login status and get user role from the backend session
function checkLoginStatus() {
    // Fetches the current session state from the server
    return fetch('backend/check_session.php')
        .then(response => response.json()); // Returns {isLoggedIn: bool, userRole: string}
}

// Global variable declarations
const urlParams = new URLSearchParams(window.location.search);
const currentPath = window.location.pathname;

// Function to handle logout
function logout() {
    const baseURL = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
    
    fetch(baseURL + '/backend/logout.php', {
        method: 'POST',
        credentials: 'include' // This is important for cookies/session
    })
    .then(() => {
        window.location.href = baseURL + '/index.html';
    })
    .catch(error => {
        console.error('Logout error:', error);
        window.location.href = baseURL + '/index.html';
    });
}
let currentUserRole = null;

// This block ensures the shopId and serviceId are correctly transferred to the hidden inputs.
if (currentPath.includes('book.html')) {
    const shopId = urlParams.get('shop_id');
    const serviceId = urlParams.get('service_id');

    document.addEventListener('DOMContentLoaded', () => {
        const serviceIdInput = document.getElementById('service-id');
        const shopIdInput = document.getElementById('shop-id');

        if (serviceId && serviceIdInput) {
            serviceIdInput.value = serviceId;
        }
        if (shopId && shopIdInput) {
            shopIdInput.value = shopId;
        }
        
        // Fetch and display shop details (name, address)
        if (shopId) {
            fetch(`backend/get_shop_details.php?shop_id=${shopId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const shop = data.shop;
                        const shopTitleElement = document.getElementById('shop-title');
                        const shopInfoElement = document.getElementById('shop-info');
                        
                        // Set the shop name and service name in the H1 tag
                        shopTitleElement.textContent = `Booking at ${shop.shop_name}`;
                        
                        // Set the shop address and information
                        shopInfoElement.innerHTML = `<p>Address: ${shop.address}</p>`;
                        
                        // Map Link Logic (Simplified): Targets the anchor tag
                        const mapLink = document.getElementById('shop-map-link');
                        if (mapLink) {
                            // Creates a clickable link to search Google Maps by address
                            const mapUrl = `http://maps.google.com/?q=${encodeURIComponent(shop.address)}`;
                            mapLink.href = mapUrl;
                        }

                    } else {
                        document.getElementById('shop-title').textContent = 'Error loading shop details.';
                    }
                })
                .catch(error => {
                    console.error('Error fetching shop details:', error);
                    document.getElementById('shop-title').textContent = 'Error fetching shop details.';
                });
        }
    });
}

// CORE FUNCTION: Fetches available/booked time slots for the booking page
function fetchSlots() {
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');
    const serviceIdInput = document.getElementById('service-id');
    const shopIdInput = document.getElementById('shop-id'); 

    // Retrieve values from the hidden input fields (now guaranteed to be set)
    const serviceId = serviceIdInput ? serviceIdInput.value : null;
    const date = dateInput ? dateInput.value : null;
    const shopId = shopIdInput ? shopIdInput.value : null;

    if (!serviceId || !date || !shopId) {
         timeSelect.innerHTML = '<option value="">-- Select Date/Shop Not Found --</option>';
         timeSelect.disabled = true;
         return;
    }

    timeSelect.innerHTML = '<option value="">Loading slots...</option>';
    timeSelect.disabled = true;

    // Use the available_slots.php backend endpoint
    fetch(`backend/available_slots.php?service_id=${serviceId}&date=${date}&shop_id=${shopId}`)
        .then(response => response.json())
        .then(data => {
            timeSelect.innerHTML = '';
            timeSelect.disabled = false;

            if (data.status === 'success' && data.slots.length > 0) {
                let availableFound = false;
                data.slots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot.time_24h;
                    option.textContent = `${slot.time_display} ${slot.is_available ? '(Available)' : '(Booked - Queue)'}`;
                    option.disabled = !slot.is_available;
                    if (slot.is_available) {
                         availableFound = true;
                    }
                    timeSelect.appendChild(option);
                });

                if (!availableFound) {
                    timeSelect.innerHTML = '<option value="">No slots available for this day.</option>';
                    timeSelect.disabled = true;
                }
            } else {
                timeSelect.innerHTML = '<option value="">No slots available or Error.</option>';
                timeSelect.disabled = true;
            }
        })
        .catch(error => {
            console.error("Error fetching slots:", error);
            timeSelect.innerHTML = '<option value="">Could not fetch slots.</option>';
            timeSelect.disabled = true;
        });
}

// DOM CONTENT LOADED AND MAIN APPLICATION LOGIC

document.addEventListener('DOMContentLoaded', () => {

    // --- INITIALIZATION AND ROLE-BASED PROTECTION ---
    checkLoginStatus().then(data => {
        const isLoggedIn = data.isLoggedIn;
        const userRole = data.userRole;
        currentUserRole = userRole; 
        
        // CORE PAGE PROTECTION LOGIC

        // 1. General Protected Pages (Customer Pages)
        if (!isLoggedIn && (
            currentPath.includes('categories.html') ||
            currentPath.includes('shops.html') ||
            currentPath.includes('book.html') ||
            currentPath.includes('my_bookings.html')
        )) {
            window.location.href = 'login.html?role=customer'; 
            return;
        }

        // 2. Shop Keeper Protected Pages
        if (currentPath.includes('shop_dashboard.html') || 
            currentPath.includes('upload_shop.html') ||
            currentPath.includes('shop_services.html') ||
            currentPath.includes('shop_bookings.html')
        ) {
            if (!isLoggedIn) {
                 window.location.href = 'login.html?role=shopkeeper';
                 return;
            }
            if (userRole !== 'shopkeeper') {
                 alert('Access Denied. You are logged in as a Customer.');
                 window.location.href = 'categories.html'; 
                 return;
            }
        }
        
        // --- BOOKING FORM SUBMISSION LOGIC (book.html) ---
        const bookingForm = document.getElementById('bookingForm');
        if (bookingForm) { 
            bookingForm.addEventListener('submit', (e) => {
                e.preventDefault();

                // Check login status again BEFORE submission
                checkLoginStatus().then(subData => {
                    const freshIsLoggedIn = subData.isLoggedIn;
                    const freshUserRole = subData.userRole;

                    if (!freshIsLoggedIn) {
                        alert('Your session expired. Please log in.');
                        window.location.href = 'login.html';
                        return;
                    }
                    if (freshUserRole !== 'customer') {
                        alert('Access Denied. Only customers can reserve slots.');
                        window.location.href = 'shop_dashboard.html';
                        return;
                    }

                    const formData = new FormData(bookingForm);
                    
                    if (!formData.get('time')) {
                         document.getElementById('message').textContent = 'Please select an available time slot.';
                         document.getElementById('message').style.color = 'red';
                         return;
                    }

                    // Proceed with submission
                    fetch('backend/book_service.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        const messageDiv = document.getElementById('message');
                        messageDiv.textContent = data.message;
                        if (data.status === 'success') {
                            messageDiv.style.color = 'green';
                            bookingForm.reset(); 
                        } else {
                            messageDiv.style.color = 'red';
                        }
                    })
                    .catch(error => {
                        console.error('Error submitting form:', error);
                        document.getElementById('message').textContent = 'An unexpected error occurred. Please try again.';
                        document.getElementById('message').style.color = 'red';
                    });
                });
            });
        }
    }); // END initial checkLoginStatus promise

    // --- BOOKING PAGE DYNAMIC SLOT ATTACHMENT ---
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');

    if (dateInput && timeSelect) {
        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);

        // Attach the event listener to trigger fetchSlots() when date changes
        dateInput.addEventListener('change', fetchSlots);
    }
    
    // --- MY BOOKINGS PAGE LOGIC (my_bookings.html) ---
    const bookingsList = document.getElementById('bookings-list');

    if (bookingsList) { // Checks if we are on the my_bookings.html page
    checkLoginStatus().then(data => {
        // The backend handles filtering records from previous days using CURDATE()
        if (!data.isLoggedIn) return; 

        fetch('backend/get_user_bookings.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.bookings.length > 0) {
                        
                        // Get current time once for comparison
                        const now = new Date(); 
                        
                        data.bookings.forEach(booking => {
                            // Combine booking date and time into a single Date object for comparison
                            const bookingDateTime = new Date(`${booking.booking_date} ${booking.booking_time}`);
                            
                            // Check if the booking is for today AND the time has passed
                            // We use toDateString() to compare only the date part
                            const isDone = (bookingDateTime.toDateString() === now.toDateString() && bookingDateTime < now);
                            
                            const bookingItem = document.createElement('div');
                            
                            bookingItem.innerHTML = `
                                <p>Service: ${booking.service_name}</p>
                                <p>Date: ${booking.booking_date}</p>
                                <p>Time: ${booking.booking_time}</p>
                                ${isDone 
                                    ? '<button disabled style="background-color: #4CAF50; color: white; cursor: default;">DONE (Past Time)</button>' 
                                    : `<button class="cancel-btn" data-booking-id="${booking.id}">Cancel Booking</button>`}
                                <hr>
                            `;
                            bookingsList.appendChild(bookingItem);
                        });

                        // Attach event listeners ONLY to the active Cancel buttons
                        document.querySelectorAll('.cancel-btn').forEach(button => {
                            button.addEventListener('click', (e) => {
                                const bookingId = e.target.dataset.bookingId;
                                if (confirm('Are you sure you want to cancel this booking?')) {
                                    fetch('backend/cancel_booking.php', {
                                        method: 'POST',
                                        body: new URLSearchParams({ booking_id: bookingId }),
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        alert(data.message);
                                        if (data.status === 'success') {
                                            window.location.reload(); 
                                        }
                                    });
                                }
                            });
                        });
                    } else {
                        bookingsList.innerHTML = '<p>You have no upcoming bookings.</p>';
                    }
                } else {
                    bookingsList.innerHTML = `<p>${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching bookings:', error);
                bookingsList.innerHTML = '<p>An error occurred while loading your bookings.</p>';
            });
    });
}

    if (document.getElementById('service') && !document.getElementById('shop-id')) { 
         fetch('backend/get_services.php')
            .then(response => response.json())
            .then(data => {
                const serviceSelect = document.getElementById('service');
                if (data && data.length) {
                     data.forEach(service => {
                        const option = document.createElement('option');
                        option.value = service.id;
                        option.textContent = service.name;
                        serviceSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error fetching services:', error));
    }
});