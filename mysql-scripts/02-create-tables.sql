-- ========================================
-- SCRIPT 2: CREACIÓN DE TABLAS
-- Sistema de Gestión de Créditos - MySQL
-- ========================================

USE creditos_eider;

-- =========================
-- TABLA: roles
-- =========================
CREATE TABLE roles (
  id_rol INT PRIMARY KEY AUTO_INCREMENT,
  nombre_rol VARCHAR(20) NOT NULL UNIQUE COMMENT 'JEFE, COBRADOR, VENDEDOR',
  descripcion TEXT,
  permisos JSON COMMENT 'Define qué módulos puede ver cada rol',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Roles del sistema (JEFE, COBRADOR, VENDEDOR)';

-- =========================
-- TABLA: usuarios
-- =========================
CREATE TABLE usuarios (
  id_usuario INT PRIMARY KEY AUTO_INCREMENT,
  cedula VARCHAR(20) NOT NULL UNIQUE,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  telefono VARCHAR(20),
  email VARCHAR(100) UNIQUE,
  clave_hash VARCHAR(255) NOT NULL,
  id_rol INT NOT NULL,
  id_jefe_asociado INT COMMENT 'Jefe que supervisa a este usuario',
  porcentaje_comision DECIMAL(5,2) DEFAULT 10.00 COMMENT '% de comisión sobre cobros (solo para cobradores)',
  activo BOOLEAN DEFAULT TRUE,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_usuarios_rol FOREIGN KEY (id_rol) REFERENCES roles(id_rol),
  CONSTRAINT fk_usuarios_jefe FOREIGN KEY (id_jefe_asociado) REFERENCES usuarios(id_usuario),
  
  INDEX idx_usuarios_rol (id_rol),
  INDEX idx_usuarios_jefe (id_jefe_asociado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Usuarios del sistema (jefes, cobradores, vendedores)';

-- =========================
-- TABLA: pueblos
-- =========================
CREATE TABLE pueblos (
  id_pueblo INT PRIMARY KEY AUTO_INCREMENT,
  nombre_pueblo VARCHAR(50) NOT NULL UNIQUE,
  departamento VARCHAR(50),
  latitud DECIMAL(9,6),
  longitud DECIMAL(9,6),
  activo BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de pueblos/ciudades';

-- =========================
-- TABLA: rutas_cobro
-- =========================
CREATE TABLE rutas_cobro (
  id_ruta INT PRIMARY KEY AUTO_INCREMENT,
  nombre_ruta VARCHAR(50) NOT NULL,
  id_pueblo INT NOT NULL,
  id_cobrador_asignado INT,
  activa BOOLEAN DEFAULT TRUE,
  fecha_asignacion DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_rutas_pueblo FOREIGN KEY (id_pueblo) REFERENCES pueblos(id_pueblo),
  CONSTRAINT fk_rutas_cobrador FOREIGN KEY (id_cobrador_asignado) REFERENCES usuarios(id_usuario),
  
  INDEX idx_rutas_pueblo (id_pueblo),
  INDEX idx_rutas_cobrador (id_cobrador_asignado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Rutas de cobro asignadas a cobradores';

-- =========================
-- TABLA: deudores
-- =========================
CREATE TABLE deudores (
  id_deudor INT PRIMARY KEY AUTO_INCREMENT,
  codigo_deudor VARCHAR(20) NOT NULL UNIQUE,
  cedula VARCHAR(20) NOT NULL UNIQUE,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  direccion TEXT NOT NULL,
  latitud DECIMAL(9,6) COMMENT 'Coordenada GPS - latitud del domicilio',
  longitud DECIMAL(9,6) COMMENT 'Coordenada GPS - longitud del domicilio',
  telefono_principal VARCHAR(20) NOT NULL,
  id_ruta INT NOT NULL,
  dia_cobro_preferido TINYINT COMMENT '1-7 (Lunes-Domingo) o NULL',
  recordatorio_diario BOOLEAN DEFAULT TRUE COMMENT 'TRUE si no tiene día establecido',
  score_credito SMALLINT DEFAULT 100,
  id_creado_por INT COMMENT 'Usuario (Jefe) que creó este deudor',
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_deudores_ruta FOREIGN KEY (id_ruta) REFERENCES rutas_cobro(id_ruta),
  CONSTRAINT fk_deudores_creado_por FOREIGN KEY (id_creado_por) REFERENCES usuarios(id_usuario),
  
  INDEX idx_deudores_ruta (id_ruta),
  INDEX idx_deudores_creado_por (id_creado_por),
  INDEX idx_deudores_ubicacion (latitud, longitud) COMMENT 'Para búsquedas por proximidad'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de deudores (clientes con crédito)';

-- =========================
-- TABLA: tarjetas_deudor
-- =========================
CREATE TABLE tarjetas_deudor (
  id_tarjeta INT PRIMARY KEY AUTO_INCREMENT,
  codigo_tarjeta VARCHAR(20) NOT NULL UNIQUE,
  id_deudor INT NOT NULL,
  fecha_emision DATE NOT NULL,
  limite_credito DECIMAL(12,2),
  saldo_actual DECIMAL(12,2) DEFAULT 0,
  saldo_vencido DECIMAL(12,2) DEFAULT 0,
  estado_tarjeta VARCHAR(20) DEFAULT 'ACTIVA' COMMENT 'ACTIVA, BLOQUEADA, CANCELADA',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_tarjetas_deudor FOREIGN KEY (id_deudor) REFERENCES deudores(id_deudor),
  
  INDEX idx_deudor_estado (id_deudor, estado_tarjeta),
  INDEX idx_tarjetas_deudor (id_deudor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tarjetas de crédito de cada deudor';

-- =========================
-- TABLA: productos
-- =========================
CREATE TABLE productos (
  id_producto INT PRIMARY KEY AUTO_INCREMENT,
  codigo_producto VARCHAR(50) NOT NULL UNIQUE,
  nombre_producto VARCHAR(100) NOT NULL,
  descripcion TEXT,
  precio_venta DECIMAL(12,2) NOT NULL,
  activo BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de productos vendidos a crédito';

-- =========================
-- TABLA: ventas
-- =========================
CREATE TABLE ventas (
  id_venta INT PRIMARY KEY AUTO_INCREMENT,
  id_tarjeta INT NOT NULL,
  id_producto INT NOT NULL,
  id_vendedor INT NOT NULL,
  cantidad INT DEFAULT 1,
  monto_total DECIMAL(12,2) NOT NULL,
  fecha_venta DATE NOT NULL,
  estado_venta VARCHAR(20) DEFAULT 'ACTIVA' COMMENT 'ACTIVA, CANCELADA',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_ventas_tarjeta FOREIGN KEY (id_tarjeta) REFERENCES tarjetas_deudor(id_tarjeta),
  CONSTRAINT fk_ventas_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
  CONSTRAINT fk_ventas_vendedor FOREIGN KEY (id_vendedor) REFERENCES usuarios(id_usuario),
  
  INDEX idx_ventas_tarjeta (id_tarjeta),
  INDEX idx_ventas_producto (id_producto),
  INDEX idx_ventas_vendedor (id_vendedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de ventas a crédito';

-- =========================
-- TABLA: planes_pago
-- =========================
CREATE TABLE planes_pago (
  id_plan INT PRIMARY KEY AUTO_INCREMENT,
  id_venta INT NOT NULL UNIQUE,
  modalidad VARCHAR(20) COMMENT 'DIARIO, SEMANAL, QUINCENAL, MENSUAL',
  valor_cuota DECIMAL(12,2),
  cuotas_totales INT,
  estado_plan VARCHAR(20) DEFAULT 'ACTIVO' COMMENT 'ACTIVO, COMPLETADO, CANCELADO',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_planes_venta FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
  
  INDEX idx_planes_venta (id_venta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Planes de pago de cada venta';

-- =========================
-- TABLA: cuotas
-- =========================
CREATE TABLE cuotas (
  id_cuota INT PRIMARY KEY AUTO_INCREMENT,
  id_plan INT NOT NULL,
  numero_cuota INT NOT NULL,
  fecha_vencimiento DATE COMMENT 'Puede ser modificada por el cobrador',
  monto_cuota DECIMAL(12,2) COMMENT 'Puede variar entre cuotas',
  estado_cuota VARCHAR(20) DEFAULT 'PENDIENTE' COMMENT 'PENDIENTE, PAGADA, VENCIDA',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_cuotas_plan FOREIGN KEY (id_plan) REFERENCES planes_pago(id_plan),
  
  INDEX idx_cuotas_plan (id_plan),
  INDEX idx_cuotas_estado (estado_cuota)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cuotas individuales de cada plan de pago';

-- =========================
-- TABLA: pagos
-- =========================
CREATE TABLE pagos (
  id_pago INT PRIMARY KEY AUTO_INCREMENT,
  id_cuota INT NOT NULL,
  id_cobrador INT NOT NULL,
  monto_abonado DECIMAL(12,2) NOT NULL,
  fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  metodo_pago VARCHAR(20) COMMENT 'EFECTIVO, TRANSFERENCIA, etc.',
  comision_generada DECIMAL(12,2) COMMENT 'Calculado automáticamente según % del cobrador',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_pagos_cuota FOREIGN KEY (id_cuota) REFERENCES cuotas(id_cuota),
  CONSTRAINT fk_pagos_cobrador FOREIGN KEY (id_cobrador) REFERENCES usuarios(id_usuario),
  
  INDEX idx_pagos_cuota (id_cuota),
  INDEX idx_pagos_cobrador (id_cobrador),
  INDEX idx_pagos_fecha (fecha_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de pagos realizados por deudores';

-- =========================
-- TABLA: comisiones_cobradores
-- =========================
CREATE TABLE comisiones_cobradores (
  id_comision INT PRIMARY KEY AUTO_INCREMENT,
  id_cobrador INT NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  total_recaudado DECIMAL(12,2) NOT NULL COMMENT 'Suma de pagos en el período',
  total_comision DECIMAL(12,2) NOT NULL COMMENT 'Calculado según % del cobrador',
  estado_pago VARCHAR(20) DEFAULT 'PENDIENTE' COMMENT 'PENDIENTE, PAGADO',
  fecha_pago DATE COMMENT 'Fecha en que el jefe pagó la comisión',
  id_jefe_pagador INT COMMENT 'Jefe que realizó el pago',
  periodicidad VARCHAR(20) COMMENT 'SEMANAL, QUINCENAL, MENSUAL',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_comisiones_cobrador FOREIGN KEY (id_cobrador) REFERENCES usuarios(id_usuario),
  CONSTRAINT fk_comisiones_jefe FOREIGN KEY (id_jefe_pagador) REFERENCES usuarios(id_usuario),
  
  INDEX idx_cobrador_estado (id_cobrador, estado_pago),
  INDEX idx_comisiones_cobrador (id_cobrador),
  INDEX idx_comisiones_jefe (id_jefe_pagador),
  INDEX idx_comisiones_periodo (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Comisiones calculadas para cobradores';

-- =========================
-- MENSAJE FINAL
-- =========================
SELECT 'Todas las tablas creadas exitosamente' AS Status;