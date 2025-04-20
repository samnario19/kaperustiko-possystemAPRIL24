-- Create remit_sales table
CREATE TABLE IF NOT EXISTS remit_sales (
    remit_id INT AUTO_INCREMENT PRIMARY KEY,
    cashier_name VARCHAR(255) NOT NULL,
    total_sales DECIMAL(10,2) NOT NULL,
    remit_date DATE NOT NULL,
    remit_time TIME NOT NULL,
    remit_shortage DECIMAL(10,2) DEFAULT 0.00,
    remit_validation VARCHAR(50) NOT NULL,
    shift VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create remit_sales_data table
CREATE TABLE IF NOT EXISTS remit_sales_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    remit_id INT NOT NULL,
    receipt_number VARCHAR(50) NOT NULL,
    items_ordered TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    amount_change DECIMAL(10,2) NOT NULL,
    order_date DATE NOT NULL,
    order_time TIME NOT NULL,
    order_take VARCHAR(50) NOT NULL,
    cashier_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (remit_id) REFERENCES remit_sales(remit_id) ON DELETE CASCADE
); 