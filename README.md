# Sistema de Gestión de Créditos Eider

Sistema completo de gestión de créditos con Laravel (Backend API) y React (Frontend), con soporte para coordenadas GPS de deudores.

## 📋 Características Principales

✅ **Gestión de usuarios** con 3 roles: JEFE, COBRADOR, VENDEDOR  
✅ **Coordenadas GPS** para ubicación exacta de cada deudor  
✅ **Búsqueda de deudores cercanos** por proximidad geográfica  
✅ **Gestión de rutas de cobro** asignadas a cobradores  
✅ **Sistema de comisiones** automático para cobradores  
✅ **Control de pagos y cuotas** flexible  
✅ **Catálogo de productos** vendibles a crédito  
✅ **API REST** completa con autenticación Sanctum  

---

## 🚀 Instalación Local (Desarrollo)

### Requisitos Previos

- PHP 8.1 o superior
- Composer
- MySQL 8.0 o superior
- Node.js 18+ y npm
- Git

### Paso 1: Clonar el Proyecto

```bash
cd /var/www/
git clone <tu-repositorio> creditos-eider
cd creditos-eider
```

### Paso 2: Configurar Backend (Laravel)

```bash
# Instalar dependencias de PHP
composer install

# Copiar archivo de entorno
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Configurar base de datos en .env
nano .env
```

**Editar .env con tus credenciales:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=creditos_eider
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### Paso 3: Crear Base de Datos

```bash
# Opción 1: Ejecutar scripts SQL manualmente
mysql -u root -p < mysql-scripts/01-create-database.sql
mysql -u root -p < mysql-scripts/02-create-tables.sql
mysql -u root -p < mysql-scripts/03-insert-roles.sql
mysql -u root -p < mysql-scripts/04-seed-data.sql

# Opción 2: Usar migraciones de Laravel
php artisan migrate
php artisan db:seed
```

### Paso 4: Configurar Frontend (React)

```bash
cd resources/react-src

# Instalar dependencias
npm install

# Modo desarrollo
npm run dev
```

### Paso 5: Levantar Servidor Local

**Terminal 1 (Laravel):**
```bash
php artisan serve
# API disponible en: http://localhost:8000
```

**Terminal 2 (React):**
```bash
cd resources/react-src
npm run dev
# Frontend disponible en: http://localhost:5173
```

---

## 🌐 Deployment en Hostinger (Producción)

### Requisitos del Plan Hostinger

- **Plan:** KVM 1 (CO$ 20.900/mes)
- **Recursos:** 1 vCPU, 4GB RAM, 50GB NVMe, 4TB bandwidth
- **PHP:** 8.1 o superior
- **MySQL:** 8.0

### Paso 1: Preparar Backend

```bash
# En tu máquina local, compilar React
cd resources/react-src
npm run build

# Esto genera los archivos en: public/build
```

### Paso 2: Subir Archivos a Hostinger

**Usando FTP/SFTP:**

1. Conectar a Hostinger via FileZilla/WinSCP
2. Subir TODO el proyecto a `/home/tuusuario/public_html`
3. Asegurarte que la carpeta `public` de Laravel sea la raíz web

**Estructura en Hostinger:**

```
/home/tuusuario/
├── creditos-eider/              # Código Laravel fuera de public_html
│   ├── app/
│   ├── database/
│   ├── routes/
│   └── ...
└── public_html/                 # Apunta aquí (carpeta public de Laravel)
    ├── index.php
    ├── .htaccess
    └── build/                   # Archivos compilados de React
```

### Paso 3: Configurar Base de Datos en Hostinger

1. Ir al panel de Hostinger > MySQL Databases
2. Crear base de datos: `u123456789_creditos`
3. Crear usuario: `u123456789_admin`
4. Anotar las credenciales

**Ejecutar scripts SQL:**

```bash
# Conectar via phpMyAdmin o SSH
mysql -u u123456789_admin -p u123456789_creditos < 01-create-database.sql
mysql -u u123456789_admin -p u123456789_creditos < 02-create-tables.sql
mysql -u u123456789_admin -p u123456789_creditos < 03-insert-roles.sql
mysql -u u123456789_admin -p u123456789_creditos < 04-seed-data.sql
```

### Paso 4: Configurar .env en Producción

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_creditos
DB_USERNAME=u123456789_admin
DB_PASSWORD=TuPasswordSeguro123
```

### Paso 5: Optimizar para Producción

```bash
# En SSH de Hostinger
cd /home/tuusuario/creditos-eider

composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Permisos correctos
chmod -R 755 storage bootstrap/cache
chown -R tuusuario:tuusuario *
```

### Paso 6: Configurar .htaccess

Verificar que `/public_html/.htaccess` tenga:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ ../creditos-eider/public/$1 [L]
</IfModule>
```

---

## 👥 Usuarios de Prueba

Después de ejecutar el seed data:

| Rol | Email | Password |
|-----|-------|----------|
| **JEFE** | admin@creditos.com | admin123 |
| **COBRADOR** | cobrador1@creditos.com | cobrador123 |
| **COBRADOR** | cobrador2@creditos.com | cobrador123 |
| **VENDEDOR** | vendedor1@creditos.com | vendedor123 |
| **VENDEDOR** | vendedor2@creditos.com | vendedor123 |

---

## 📍 Uso de Coordenadas GPS

### Agregar Ubicación a un Deudor (API)

```javascript
// Endpoint: PUT /api/deudores/{id}/ubicacion

fetch('https://tuapi.com/api/deudores/1/ubicacion', {
  method: 'PUT',
  headers: {
    'Authorization': 'Bearer TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    latitud: 4.710989,
    longitud: -74.072092
  })
})
```

### Buscar Deudores Cercanos

```javascript
// Endpoint: POST /api/deudores/cercanos

fetch('https://tuapi.com/api/deudores/cercanos', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    latitud: 4.648682,
    longitud: -74.089890,
    radio_km: 5  // Buscar en un radio de 5km
  })
})
```

---

## 🔧 Mantenimiento

### Backups Automáticos

```bash
# Crear backup de base de datos
mysqldump -u usuario -p creditos_eider > backup_$(date +%Y%m%d).sql

# Restaurar backup
mysql -u usuario -p creditos_eider < backup_20240101.sql
```

### Logs

```bash
# Ver logs de Laravel
tail -f storage/logs/laravel.log

# Ver logs de MySQL
tail -f /var/log/mysql/error.log
```

---

## 📞 Soporte

Para dudas o problemas:
- Email: soporte@creditos.com
- Documentación completa: `/docs/`

---

## 📄 Licencia

Sistema propietario - Créditos Eider © 2024