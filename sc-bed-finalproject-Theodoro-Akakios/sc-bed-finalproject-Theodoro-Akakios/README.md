# PHP & MariaDB Development Enviroment Tester

## Purpose

This simple app will help setup a working enviroment for your final project. It will also check that PHP is working, and that a connection to the MariaDB database server can be established. 

## Usage

1. Clone this repository.
2. Ensure Docker Desktop is running.
3. Open a terminal and change to the folder where you cloned this repository.
4. Run the run.cmd script.  
    4.1. On Windows, type **.\run.cmd**.    
    4.2. On macOS or Linux, type: **./run.cmd**.
5. Open [http://localhost:8001](https://localhost:8001) in your browser.

## Details

PHP has been setup as usual. A MariaDB server has also been created. Details follow:

- **Host**: mariadb
- **Database Name:** kahuna
- **User**: root
- **Pass**: root

The services started include:
- API Server on [http://localhost:8000](https://localhost:8000).
- Client on [http://localhost:8001](https://localhost:8001).

## Next Steps

You can now start working on your final project.

1. It is safe to delete the contents of the **client** folder. 

------------------------------------------------------------------------------------------------------------------------------------------------------------

# FINAL PROJECT  --  Product Registration System API

## Overview

This project is a Product Registration System that allows clients to register products and view their warranty details. Admin users can add products to the system. The system is built using PHP with MariaDB for the database. The API endpoints are tested using Postman, and version control is managed via GitHub.

## Objectives

1. **Set Up Project Environment**: Install necessary tools like VSCODE, PHP, MariaDB, Postman, and Git.
2. **Database Design**: Create an relational database (ERD) with tables for users, products, and registrations.
3. **Develop REST API**: Implement various endpoints, including user authentication, product registration, and admin-only product management.
4. **API Testing**: Use Postman to create and test all API endpoints, ensuring functionality for both clients and admin users.
5. **Documentation**: Provide clear instructions in this README for setting up, running, and testing the project.

## Technologies Used

- **PHP**: Backend language to create RESTful API endpoints.
- **MariaDB**: Relational database to store users, products, and registration data.
- **Postman**: API development and testing tool.
- **VSCode**: Integrated Development Environment (IDE) for coding.
- **GitHub**: Version control platform for managing the project codebase.

## Step 1: Set Up the Project Environment

### Prerequisites

1. **Install Required Tools**:
    - PHP
    - MariaDB 
    - Postman for API testing
    - A web server (Using docker)
    - Git for version control

2. **Set Up Project Directory**:
    - Create a new directory for the project.
    - Initialize a Git repository.

## Step 2: Design the Database

### Create the Database

1. **Entity Relationship Diagram (ERD)**: Design tables for users, products, and registrations.
    - **Users**: `id`, `firstName`, `lastName`, `email`, `password`, `accessLevel` (client/admin), `phone`, `address`
    - **Products**: `id`, `serial`, `name`, `warrantyLength`
    - **Registrations**: `id`, `userId`, `productId`, `registrationDate`
    - **AccessToken**: `id`, `userId`, `birth`

2. **Database Creation**:
    - Create the MariaDB database and tables using SQL scripts.
    - Populate the product table with example data for testing.
    
    Example SQL command:
    ```sql
    CREATE DATABASE IF NOT EXISTS kahuna;
    ```

## Step 3: Develop the REST API

1. **PHP Project Setup**:
    - Set up routing in PHP to handle different API endpoints.
    - Use libraries like Slim for routing or custom logic.

2. **Endpoints**:
    - **Non-Authenticated**:
        - Create Account
        - Login
    - **Authenticated Endpoints**:
        - Register Product (validate the serial number, link it to the user)
        - View Registered Products
        - View Product Details (serial number, warranty details)
        - Logout
    - **Admin-Only**:
        - Add Product (Admin can add new products to the database)

## Step 4: Write Tests for the API

1. **Postman Collection**:
    - Create a Postman collection to include all API endpoints.
    - Test cases should cover:
        - Account creation
        - Admin account creation
        - Login
        - Product registration
        - Add product (admin)
        - Authentication and authorization

2. **Test Authentication**:
    - Validate tokens and session management.
    
3. **Test Admin Privileges**:
    - Ensure admin-only routes are restricted for clients.

## Step 5: Push to GitHub

1. **Commit the Code**:
    - Commit code regularly using meaningful commit messages.
    
    Example:
    ```bash
    git add .
    git commit -m "Implement product registration endpoint"
    ```

2. **Push to GitHub**:
    - Push the entire project, including the README.md, code, database scripts, and Postman collection.
    ```bash
    git push origin main
    ```

## Step 6: Final Testing and Review

1. **Review Your Code**:
    - Ensure all endpoints are functioning correctly.
    - Double-check the README.md for clarity and completeness.

2. **Test the API**:
    - Run all Postman tests to ensure all functionalities work as expected.
    - Verify authentication and admin privileges.

---

**DATABASE USER = root**
**DATABASE PASSWORD = root**