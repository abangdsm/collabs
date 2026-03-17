CREATE DATABASE IF NOT EXISTS collabs;
USE collabs;

-- Tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    last_login DATETIME NULL,
    total_duration INT DEFAULT 0, -- dalam detik
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel tasks (Judul Tugas)
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel subtasks (Daftar Tugas)
CREATE TABLE subtasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    judul_sub VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    skill_needed VARCHAR(255),
    priority ENUM('high', 'medium', 'low') DEFAULT 'medium',
    deadline DATE,
    status ENUM('proses', 'selesai', 'evaluasi') DEFAULT 'proses',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_archived TINYINT(1) DEFAULT 0,
    urutan INT DEFAULT 0,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel comments
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subtask_id INT NOT NULL,
    user_id INT NOT NULL,
    komentar TEXT,
    link_attachment VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subtask_id) REFERENCES subtasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel activity_logs
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Index untuk performa
CREATE INDEX idx_status ON subtasks(status);
CREATE INDEX idx_priority ON subtasks(priority);
CREATE INDEX idx_deadline ON subtasks(deadline);
CREATE INDEX idx_archived ON subtasks(is_archived);
CREATE INDEX idx_urutan ON subtasks(urutan);