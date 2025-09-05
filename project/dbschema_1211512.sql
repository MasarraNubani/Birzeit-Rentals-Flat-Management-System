CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer','owner','manager') NOT NULL,
    PRIMARY KEY (id)
);


CREATE TABLE customers (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    national_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    email VARCHAR(100) NOT NULL,
    mobile_number VARCHAR(20),
    telephone_number VARCHAR(20),
    customer_id CHAR(9) UNIQUE,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE owners (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    national_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    email VARCHAR(100) NOT NULL,
    mobile_number VARCHAR(20),
    telephone_number VARCHAR(20),
    bank_name VARCHAR(100),
    bank_branch VARCHAR(100),
    account_number VARCHAR(50),
    owner_id CHAR(9) UNIQUE,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE flats (
    id INT NOT NULL AUTO_INCREMENT,
    owner_id INT NOT NULL,
    location VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    rent_cost DECIMAL(10,2) NOT NULL,
    available_from DATE NOT NULL,
    available_to DATE NOT NULL,
    bedrooms INT NOT NULL,
    bathrooms INT NOT NULL,
    size_sqm FLOAT,
    has_heating BOOLEAN,
    has_air_conditioning BOOLEAN,
    has_access_control BOOLEAN,
    has_parking BOOLEAN,
    backyard_type ENUM('individual','shared','none'),
    has_playground BOOLEAN,
    has_storage BOOLEAN,
    description TEXT,
    is_furnished BOOLEAN,
    is_approved BOOLEAN DEFAULT FALSE,
    reference_number CHAR(6) UNIQUE,
    PRIMARY KEY (id),
    FOREIGN KEY (owner_id) REFERENCES owners(id)
);
CREATE TABLE rentals (
    id INT NOT NULL AUTO_INCREMENT,
    customer_id INT NOT NULL,
    flat_id INT NOT NULL,
    rental_start DATE NOT NULL,
    rental_end DATE NOT NULL,
    total_amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (flat_id) REFERENCES flats(id)
);

CREATE TABLE appointments (
    id INT NOT NULL AUTO_INCREMENT,
    customer_id INT NOT NULL,
    flat_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    is_confirmed BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (flat_id) REFERENCES flats(id)
);

CREATE TABLE messages (
    id INT NOT NULL AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    title VARCHAR(255),
    body TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (id),
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);
CREATE TABLE images (
    id INT NOT NULL AUTO_INCREMENT,
    flat_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    PRIMARY KEY (id),
    FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE CASCADE
);
CREATE TABLE marketing_info (
    id INT NOT NULL AUTO_INCREMENT,
    flat_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    url VARCHAR(255),
    PRIMARY KEY (id),
    FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE CASCADE
);
CREATE TABLE viewing_times (
    id INT NOT NULL AUTO_INCREMENT,
    flat_id INT NOT NULL,
    day_of_week ENUM('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
    time_from TIME NOT NULL,
    time_to TIME NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    is_booked BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (id),
    FOREIGN KEY (flat_id) REFERENCES flats(id) ON DELETE CASCADE
);

