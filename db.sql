-- 1. Tabel: categories
CREATE TABLE categories (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Tabel: users
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE, -- Diasumsikan email harus unik
    no_telp VARCHAR(20) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Tabel: items
CREATE TABLE items (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    kategori_id INT(11) DEFAULT NULL,
    nama_barang VARCHAR(255) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    harga INT(11) NOT NULL DEFAULT 0,
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    -- Mendefinisikan Foreign Key ke tabel users
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    -- Mendefinisikan Foreign Key ke tabel categories
    FOREIGN KEY (kategori_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE
    -- ON DELETE SET NULL digunakan karena kategori_id diizinkan NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Tabel: messages
CREATE TABLE messages (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sender_id INT(11) NOT NULL,
    receiver_id INT(11) NOT NULL,
    item_id INT(11) DEFAULT NULL,
    pesan TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    is_read TINYINT(1) DEFAULT 0,
    -- Mendefinisikan Foreign Key ke tabel users (sender)
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    -- Mendefinisikan Foreign Key ke tabel users (receiver)
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    -- Mendefinisikan Foreign Key ke tabel items
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL ON UPDATE CASCADE
    -- ON DELETE SET NULL digunakan karena item_id diizinkan NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;