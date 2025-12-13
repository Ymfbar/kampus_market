-- CREATE DATABASE
CREATE DATABASE IF NOT EXISTS kampus_market
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE kampus_market;


-- TABLE: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLE: items
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_barang VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    harga INT DEFAULT 0,
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- TABLE: messages (FINAL)
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    item_id INT DEFAULT NULL,
    pesan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL
);

-- EXTRA INDEX (buat performa chat)
ALTER TABLE messages 
ADD INDEX idx_chat_pair (sender_id, receiver_id);

-- OPTIONAL: fitur "pesan sudah dibaca"
ALTER TABLE messages 
ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER created_at;

-- NOTIF Pesan Baru
ALTER TABLE messages 
ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER pesan;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL
);

INSERT INTO categories (nama_kategori) VALUES
('Elektronik'),
('Fashion'),
('Aksesoris'),
('Kendaraan'),
('Alat Rumah Tangga'),
('Hobi'),
('Lainnya');

ALTER TABLE items
ADD COLUMN kategori_id INT AFTER user_id,
ADD FOREIGN KEY (kategori_id) REFERENCES categories(id);


-- SAMPLE ADMIN USER
-- email: admin@kampus.test
-- pass: admin123
INSERT INTO users (nama, email, password, role) VALUES (
    'Admin',
    'admin@kampus.test',
    '$2y$10$u8L2bWzQ2hQ1cZJ6/6yBDe0gX2c0ZLJ2Y/4s2kz1oQ1y2Zg3kX7pO',
    'admin'
);
