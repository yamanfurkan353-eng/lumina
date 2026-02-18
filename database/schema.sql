-- Hotel Master Lite Database Schema
-- SQLite 3.x Compatible
-- Created for managing hotel reservations, rooms, customers, and staff

-- Users Table (Admin, Receptionist, Housekeeping)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'receptionist' CHECK (role IN ('admin', 'receptionist', 'housekeeping')),
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT 1,
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Rooms Table
CREATE TABLE IF NOT EXISTS rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_number VARCHAR(10) NOT NULL UNIQUE,
    room_type VARCHAR(50) NOT NULL CHECK (room_type IN ('single', 'double', 'suite', 'deluxe')),
    capacity INTEGER DEFAULT 2,
    price_per_night DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'available' CHECK (status IN ('available', 'occupied', 'dirty', 'maintenance')),
    floor INTEGER DEFAULT 1,
    amenities TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Customers Table
CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20) NOT NULL,
    national_id VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Türkiye',
    birth_date DATE,
    notes TEXT,
    total_stays INTEGER DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Reservations Table
CREATE TABLE IF NOT EXISTS reservations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    room_id INTEGER NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    number_of_guests INTEGER NOT NULL,
    total_price DECIMAL(10,2),
    status VARCHAR(50) NOT NULL DEFAULT 'confirmed' CHECK (status IN ('confirmed', 'checked_in', 'checked_out', 'cancelled')),
    payment_status VARCHAR(50) NOT NULL DEFAULT 'pending' CHECK (payment_status IN ('pending', 'partial', 'paid')),
    notes TEXT,
    created_by INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Audit Log Table (Activity Tracking)
CREATE TABLE IF NOT EXISTS audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(100),
    entity_id INTEGER,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Settings Table (System Configuration)
CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key VARCHAR(255) NOT NULL UNIQUE,
    value TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'string' CHECK (type IN ('string', 'int', 'bool', 'json')),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create Indexes for Performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_is_active ON users(is_active);

CREATE INDEX idx_rooms_status ON rooms(status);
CREATE INDEX idx_rooms_room_type ON rooms(room_type);

CREATE INDEX idx_customers_email ON customers(email);
CREATE INDEX idx_customers_phone ON customers(phone);
CREATE INDEX idx_customers_national_id ON customers(national_id);

CREATE INDEX idx_reservations_customer_id ON reservations(customer_id);
CREATE INDEX idx_reservations_room_id ON reservations(room_id);
CREATE INDEX idx_reservations_status ON reservations(status);
CREATE INDEX idx_reservations_check_in ON reservations(check_in);
CREATE INDEX idx_reservations_check_out ON reservations(check_out);
CREATE INDEX idx_reservations_created_by ON reservations(created_by);

CREATE INDEX idx_audit_log_user_id ON audit_log(user_id);
CREATE INDEX idx_audit_log_entity_type ON audit_log(entity_type);
CREATE INDEX idx_audit_log_created_at ON audit_log(created_at);

-- Initial System Settings
INSERT OR IGNORE INTO settings (key, value, type) VALUES
    ('hotel_name', 'Hotel Master Lite', 'string'),
    ('currency', 'TRY', 'string'),
    ('currency_symbol', '₺', 'string'),
    ('check_in_time', '14:00', 'string'),
    ('check_out_time', '11:00', 'string'),
    ('timezone', 'Europe/Istanbul', 'string'),
    ('language', 'tr', 'string'),
    ('items_per_page', '20', 'int'),
    ('enable_pwa', '1', 'bool'),
    ('enable_backups', '1', 'bool'),
    ('backup_retention_days', '30', 'int');
