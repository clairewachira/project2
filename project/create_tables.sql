-- Create the administrator table
CREATE TABLE IF NOT EXISTS administrator (
	administrator_id INT PRIMARY KEY AUTO_INCREMENT,
	first_name VARCHAR(50) NOT NULL,
	surname VARCHAR(50) NOT NULL,
	gender ENUM('Male', 'Female', 'Other'),
	email_address VARCHAR(100) NOT NULL,
	phone_number VARCHAR(20),
	password_hash VARCHAR(255) NOT NULL,
	image_url VARCHAR(255)
);

-- Create the patient table
CREATE TABLE IF NOT EXISTS patient (
	patient_id INT PRIMARY KEY AUTO_INCREMENT,
	first_name VARCHAR(50) NOT NULL,
	surname VARCHAR(50) NOT NULL,
	gender ENUM('Male', 'Female', 'Other'),
	email_address VARCHAR(100) NOT NULL,
	phone_number VARCHAR(20),
	date_of_birth DATE,
	social_security_number VARCHAR(20),
	password_hash VARCHAR(255) NOT NULL,
	image_url VARCHAR(255)
);

-- Create the pharmacy table
CREATE TABLE IF NOT EXISTS pharmacy (
	pharmacy_id INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	location VARCHAR(255) NOT NULL,
	email_address VARCHAR(100) NOT NULL,
	phone_number VARCHAR(20)
);

-- Create the pharmaceutical table
CREATE TABLE IF NOT EXISTS pharmaceutical (
	pharmaceutical_id INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	location VARCHAR(255) NOT NULL,
	email_address VARCHAR(100) NOT NULL,
	phone_number VARCHAR(20),
	password_hash VARCHAR(255) NOT NULL
);

-- Create the physician table
CREATE TABLE IF NOT EXISTS physician (
	physician_id INT PRIMARY KEY AUTO_INCREMENT,
	first_name VARCHAR(50) NOT NULL,
	surname VARCHAR(50) NOT NULL,
	gender ENUM('Male', 'Female', 'Other'),
	email_address VARCHAR(100) NOT NULL,
	phone_number VARCHAR(20),
	medical_licence_number VARCHAR(50),
	hospital_name VARCHAR(100),
	specialization VARCHAR(100),
	image_url VARCHAR(255),
	password_hash VARCHAR(255) NOT NULL
);

-- Create the supervisor table
CREATE TABLE IF NOT EXISTS supervisor (
	supervisor_id INT PRIMARY KEY AUTO_INCREMENT,
	first_name VARCHAR(50) NOT NULL,
	surname VARCHAR(50) NOT NULL,
	email_address VARCHAR(100) NOT NULL,
	phone_number VARCHAR(20),
	gender ENUM('Male', 'Female', 'Other'),
	image_url VARCHAR(255),
	password_hash VARCHAR(255) NOT NULL,
	pharmaceutical_id INT,
	FOREIGN KEY (pharmaceutical_id) REFERENCES pharmaceutical (pharmaceutical_id)
);

-- Create the pharmacist table
CREATE TABLE IF NOT EXISTS pharmacist (
	pharmacist_id INT PRIMARY KEY AUTO_INCREMENT,
	first_name VARCHAR(50) NOT NULL,
	surname VARCHAR(50) NOT NULL,
	email_address VARCHAR(100) NOT NULL,
	phone_number VARCHAR(20),
	gender ENUM('Male', 'Female', 'Other'),
	password_hash VARCHAR(255) NOT NULL,
	image_url VARCHAR(255),
	pharmacy_id INT,
	FOREIGN KEY (pharmacy_id) REFERENCES pharmacy (pharmacy_id)
);

-- Create the patient_physician table
CREATE TABLE IF NOT EXISTS patient_physician (
	patient_physician_id INT PRIMARY KEY AUTO_INCREMENT,
	patient_id INT NOT NULL,
	physician_id INT NOT NULL,
	is_primary BOOL NOT NULL,
	FOREIGN KEY (patient_id) REFERENCES patient (patient_id),
	FOREIGN KEY (physician_id) REFERENCES physician (physician_id)
);

-- Create the contract table
CREATE TABLE IF NOT EXISTS contract (
	contract_id INT PRIMARY KEY AUTO_INCREMENT,
	date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	start_date DATE NOT NULL,
	end_date DATE NOT NULL,
	pharmacy_id INT NOT NULL,
	pharmaceutical_id INT NOT NULL,
	FOREIGN KEY (pharmacy_id) REFERENCES pharmacy (pharmacy_id),
	FOREIGN KEY (pharmaceutical_id) REFERENCES pharmaceutical (pharmaceutical_id)
);

-- Create the category table
CREATE TABLE IF NOT EXISTS category (
	categoryId INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(128),
	description TEXT
);

-- Create the drug table
CREATE TABLE IF NOT EXISTS drug (
	drug_id INT PRIMARY KEY AUTO_INCREMENT,
	scientific_name VARCHAR(100) NOT NULL,
	trade_name VARCHAR(100) NOT NULL,
	expiry_date DATE NOT NULL,
	manufacturing_date DATE NOT NULL,
	amount INT,
	form VARCHAR(50),
	image_url VARCHAR(255),
	contract_id INT NOT NULL,
	categoryId INT,
	FOREIGN KEY (categoryId) REFERENCES category(categoryId),
	FOREIGN KEY (contract_id) REFERENCES contract (contract_id)
);

-- Create the prescription table
CREATE TABLE IF NOT EXISTS prescription (
	prescription_id INT PRIMARY KEY AUTO_INCREMENT,
	date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	start_date DATE NOT NULL,
	end_date DATE NOT NULL,
	drug_id INT NOT NULL,
	dosage VARCHAR(50),
	frequency VARCHAR(50),
	cost DECIMAL(10, 2),
	is_assigned BOOL NOT NULL,
	patient_physician_id INT NOT NULL,
	FOREIGN KEY (drug_id) REFERENCES drug (drug_id),
	FOREIGN KEY (patient_physician_id) REFERENCES patient_physician (patient_physician_id)
);
