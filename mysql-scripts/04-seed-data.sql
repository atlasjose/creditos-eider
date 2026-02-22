-- ========================================
-- SCRIPT 4: DATOS DE PRUEBA (SEED DATA)
-- Sistema de Gestión de Créditos - MySQL
-- ========================================

USE creditos_eider;

-- =========================
-- USUARIO JEFE (ADMINISTRADOR)
-- =========================
-- Contraseña: admin123 (hasheada con bcrypt)
INSERT INTO usuarios (cedula, nombres, apellidos, telefono, email, clave_hash, id_rol, activo)
VALUES 
('1234567890', 'Carlos', 'Rodríguez', '3001234567', 'admin@creditos.com', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 1, TRUE);

-- =========================
-- USUARIOS COBRADORES
-- =========================
-- Contraseña: cobrador123 para todos
INSERT INTO usuarios (cedula, nombres, apellidos, telefono, email, clave_hash, id_rol, id_jefe_asociado, porcentaje_comision, activo)
VALUES 
('9876543210', 'Juan', 'Pérez', '3009876543', 'cobrador1@creditos.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 2, 1, 10.00, TRUE),
 
('5555555555', 'María', 'González', '3005555555', 'cobrador2@creditos.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 2, 1, 10.00, TRUE);

-- =========================
-- USUARIOS VENDEDORES
-- =========================
-- Contraseña: vendedor123 para todos
INSERT INTO usuarios (cedula, nombres, apellidos, telefono, email, clave_hash, id_rol, id_jefe_asociado, activo)
VALUES 
('1111111111', 'Pedro', 'Martínez', '3001111111', 'vendedor1@creditos.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 3, 1, TRUE),
 
('2222222222', 'Ana', 'López', '3002222222', 'vendedor2@creditos.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 3, 1, TRUE);

-- =========================
-- PUEBLOS
-- =========================
INSERT INTO pueblos (nombre_pueblo, departamento, latitud, longitud, activo) VALUES
('Bogotá', 'Cundinamarca', 4.710989, -74.072092, TRUE),
('Medellín', 'Antioquia', 6.244203, -75.581212, TRUE),
('Cali', 'Valle del Cauca', 3.451647, -76.531985, TRUE),
('Barranquilla', 'Atlántico', 10.963889, -74.796387, TRUE),
('Cartagena', 'Bolívar', 10.391049, -75.479426, TRUE);

-- =========================
-- RUTAS DE COBRO
-- =========================
INSERT INTO rutas_cobro (nombre_ruta, id_pueblo, id_cobrador_asignado, activa, fecha_asignacion) VALUES
('Ruta Centro Bogotá', 1, 2, TRUE, CURDATE()),
('Ruta Norte Bogotá', 1, 3, TRUE, CURDATE()),
('Ruta Centro Medellín', 2, 2, TRUE, CURDATE());

-- =========================
-- DEUDORES
-- =========================
INSERT INTO deudores (codigo_deudor, cedula, nombres, apellidos, direccion, latitud, longitud, telefono_principal, id_ruta, dia_cobro_preferido, score_credito, id_creado_por) VALUES
('DEU-001', '80001234', 'Luis', 'Ramírez', 'Calle 45 #12-34, Bogotá', 4.648682, -74.089890, '3101234567', 1, 1, 100, 1),
('DEU-002', '80005678', 'Sandra', 'Torres', 'Carrera 7 #80-45, Bogotá', 4.668990, -74.057123, '3105678901', 1, 3, 100, 1),
('DEU-003', '80009999', 'Roberto', 'Díaz', 'Avenida 68 #45-90, Bogotá', 4.652345, -74.098765, '3109876543', 2, 5, 100, 1);

-- =========================
-- TARJETAS
-- =========================
INSERT INTO tarjetas_deudor (codigo_tarjeta, id_deudor, fecha_emision, limite_credito, saldo_actual, estado_tarjeta) VALUES
('CARD-001', 1, CURDATE(), 1000000.00, 0.00, 'ACTIVA'),
('CARD-002', 2, CURDATE(), 800000.00, 0.00, 'ACTIVA'),
('CARD-003', 3, CURDATE(), 1500000.00, 0.00, 'ACTIVA');

-- =========================
-- PRODUCTOS
-- =========================
INSERT INTO productos (codigo_producto, nombre_producto, descripcion, precio_venta, activo) VALUES
('PROD-001', 'Celular Samsung A14', 'Smartphone Samsung Galaxy A14 64GB', 450000.00, TRUE),
('PROD-002', 'Laptop HP 15', 'Laptop HP 15.6" Core i3 8GB RAM', 1200000.00, TRUE),
('PROD-003', 'Nevera Haceb 200L', 'Nevera Haceb No Frost 200 Litros', 850000.00, TRUE),
('PROD-004', 'Televisor LG 43"', 'Smart TV LG 43" Full HD', 950000.00, TRUE),
('PROD-005', 'Moto Yamaha FZ', 'Motocicleta Yamaha FZ 150cc', 6500000.00, TRUE);

-- =========================
-- VENTAS DE EJEMPLO
-- =========================
INSERT INTO ventas (id_tarjeta, id_producto, id_vendedor, cantidad, monto_total, fecha_venta, estado_venta) VALUES
(1, 1, 4, 1, 450000.00, CURDATE(), 'ACTIVA'),
(2, 3, 5, 1, 850000.00, CURDATE(), 'ACTIVA');

-- =========================
-- PLANES DE PAGO
-- =========================
INSERT INTO planes_pago (id_venta, modalidad, valor_cuota, cuotas_totales, estado_plan) VALUES
(1, 'SEMANAL', 50000.00, 9, 'ACTIVO'),
(2, 'QUINCENAL', 100000.00, 9, 'ACTIVO');

-- =========================
-- CUOTAS (9 cuotas para cada plan)
-- =========================
-- Plan 1 (Semanal)
INSERT INTO cuotas (id_plan, numero_cuota, fecha_vencimiento, monto_cuota, estado_cuota) VALUES
(1, 1, DATE_ADD(CURDATE(), INTERVAL 1 WEEK), 50000.00, 'PENDIENTE'),
(1, 2, DATE_ADD(CURDATE(), INTERVAL 2 WEEK), 50000.00, 'PENDIENTE'),
(1, 3, DATE_ADD(CURDATE(), INTERVAL 3 WEEK), 50000.00, 'PENDIENTE'),
(1, 4, DATE_ADD(CURDATE(), INTERVAL 4 WEEK), 50000.00, 'PENDIENTE'),
(1, 5, DATE_ADD(CURDATE(), INTERVAL 5 WEEK), 50000.00, 'PENDIENTE'),
(1, 6, DATE_ADD(CURDATE(), INTERVAL 6 WEEK), 50000.00, 'PENDIENTE'),
(1, 7, DATE_ADD(CURDATE(), INTERVAL 7 WEEK), 50000.00, 'PENDIENTE'),
(1, 8, DATE_ADD(CURDATE(), INTERVAL 8 WEEK), 50000.00, 'PENDIENTE'),
(1, 9, DATE_ADD(CURDATE(), INTERVAL 9 WEEK), 50000.00, 'PENDIENTE');

-- Plan 2 (Quincenal)
INSERT INTO cuotas (id_plan, numero_cuota, fecha_vencimiento, monto_cuota, estado_cuota) VALUES
(2, 1, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 100000.00, 'PENDIENTE'),
(2, 2, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 100000.00, 'PENDIENTE'),
(2, 3, DATE_ADD(CURDATE(), INTERVAL 45 DAY), 100000.00, 'PENDIENTE'),
(2, 4, DATE_ADD(CURDATE(), INTERVAL 60 DAY), 100000.00, 'PENDIENTE'),
(2, 5, DATE_ADD(CURDATE(), INTERVAL 75 DAY), 100000.00, 'PENDIENTE'),
(2, 6, DATE_ADD(CURDATE(), INTERVAL 90 DAY), 100000.00, 'PENDIENTE'),
(2, 7, DATE_ADD(CURDATE(), INTERVAL 105 DAY), 100000.00, 'PENDIENTE'),
(2, 8, DATE_ADD(CURDATE(), INTERVAL 120 DAY), 100000.00, 'PENDIENTE'),
(2, 9, DATE_ADD(CURDATE(), INTERVAL 135 DAY), 100000.00, 'PENDIENTE');

-- =========================
-- MENSAJE FINAL
-- =========================
SELECT 'Datos de prueba insertados exitosamente' AS Status;
SELECT '========================================' AS '';
SELECT 'USUARIOS DE PRUEBA:' AS '';
SELECT 'Admin: admin@creditos.com / admin123' AS '';
SELECT 'Cobrador1: cobrador1@creditos.com / cobrador123' AS '';
SELECT 'Cobrador2: cobrador2@creditos.com / cobrador123' AS '';
SELECT 'Vendedor1: vendedor1@creditos.com / vendedor123' AS '';
SELECT 'Vendedor2: vendedor2@creditos.com / vendedor123' AS '';
SELECT '========================================' AS '';