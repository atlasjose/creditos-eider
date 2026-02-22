-- ========================================
-- SCRIPT 1: CREACIÓN DE BASE DE DATOS
-- Sistema de Gestión de Créditos - MySQL
-- ========================================

-- Eliminar base de datos si existe (¡CUIDADO EN PRODUCCIÓN!)
DROP DATABASE IF EXISTS creditos_eider;

-- Crear base de datos con codificación UTF-8
CREATE DATABASE creditos_eider
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE creditos_eider;

-- Mensaje de confirmación
SELECT 'Base de datos creada exitosamente' AS Status;