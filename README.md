# Nanopass Backend API

A robust Laravel-based REST API for managing system passwords, password policies, user permissions, and security privileges with comprehensive authentication and authorization.

## Table of Contents

-   [Features](#features)
-   [Tech Stack](#tech-stack)
-   [Installation](#installation)
-   [Configuration](#configuration)
-   [API Endpoints](#api-endpoints)
-   [Authentication](#authentication)
-   [Data Models](#data-models)
-   [Error Handling](#error-handling)
-   [Examples](#examples)

## Features

-   **User Management**: Create, read, update, and delete users with role-based access control
-   **Password Management**: Secure system password storage with category-based organization
-   **Password Policies**: Define and enforce password requirements using regex patterns
-   **Privilege Management**: Granular permission control for users
-   **Password Category Management**: Organize passwords by category with associated policies
-   **API Token Authentication**: Secure token-based authentication
-   **Comprehensive Validation**: Input validation with custom error messages
-   **Policy-Based Password Validation**: Passwords must meet the requirements of their category's policy

## Tech Stack

-   **Framework**: Laravel 11
-   **PHP**: 8.2+
-   **Database**: MySQL
-   **Authentication**: API Token (Sanctum-based)
-   **Server**: Laragon/Apache

## Installation

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   MySQL 5.7 or higher
-   Laragon or similar local development environment

### Setup Steps

1. **Clone the repository**

    ```bash
    cd c:\laragon\www
    git clone <repository-url> nanopass-backend
    cd nanopass-backend
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Create environment file**

    ```bash
    copy .env.example .env
    ```

4. **Configure database in .env**

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nanopass
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5. **Generate application key**

    ```bash
    php artisan key:generate
    ```

6. **Run migrations**

    ```bash
    php artisan migrate:refresh
    ```

7. **Start the application**
    ```bash
    php artisan serve
    ```

The API will be available at `http://localhost:8000/api`

## Configuration

### Database

The application uses the following tables:

-   `users` - User accounts
-   `privileges` - User permissions/roles
-   `password_policies` - Password requirements and regex patterns
-   `passwords_category` - Password categories with policy associations
-   `system_passwords` - Actual system passwords
-   `user_system_passwords` - Junction table for user-password relationships

### Authentication

All protected endpoints require an API token in the Authorization header:

```
Authorization: Bearer <api_token>
```

## API Endpoints

### Authentication

#### Register User

-   **Route**: `POST /api/register`
-   **Description**: Create a new user account
-   **Auth**: None
-   **Request**:
    ```json
    {
        "username": "john_doe",
        "email": "john@example.com",
        "password": "SecurePass123!",
        "password_confirmation": "SecurePass123!"
    }
    ```
-   **Response**: 201 Created
    ```json
    {
        "message": "User registered successfully",
        "user": {
            "id": 1,
            "username": "john_doe",
            "email": "john@example.com"
        },
        "token": "random_80_char_api_token"
    }
    ```

#### Login

-   **Route**: `POST /api/login`
-   **Description**: Authenticate user and get API token
-   **Auth**: None
-   **Request**:
    ```json
    {
        "login": "john_doe_or_email@example.com",
        "password": "SecurePass123!"
    }
    ```
-   **Response**: 200 OK
    ```json
    {
        "message": "Login successful",
        "user": {
            "id": 1,
            "username": "john_doe",
            "email": "john@example.com"
        },
        "token": "new_api_token"
    }
    ```

### User Management

#### Create User (Admin)

-   **Route**: `POST /api/admin/create-new-user`
-   **Description**: Admin creates a new user with privileges and system passwords
-   **Auth**: Required
-   **Request**:
    ```json
    {
        "username": "jane_doe",
        "email": "jane@example.com",
        "password": "SecurePass123!",
        "password_confirmation": "SecurePass123!",
        "is_admin": false,
        "privilege_id": 1,
        "system_passwords": [1, 2, 3]
    }
    ```
-   **Response**: 201 Created
    ```json
    {
        "message": "User created successfully",
        "user": {
            "id": 2,
            "username": "jane_doe",
            "email": "jane@example.com",
            "is_admin": false,
            "privilege_id": 1
        },
        "system_passwords": [1, 2, 3],
        "token": "api_token"
    }
    ```

#### List Users

-   **Route**: `GET /api/admin/users`
-   **Description**: Get all users with privileges and system passwords
-   **Auth**: Required
-   **Response**: 200 OK
    ```json
    {
      "message": "Users retrieved successfully",
      "data": [
        {
          "id": 1,
          "username": "john_doe",
          "email": "john@example.com",
          "is_admin": true,
          "privilege_id": 1,
          "privilege": { ... },
          "systemPasswords": [ ... ]
        }
      ]
    }
    ```

#### Get User Details

-   **Route**: `GET /api/admin/edit-user/{user}`
-   **Description**: Get a specific user with all relationships
-   **Auth**: Required
-   **Response**: 200 OK

#### Update User

-   **Route**: `POST /api/admin/update-user/{user}`
-   **Description**: Update user details, privileges, and system passwords
-   **Auth**: Required
-   **Request**:
    ```json
    {
        "username": "jane_doe_updated",
        "password": "NewPass123!",
        "password_confirmation": "NewPass123!",
        "is_admin": true,
        "privilege_id": 2,
        "system_passwords": [1, 2, 4, 5]
    }
    ```
-   **Response**: 200 OK

#### Delete User

-   **Route**: `DELETE /api/admin/delete-user/{user}`
-   **Description**: Delete a user (also detaches all system passwords)
-   **Auth**: Required
-   **Response**: 200 OK

### Privilege Management

#### List Privileges

-   **Route**: `POST /api/admin/privilege`
-   **Description**: Get all privileges
-   **Auth**: Required
-   **Response**: 200 OK
    ```json
    {
        "message": "Privileges retrieved successfully",
        "data": [
            {
                "id": 1,
                "priv_id": 1,
                "name": "Administrator",
                "description": "Full system access"
            }
        ]
    }
    ```

#### Create Privilege

-   **Route**: `POST /api/admin/create-privilege`
-   **Description**: Create a new privilege
-   **Auth**: Required
-   **Request**:
    ```json
    {
        "priv_id": 1,
        "name": "Administrator",
        "description": "Full system access with all permissions"
    }
    ```
-   **Response**: 201 Created

#### Get Privilege

-   **Route**: `GET /api/admin/edit-privilege/{privilege}`
-   **Description**: Get a specific privilege
-   **Auth**: Required
-   **Response**: 200 OK

#### Update Privilege

-   **Route**: `GET /api/admin/update-privilege/{privilege}`
-   **Description**: Update a privilege
-   **Auth**: Required
-   **Request**:
    ```json
    {
        "priv_id": 1,
        "name": "Super Admin",
        "description": "Enhanced admin privileges"
    }
    ```
-   **Response**: 200 OK

#### Delete Privilege

-   **Route**: `GET /api/admin/delete-privilege/{privilege}`
-   **Description**: Delete a privilege
-   **Auth**: Required
-   **Response**: 200 OK

### Password Category Management

#### List Categories

-   **Route**: `GET /api/admin/password-categories`
-   **Description**: Get all password categories with policies
-   **Auth**: Required
-   **Response**: 200 OK
    ```json
    {
      "message": "Password categories retrieved successfully",
      "data": [
        {
          "id": 1,
          "name": "Database Credentials",
          "description": "Database server passwords",
          "password_policy_id": 1,
          "policy": { ... },
          "passwords": [ ... ]
        }
      ]
    }
    ```

#### Create Category

-   **Route**: `POST /api/admin/create-password-category`
-   **Description**: Create a new password category
-   **Auth**: Required
-   **Request**:
    ```json
    {
        "name": "Database Credentials",
        "description": "Passwords for database servers",
        "password_policy_id": 1
    }
    ```
-   **Response**: 201 Created

#### Get Category

-   **Route**: `GET /api/admin/edit-password-category/{category}`
-   **Description**: Get a specific category with policy and passwords
-   **Auth**: Required
-   **Response**: 200 OK

#### Update Category

-   **Route**: `POST /api/admin/update-password-category/{category}`
-   **Description**: Update a category
-   **Auth**: Required
-   **Request**:
    ```json
    {
        "name": "Updated Category Name",
        "password_policy_id": 2
    }
    ```
-   **Response**: 200 OK

#### Delete Category

-   **Route**: `DELETE /api/admin/delete-password-category/{category}`
-   **Description**: Delete a category
-   **Auth**: Required
-   **Response**: 200 OK

### Password Policy Management

#### List Policies

-   **Route**: `GET /api/admin/password-policies`
-   **Description**: Get all password policies
-   **Auth**: Required
-   **Response**: 200 OK
    ```json
    {
        "message": "Password policies retrieved successfully",
        "data": [
            {
                "id": 1,
                "name": "Strong Password Policy",
                "description": "Requires uppercase, numbers, and special characters",
                "regex_pattern": "^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{8,}).*$",
                "expiration": 90
            }
        ]
    }
    ```

#### Create Policy

-   **Route**: `POST /api/admin/create-password-policy`
-   **Description**: Create a new password policy
-   **Auth**: Required
-   **Request**:
    ```json
    {
        "name": "Strong Password Policy",
        "description": "Requires uppercase, numbers, and special characters",
        "regex_pattern": "^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{8,}).*$",
        "expiration": 90
    }
    ```
-   **Response**: 201 Created

#### Get Policy

-   **Route**: `GET /api/admin/edit-password-policy/{policy}`
-   **Description**: Get a specific policy with categories
-   **Auth**: Required
-   **Response**: 200 OK

#### Update Policy

-   **Route**: `POST /api/admin/update-password-policy/{policy}`
-   **Description**: Update a policy
-   **Auth**: Required
-   **Request**:
    ```json
    {
        "regex_pattern": "^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.{12,}).*$",
        "expiration": 60
    }
    ```
-   **Response**: 200 OK

#### Delete Policy

-   **Route**: `DELETE /api/admin/delete-password-policy/{policy}`
-   **Description**: Delete a policy
-   **Auth**: Required
-   **Response**: 200 OK

### System Password Management

#### List System Passwords

-   **Route**: `GET /api/system-passwords`
-   **Description**: Get all system passwords
-   **Auth**: Required
-   **Response**: 200 OK

#### Create System Password

-   **Route**: `POST /api/system-passwords`
-   **Description**: Create a new system password (must comply with category's policy)
-   **Auth**: Required
-   **Request**:
    ```json
    {
        "name": "Database Admin",
        "password": "SecurePass123!@#",
        "description": "Production database admin credentials",
        "passwords_category_id": 1
    }
    ```
-   **Validation**: Password must match the regex pattern of category's password policy
-   **Response**: 201 Created
    ```json
    {
      "message": "System password created successfully",
      "data": {
        "id": 1,
        "name": "Database Admin",
        "description": "Production database admin credentials",
        "passwords_category_id": 1,
        "category": { ... }
      }
    }
    ```

#### Get System Password

-   **Route**: `GET /api/system-passwords/{system_password}`
-   **Description**: Get a specific system password
-   **Auth**: Required
-   **Response**: 200 OK

#### Update System Password

-   **Route**: `POST /api/system-passwords/{system_password}`
-   **Description**: Update system password (validates against policy)
-   **Auth**: Required
-   **Request**:
    ```json
    {
        "password": "NewSecurePass123!@#",
        "name": "Updated Password Name"
    }
    ```
-   **Response**: 200 OK

#### Delete System Password

-   **Route**: `DELETE /api/system-passwords/{system_password}`
-   **Description**: Delete a system password
-   **Auth**: Required
-   **Response**: 200 OK

## Authentication

### API Token Flow

1. **Registration**: User registers → receives API token
2. **Login**: User logs in → receives new API token
3. **Authenticated Requests**: Include token in Authorization header
4. **Token Management**: Each login generates a new token, old tokens remain valid until manually revoked

### Token Usage

```bash
curl -H "Authorization: Bearer <token>" \
  http://localhost:8000/api/admin/users
```

## Data Models

### User

-   `id` - Primary key
-   `username` - Unique username
-   `email` - Unique email address
-   `password` - Hashed password
-   `is_admin` - Admin flag
-   `privilege_id` - Foreign key to Privilege
-   `api_token` - Unique API authentication token
-   `two_factor_secret` - Optional 2FA secret
-   `timestamps` - Created/updated at

**Relationships:**

-   `privilege()` - Belongs to one Privilege
-   `systemPasswords()` - Belongs to many SystemPassword

### Privilege

-   `id` - Primary key
-   `priv_id` - Privilege ID (unique)
-   `name` - Privilege name (unique)
-   `description` - Privilege description
-   `timestamps` - Created/updated at

### PasswordPolicy

-   `id` - Primary key
-   `name` - Policy name (unique)
-   `description` - Policy description
-   `regex_pattern` - Regex pattern for validation
-   `expiration` - Days until password expires (nullable)
-   `timestamps` - Created/updated at

**Relationships:**

-   `categories()` - Has many PasswordCategory

### PasswordCategory

-   `id` - Primary key
-   `name` - Category name (unique)
-   `description` - Category description
-   `password_policy_id` - Foreign key to PasswordPolicy
-   `timestamps` - Created/updated at

**Relationships:**

-   `policy()` - Belongs to one PasswordPolicy
-   `passwords()` - Has many SystemPassword

### SystemPassword

-   `id` - Primary key
-   `name` - Password name
-   `password_hash` - Hashed password
-   `description` - Password description
-   `passwords_category_id` - Foreign key to PasswordCategory
-   `timestamps` - Created/updated at

**Relationships:**

-   `category()` - Belongs to one PasswordCategory
-   `users()` - Belongs to many User

### UserSystemPassword (Junction)

-   `id` - Primary key
-   `user_id` - Foreign key to User
-   `system_password_id` - Foreign key to SystemPassword
-   `assigned_at` - When password was assigned
-   `timestamps` - Created/updated at

## Error Handling

### HTTP Status Codes

-   `200 OK` - Successful retrieval or update
-   `201 Created` - Successful creation
-   `400 Bad Request` - Invalid request format
-   `422 Unprocessable Entity` - Validation error (e.g., password doesn't match policy)
-   `500 Internal Server Error` - Server error

### Error Response Format

```json
{
    "message": "Operation failed",
    "error": "Detailed error message"
}
```

### Validation Errors

```json
{
    "message": "Password validation failed",
    "error": "Password does not meet the requirements of the selected category policy: Strong Password Policy"
}
```

## Examples

### Complete Workflow

1. **Create Password Policy**

    ```bash
    POST /api/admin/create-password-policy
    {
      "name": "Strong",
      "regex_pattern": "^(?=.*[A-Z])(?=.*[0-9])(?=.{8,}).*$",
      "expiration": 90
    }
    ```

2. **Create Password Category**

    ```bash
    POST /api/admin/create-password-category
    {
      "name": "Database",
      "password_policy_id": 1
    }
    ```

3. **Create System Password**

    ```bash
    POST /api/system-passwords
    {
      "name": "Admin DB",
      "password": "MyPass123",
      "passwords_category_id": 1
    }
    ```

    Password validates against policy regex before being saved.

4. **Create Privilege**

    ```bash
    POST /api/admin/create-privilege
    {
      "priv_id": 1,
      "name": "DB Admin"
    }
    ```

5. **Create User**

    ```bash
    POST /api/admin/create-new-user
    {
      "username": "admin_user",
      "email": "admin@example.com",
      "password": "UserPass123!",
      "password_confirmation": "UserPass123!",
      "privilege_id": 1,
      "system_passwords": [1]
    }
    ```

6. **User Logs In**
    ```bash
    POST /api/login
    {
      "login": "admin_user",
      "password": "UserPass123!"
    }
    ```
    Receives API token for subsequent requests.

## Development Notes

-   All passwords are hashed using bcrypt
-   System password hashes are never returned in API responses (hidden)
-   API tokens are 80-character random strings
-   Database uses cascading deletes for data integrity
-   Password validation occurs before hashing to ensure policy compliance
-   User-password relationships use a pivot table for many-to-many flexibility

## Support

For issues or questions, please refer to the Laravel documentation at https://laravel.com/docs
