# 🧩 Donation & Rescue Management Web Application

Full-stack university project designed to coordinate **donations**, **rescue efforts**, and **requests for aid** between users, rescuers, and administrators.  
The system focuses on role-based access, data integrity, and backend automation through **stored procedures** and **dynamic web components**.

---

## 📘 Project Summary
The application enables:
- 🧍 **Users** to create donation requests or announcements.  
- 🦺 **Rescuers** to accept and complete active requests.  
- 🛠️ **Admins** to monitor system activity and manage statistics.

All roles interact through a shared web interface built in **PHP**, with menus and dashboards that adapt dynamically to each user type.

---

## 🧱 Key Features
- **Role-Based Interface:** Separate dashboards and menus for users, rescuers, and admins (`menu.php` dynamically loads the right view).  
- **Requests & Announcements Logic:**  
  - Implemented using SQL and PHP to categorize data as *new*, *active*, or *completed* based on timestamps (`created_at`, `accepted_at`, `completed_at`, etc.).  
  - Support for rescuer-specific conditions (e.g., `rescuer_acceptance_date`).  
- **Statistics Module:** Filters data by year/month, visualizes totals, and calculates performance metrics.  
- **Expense & Reservation Sections:** Allow admins to log payments, manage expenses, and delete or filter entries.  
- **Stored Procedures (MySQL Workbench):**  
  - `ManageJobApplications` and `AssignCorrectHiredStatus` procedures for maintaining database consistency.  
  - Procedures tested with multiple scenarios, including missing evaluators and simultaneous application dates.  
- **Frontend Integration:**  
  - JavaScript-based filters for month/year and type selection.  
  - Modal forms for adding data (reservations, expenses).  
  - Responsive UI with clear navigation.  

---

## ⚙️ Tech Stack
| Layer | Technology |
|:--|:--|
| **Frontend** | HTML, CSS, JavaScript |
| **Backend** | PHP |
| **Database** | MySQL (Workbench) |
| **Server** | Apache (via XAMPP) |
| **Other Tools** | Chart.js for statistics, Bootstrap for UI styling |

---

## 🧭 Methodology
- **Approach:** Modular development — each page (`reservations.php`, `expenses.php`, etc.) encapsulates its own logic and connects through shared includes.  
- **Database Management:** Designed relational schema in MySQL Workbench; used stored procedures for controlled insert/update logic.  
- **Testing:** Created controlled test cases for procedures and ensured no conflicts with existing records.  

---

## 💼 Role – Emmanouil (Manos) Tzanis
- Designed and implemented **database logic** and stored procedures.  
- Developed core **PHP backend** and role-based user structure.  
- Implemented dynamic filtering and statistics features.  
- Managed schema creation, testing, and debugging within MySQL Workbench.  

---

## 📁 Repository Structure
