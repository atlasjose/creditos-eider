CREATE TABLE `roles` (
  `id_rol` int PRIMARY KEY,
  `nombre_rol` varchar(20) UNIQUE NOT NULL COMMENT 'JEFE, COBRADOR, VENDEDOR',
  `descripcion` text,
  `permisos` json COMMENT 'Define qué módulos puede ver cada rol'
);

CREATE TABLE `usuarios` (
  `id_usuario` int PRIMARY KEY AUTO_INCREMENT,
  `cedula` varchar(20) UNIQUE NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `telefono` varchar(20),
  `email` varchar(100) UNIQUE,
  `clave_hash` varchar(255) NOT NULL,
  `id_rol` int NOT NULL,
  `id_jefe_asociado` int COMMENT 'Jefe que supervisa a este usuario',
  `porcentaje_comision` decimal(5,2) DEFAULT 10 COMMENT '% de comisión sobre cobros (solo para cobradores)',
  `activo` boolean DEFAULT true,
  `fecha_creacion` timestamp DEFAULT null
);

CREATE TABLE `pueblos` (
  `id_pueblo` int PRIMARY KEY,
  `nombre_pueblo` varchar(50) UNIQUE NOT NULL,
  `departamento` varchar(50),
  `latitud` decimal(9,6),
  `longitud` decimal(9,6),
  `activo` boolean DEFAULT true
);

CREATE TABLE `rutas_cobro` (
  `id_ruta` int PRIMARY KEY AUTO_INCREMENT,
  `nombre_ruta` varchar(50) NOT NULL,
  `id_pueblo` int NOT NULL,
  `id_cobrador_asignado` int,
  `activa` boolean DEFAULT true,
  `fecha_asignacion` date DEFAULT null
);

CREATE TABLE `deudores` (
  `id_deudor` int PRIMARY KEY AUTO_INCREMENT,
  `codigo_deudor` varchar(20) UNIQUE NOT NULL,
  `cedula` varchar(20) UNIQUE NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `direccion` text NOT NULL,
  `latitud` decimal(9,6) COMMENT 'Coordenada geográfica (latitud) del domicilio del deudor',
  `longitud` decimal(9,6) COMMENT 'Coordenada geográfica (longitud) del domicilio del deudor',
  `telefono_principal` varchar(20) NOT NULL,
  `id_ruta` int NOT NULL,
  `dia_cobro_preferido` int COMMENT '1-7 (Lunes-Domingo) o NULL si no está establecido',
  `recordatorio_diario` boolean DEFAULT true COMMENT 'TRUE si no tiene día establecido',
  `score_credito` smallint DEFAULT 100,
  `id_creado_por` int COMMENT 'Usuario (Jefe) que creó este deudor',
  `fecha_creacion` timestamp DEFAULT null
);

CREATE TABLE `tarjetas_deudor` (
  `id_tarjeta` int PRIMARY KEY AUTO_INCREMENT,
  `codigo_tarjeta` varchar(20) UNIQUE NOT NULL,
  `id_deudor` int NOT NULL,
  `fecha_emision` date NOT NULL,
  `limite_credito` decimal(12,2),
  `saldo_actual` decimal(12,2) DEFAULT 0,
  `saldo_vencido` decimal(12,2) DEFAULT 0,
  `estado_tarjeta` varchar(20) DEFAULT 'ACTIVA'
);

CREATE TABLE `productos` (
  `id_producto` int PRIMARY KEY AUTO_INCREMENT,
  `codigo_producto` varchar(50) UNIQUE NOT NULL,
  `nombre_producto` varchar(100) NOT NULL,
  `descripcion` text,
  `precio_venta` decimal(12,2) NOT NULL,
  `activo` boolean DEFAULT true
);

CREATE TABLE `ventas` (
  `id_venta` int PRIMARY KEY AUTO_INCREMENT,
  `id_tarjeta` int NOT NULL,
  `id_producto` int NOT NULL,
  `id_vendedor` int NOT NULL,
  `cantidad` int DEFAULT 1,
  `monto_total` decimal(12,2) NOT NULL,
  `fecha_venta` date NOT NULL,
  `estado_venta` varchar(20) DEFAULT 'ACTIVA'
);

CREATE TABLE `planes_pago` (
  `id_plan` int PRIMARY KEY AUTO_INCREMENT,
  `id_venta` int UNIQUE NOT NULL,
  `modalidad` varchar(20) COMMENT 'DIARIO, SEMANAL, QUINCENAL, MENSUAL',
  `valor_cuota` decimal(12,2),
  `cuotas_totales` int,
  `estado_plan` varchar(20) DEFAULT 'ACTIVO'
);

CREATE TABLE `cuotas` (
  `id_cuota` int PRIMARY KEY AUTO_INCREMENT,
  `id_plan` int NOT NULL,
  `numero_cuota` int NOT NULL,
  `fecha_vencimiento` date COMMENT 'Puede ser modificada por el cobrador',
  `monto_cuota` decimal(12,2) COMMENT 'Puede variar entre cuotas',
  `estado_cuota` varchar(20) DEFAULT 'PENDIENTE' COMMENT 'PENDIENTE, PAGADA, VENCIDA'
);

CREATE TABLE `pagos` (
  `id_pago` int PRIMARY KEY AUTO_INCREMENT,
  `id_cuota` int NOT NULL,
  `id_cobrador` int NOT NULL,
  `monto_abonado` decimal(12,2) NOT NULL,
  `fecha_pago` timestamp DEFAULT null,
  `metodo_pago` varchar(20),
  `comision_generada` decimal(12,2) COMMENT 'Se calcula como monto_abonado * (porcentaje_comision del cobrador / 100)'
);

CREATE TABLE `comisiones_cobradores` (
  `id_comision` int PRIMARY KEY AUTO_INCREMENT,
  `id_cobrador` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `total_recaudado` decimal(12,2) NOT NULL COMMENT 'Suma de pagos en el período',
  `total_comision` decimal(12,2) NOT NULL COMMENT 'Calculado según el porcentaje del cobrador',
  `estado_pago` varchar(20) DEFAULT 'PENDIENTE' COMMENT 'PENDIENTE, PAGADO',
  `fecha_pago` date COMMENT 'Fecha en que el jefe pagó la comisión',
  `id_jefe_pagador` int COMMENT 'Jefe que realizó el pago',
  `periodicidad` varchar(20) COMMENT 'SEMANAL, QUINCENAL, MENSUAL'
);

CREATE INDEX `idx_usuarios_rol` ON `usuarios` (`id_rol`);

CREATE INDEX `idx_usuarios_jefe` ON `usuarios` (`id_jefe_asociado`);

CREATE INDEX `idx_rutas_pueblo` ON `rutas_cobro` (`id_pueblo`);

CREATE INDEX `idx_rutas_cobrador` ON `rutas_cobro` (`id_cobrador_asignado`);

CREATE INDEX `idx_deudores_ruta` ON `deudores` (`id_ruta`);

CREATE INDEX `idx_deudores_creado_por` ON `deudores` (`id_creado_por`);

CREATE INDEX `idx_deudores_ubicacion` ON `deudores` (`latitud`, `longitud`);

CREATE INDEX `idx_deudor_estado` ON `tarjetas_deudor` (`id_deudor`, `estado_tarjeta`);

CREATE INDEX `idx_tarjetas_deudor` ON `tarjetas_deudor` (`id_deudor`);

CREATE INDEX `idx_ventas_tarjeta` ON `ventas` (`id_tarjeta`);

CREATE INDEX `idx_ventas_producto` ON `ventas` (`id_producto`);

CREATE INDEX `idx_ventas_vendedor` ON `ventas` (`id_vendedor`);

CREATE INDEX `idx_planes_venta` ON `planes_pago` (`id_venta`);

CREATE INDEX `idx_cuotas_plan` ON `cuotas` (`id_plan`);

CREATE INDEX `idx_cuotas_estado` ON `cuotas` (`estado_cuota`);

CREATE INDEX `idx_pagos_cuota` ON `pagos` (`id_cuota`);

CREATE INDEX `idx_pagos_cobrador` ON `pagos` (`id_cobrador`);

CREATE INDEX `idx_pagos_fecha` ON `pagos` (`fecha_pago`);

CREATE INDEX `idx_cobrador_estado` ON `comisiones_cobradores` (`id_cobrador`, `estado_pago`);

CREATE INDEX `idx_comisiones_cobrador` ON `comisiones_cobradores` (`id_cobrador`);

CREATE INDEX `idx_comisiones_jefe` ON `comisiones_cobradores` (`id_jefe_pagador`);

CREATE INDEX `idx_comisiones_periodo` ON `comisiones_cobradores` (`fecha_inicio`, `fecha_fin`);

ALTER TABLE `usuarios` ADD FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

ALTER TABLE `usuarios` ADD FOREIGN KEY (`id_jefe_asociado`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `rutas_cobro` ADD FOREIGN KEY (`id_pueblo`) REFERENCES `pueblos` (`id_pueblo`);

ALTER TABLE `rutas_cobro` ADD FOREIGN KEY (`id_cobrador_asignado`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `deudores` ADD FOREIGN KEY (`id_ruta`) REFERENCES `rutas_cobro` (`id_ruta`);

ALTER TABLE `deudores` ADD FOREIGN KEY (`id_creado_por`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `tarjetas_deudor` ADD FOREIGN KEY (`id_deudor`) REFERENCES `deudores` (`id_deudor`);

ALTER TABLE `ventas` ADD FOREIGN KEY (`id_tarjeta`) REFERENCES `tarjetas_deudor` (`id_tarjeta`);

ALTER TABLE `ventas` ADD FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

ALTER TABLE `ventas` ADD FOREIGN KEY (`id_vendedor`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `planes_pago` ADD FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`);

ALTER TABLE `cuotas` ADD FOREIGN KEY (`id_plan`) REFERENCES `planes_pago` (`id_plan`);

ALTER TABLE `pagos` ADD FOREIGN KEY (`id_cuota`) REFERENCES `cuotas` (`id_cuota`);

ALTER TABLE `pagos` ADD FOREIGN KEY (`id_cobrador`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `comisiones_cobradores` ADD FOREIGN KEY (`id_cobrador`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `comisiones_cobradores` ADD FOREIGN KEY (`id_jefe_pagador`) REFERENCES `usuarios` (`id_usuario`);
