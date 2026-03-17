# 🤝 Collabs - Platform Kolaborasi Tim Modern

<div align="center">
  
  ![Collabs Logo](https://img.shields.io/badge/Collabs-v1.0.0-blue?style=for-the-badge)
  ![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
  ![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
  ![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white)
  ![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)
  
  <h3>✨ Kelola Tugas, Pantau Progress, dan Berkolaborasi dengan Tim Secara Efektif ✨</h3>
  
  [![Demo](https://img.shields.io/badge/Demo-Live-brightgreen?style=for-the-badge)](https://collabs.demo.com)
  [![Documentation](https://img.shields.io/badge/Docs-Read-blue?style=for-the-badge)](docs/)
  [![License](https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge)](LICENSE)
  
</div>

---

## 📸 Screenshot

<div align="center">
  <img src="https://drive.google.com/file/d/1ThGoRRUIFh3U4opRJotfeUziz7OytArn/view?usp=sharing" alt="Dashboard Preview" width="80%" style="border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
</div>

---

## 🚀 Fitur Unggulan

### 👥 **Manajemen Tim**
- ✅ Multi-level role (Admin & Member)
- ✅ Manajemen user untuk admin
- ✅ Activity log setiap user
- ✅ Keamanan berbasis role

### 📋 **Task Management**
- ✅ CRUD Tasks & Subtasks
- ✅ Status unik: Proses → Selesai → Evaluasi
- ✅ Auto-evaluate deadline (lewat otomatis evaluasi)
- ✅ Prioritas tugas (High, Medium, Low)
- ✅ Deadline dengan visual warning (hijau, kuning, merah)
- ✅ Drag & Drop urutan subtasks

### 💬 **Kolaborasi Real-time**
- ✅ Komentar & balasan (threaded comments)
- ✅ Edit & hapus komentar
- ✅ Link attachment di komentar
- ✅ In-App Notifications (polling 30 detik)
- ✅ Halaman semua notifikasi dengan filter

### 🔍 **Filter & Pencarian**
- ✅ Filter berdasarkan status, prioritas, deadline
- ✅ Search real-time (auto-search saat mengetik)
- ✅ Tasks & Subtasks terintegrasi

### 📊 **Dashboard Interaktif**
- ✅ Tampilan tasks per project
- ✅ Subtasks dimuat via AJAX
- ✅ Loading states yang halus
- ✅ Responsive design (mobile friendly)

---

## 🛠️ Teknologi

| Bagian | Teknologi |
|--------|-----------|
| **Backend** | PHP Native (tanpa framework) |
| **Database** | MySQL, HeidiSQL |
| **Frontend** | HTML5, CSS3, JavaScript |
| **Framework CSS** | Bootstrap 5 |
| **Library JS** | jQuery 3.6, jQuery UI |
| **AJAX** | jQuery AJAX |
| **Environment** | Laragon (Local Development) |
| **Version Control** | Git, GitHub |

---

## 📦 Struktur Database

```sql
-- Users
users(id, username, email, password, role, last_login, total_duration, created_at)

-- Tasks (Judul Tugas)
tasks(id, judul, created_by, created_at)

-- Subtasks (Daftar Tugas)
subtasks(id, task_id, judul_sub, deskripsi, priority, deadline, status, created_by, created_at, is_archived, urutan)

-- Comments
comments(id, subtask_id, user_id, komentar, link_attachment, parent_id, created_at)

-- Notifications
notifications(id, user_id, message, type, is_read, link, created_at)

-- Activity Logs
activity_logs(id, user_id, action, ip_address, created_at)