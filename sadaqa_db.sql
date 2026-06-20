-- Create sadaqa database
CREATE DATABASE IF NOT EXISTS sadaqa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sadaqa;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User profiles table
CREATE TABLE IF NOT EXISTS profiles (
    user_id INT PRIMARY KEY,
    total_donations_collected DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Campaigns table (active or inactive)
CREATE TABLE IF NOT EXISTS campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT,
    goal_amount DECIMAL(10, 2) NOT NULL,
    current_amount DECIMAL(10, 2) DEFAULT 0.00,
    image_path VARCHAR(255),
    admin_id INT NULL, -- If admin is the creator
    user_id INT NULL,  -- If approved user campaign
    status ENUM('active', 'inactive') DEFAULT 'active', -- active or inactive
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Campaign requests table (pending review)
CREATE TABLE IF NOT EXISTS campaign_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT,
    goal_amount DECIMAL(10, 2) NOT NULL,
    image_path VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending', -- pending means under review
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Donations table
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    campaign_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    donation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
);

-- Cart items table
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    campaign_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed data (Examples)

-- Insert categories
INSERT INTO categories (name) VALUES 
('سقيا ماء'),
('ترميم مساجد'),
('حفر أبار'),
('كفالة أيتام'),
('كفالة حجاج'),
('ساعد مريضًا'),
('توفير الطعام للمساكين'),
('أخرى');

-- Insert admin
INSERT INTO admins (email, password) VALUES 
('admin@sadaqa.com', 'admin123');

-- Insert user
INSERT INTO users (email, password) VALUES 
('user@example.com', 'user123');

-- Insert user profiles
INSERT INTO profiles (user_id, total_donations_collected) VALUES 
(1, 2450.00);

-- Insert campaigns (active and inactive)
INSERT INTO campaigns (title, description, category_id, goal_amount, current_amount, image_path, admin_id, status) VALUES 
('حملة بناء مسجد الغفران', 'ساهم معنا في بناء بيت من بيوت الله، المسجد يخدم منطقة نائية ويحتاج لتكاتف الجميع لاكتماله.', 2, 50000.00, 20000.00, '../images/mosque.png', 1, 'active'),
('توفير برادات ماء للمدارس', 'توفير مياه باردة ونقية لطلاب المدارس في المناطق الحارة.', 1, 15000.00, 5000.00, '../images/water.png', 1, 'active'),
('سلة رمضان الغذائية', 'توزيع سلال غذائية متكاملة للأسر المحتاجة خلال شهر رمضان المبارك.', 7, 30000.00, 12000.00, '../images/food.png', 1, 'active'),
('صيانة مسجد الحي القديم', 'صيانة شاملة لمسجد الحي القديم ليشمل مصلى للنساء.', 2, 12000.00, 0.00, '../images/mosque.png', 1, 'inactive'),
('حفر بئر في قرية نائية', 'مشروع لتوفير المياه الصالحة للشرب لقرية تعاني من الجفاف.', 3, 35000.00, 0.00, '../images/well.png', 1, 'inactive');

-- Insert campaign requests (pending review)
INSERT INTO campaign_requests (user_id, title, description, category_id, goal_amount, image_path, status) VALUES 
(1, 'كفالة 25 يتيماً', 'مشروع لكفالة أيتام وتوفير احتياجاتهم الأساسية.', 4, 18000.00, '../images/orphan.png', 'pending'),
(1, 'دعم علاج مرضى الكلى', 'توفير جلسات غسيل كلى للمرضى غير القادرين.', 6, 22000.00, '../images/medical.png', 'pending'),
(1, 'إعانة 10 حجاج', 'توفير تكاليف الحج للأشخاص المتعسرين مادياً.', 5, 40000.00, '../images/hajj.png', 'pending');

-- Insert donations
INSERT INTO donations (user_id, campaign_id, amount) VALUES 
(1, 1, 3000.00),
(1, 3, 8000.00),
(1, 1, 6000.00),
(1, 5, 9000.00),
(1, 2, 5000.00),
(1, 4, 7000.00),
(1, 5, 4000.00);

-- Insert cart items
INSERT INTO cart_items (user_id, campaign_id, amount) VALUES 
(1, 1, 500.00),
(1, 2, 250.00),
(1, 2, 250.00),
(1, 3, 300.00);

-- Insert contact messages
INSERT INTO contact_messages (full_name, email, phone, message) VALUES 
('User', 'user@example.com', '0501234567', 'السلام عليكم، أود الاستفسار عن كيفية المشاركة كمتطوع في حملات سقيا الماء وتوزيع السلال الغذائية.');