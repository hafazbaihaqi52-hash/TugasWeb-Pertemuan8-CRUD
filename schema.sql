-- =====================================================
-- schema.sql — CRUD Inventaris (Tugas Rutin 8)
-- DDL + seed data | Normalisasi 3NF
-- =====================================================
CREATE DATABASE IF NOT EXISTS inventaris_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventaris_db;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS categories;

-- 1) categories
CREATE TABLE categories (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 2) suppliers
CREATE TABLE suppliers (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(100) NOT NULL,
  phone VARCHAR(20)
) ENGINE=InnoDB;

-- 3) products (FK ke categories & suppliers — sisi "many")
CREATE TABLE products (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(150)  NOT NULL,
  category_id INT           NOT NULL,
  supplier_id INT           NOT NULL,
  price       DECIMAL(12,2) NOT NULL,
  stock       INT           NOT NULL DEFAULT 0,
  created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id)
    REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_products_supplier FOREIGN KEY (supplier_id)
    REFERENCES suppliers(id)  ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_products_name (name)
) ENGINE=InnoDB;

-- 4) activity_logs (BONUS: dicatat via transaction saat delete)
CREATE TABLE activity_logs (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  action       VARCHAR(20)  NOT NULL,
  product_id   INT          NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------- SEED DATA ----------------------
INSERT INTO categories (name) VALUES
  ('Aksesoris'), ('Laptop'), ('Display'), ('Penyimpanan'), ('Jaringan');

INSERT INTO suppliers (name, phone) VALUES
  ('PT Maju Jaya',        '0812-1111-2222'),
  ('CV Teknologi Nusantara','0813-3333-4444'),
  ('PT Sumber Digital',   '0821-5555-6666'),
  ('UD Berkah Komputer',  '0852-7777-8888'),
  ('PT Global Elektronik','0877-9999-0000');

INSERT INTO products (name, category_id, supplier_id, price, stock) VALUES
  ('Laptop Asus Vivobook 14', 2, 2, 8500000, 10),
  ('Mouse Wireless Logitech', 1, 1,  150000, 25),
  ('Keyboard Mechanical',     1, 1,  450000, 15),
  ('Monitor LED 24 inch',     3, 3, 1800000,  8),
  ('SSD NVMe 512GB',          4, 4,  750000, 20),
  ('Router WiFi 6 TP-Link',   5, 5,  620000, 12),
  ('Flashdisk 64GB',          4, 4,   85000, 50);
