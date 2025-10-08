# SpotBook - Local Service Booking Platform
# Live Link: [https://towntime.rf.gd/]

A modern, responsive, and robust web application designed to streamline appointment scheduling for local service shops (salons, dentists, etc.) and their customers.

---

## Features
* **Two Distinct Roles (RBAC):** Separate authentication and user experience for **Customer** and **Shop Keeper**.
* **Customer Booking Flow:** Browse services by category, view available shops, and reserve time slots.
* **Dynamic Queue Logic:** Real-time calculation of available 30-minute time slots based on existing bookings and service duration.
* **Shop Keeper Management:** Onboard and register a new shop, and manage service offerings (add and delete services).
* **Booking Management:** Customers can view future appointments, track appointments that are past time (marked as "DONE"), and instantly cancel upcoming reservations.

## Tech Stack
* **Frontend:** HTML5, CSS3 (Custom Modern UI), JavaScript (Vanilla, AJAX for data flow)
* **Backend:** PHP (Procedural, handling authentication and data processing)
* **Database:** MySQL (Structured with Foreign Keys and PDO for secure data access)
* **Session:** PHP Sessions for maintaining user login status and role.

---

## Project Workflow
1.  **Role Selection & Login:** User chooses their role (Customer/Shop Keeper) before logging in or registering.
2.  **Shop Onboarding (Shop Keeper):** On first login, the Shop Keeper registers their business details.
3.  **Service Setup:** The Shop Keeper defines their service list, which automatically links to the chosen category.
4.  **Category Browsing (Customer):** Customer selects a service category and views linked shops.
5.  **Slot Reservation:** Customer selects a date, views the dynamic queue of booked/available 30-minute slots, and submits a reservation.
6.  **Queue Monitoring:** The Shop Keeper views a live, date-specific list of their appointments on their dashboard.

---

## Setup Instructions
1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/vanshrn/SpotBook](https://github.com/vanshrn/SpotBook)
    cd SpotBook
    ```
2.  **Configure the Database:**
    * Create a MySQL database (e.g., `booking_system`).
    * Update DB credentials (username, password) in `php/db_config.php`.
    * Manually run the required SQL schema to create the `users`, `shops`, `services`, `bookings`, and `categories` tables.
    * Populate the `categories` table with initial data (e.g., Dentist, Hair Salon, Tattoo Studio, Barbershop).
3.  **Run Locally:**
    * Ensure your local server (XAMPP/WAMP) has PHP and MySQL running.
    * Run the PHP server from your project root: `php -S localhost:8000`
    * Access via `http://localhost:8000/`.

---

## File Structure
* `index.html` — Welcome / Role Selection Landing Page
* `login.html`, `register.html` — Authentication Forms
* `categories.html`, `shops.html`, `book.html` — Customer Booking Flow Pages
* `my_bookings.html` — Customer Booking Management
* `shop_dashboard.html`, `shop_bookings.html`, `shop_services.html` — Shop Keeper Management Pages
* `php/` — All Backend PHP scripts (Authentication, API endpoints, DB config)
* `style.css` — Custom CSS styles

---
**SpotBook — Simplify scheduling. Get booked today.**
