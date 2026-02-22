-- ========================================
-- SCRIPT 3: INSERCIÓN DE ROLES
-- Sistema de Gestión de Créditos - MySQL
-- ========================================

USE creditos_eider;

-- =========================
-- INSERTAR ROLES DEL SISTEMA
-- =========================

INSERT INTO roles (nombre_rol, descripcion, permisos) VALUES
('JEFE', 'Administrador principal - Acceso completo al sistema', JSON_OBJECT(
  'dashboard', true,
  'usuarios', true,
  'deudores', true,
  'tarjetas', true,
  'ventas', true,
  'pagos', true,
  'comisiones', true,
  'reportes', true,
  'rutas', true,
  'productos', true,
  'configuracion', true
)),

('COBRADOR', 'Usuario encargado de realizar cobros en rutas asignadas', JSON_OBJECT(
  'dashboard', true,
  'deudores', true,
  'tarjetas', true,
  'pagos', true,
  'comisiones', true,
  'rutas', true,
  'reportes', false,
  'usuarios', false,
  'ventas', false,
  'productos', false,
  'configuracion', false
)),

('VENDEDOR', 'Usuario encargado de realizar ventas a crédito', JSON_OBJECT(
  'dashboard', true,
  'deudores', true,
  'tarjetas', true,
  'ventas', true,
  'productos', true,
  'reportes', false,
  'usuarios', false,
  'pagos', false,
  'comisiones', false,
  'rutas', false,
  'configuracion', false
));

-- =========================
-- VERIFICACIÓN
-- =========================
SELECT * FROM roles;

SELECT 'Roles insertados exitosamente' AS Status;