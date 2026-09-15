# Manual de Despliegue en Hostinger (PHP + MySQL)
## TCS MOTRIZ - Plataforma de Mantenimiento y Telemetría

Este documento contiene la guía paso a paso para desplegar la plataforma técnica de **TCS Motriz** en tu plan de alojamiento de **Hostinger**.

---

### Paso 1: Subir los Archivos de la Aplicación

1. Inicia sesión en el panel de control **hPanel** de Hostinger (`https://hpanel.hostinger.com`).
2. Dirígete a **Sitios web** $\rightarrow$ Selecciona tu dominio (`servicio-tcsmotriz.com.mx` o tu dominio asignado) $\rightarrow$ **Administrador de Archivos**.
3. Navega al directorio raíz público:
   ```
   public_html/
   ```
4. Sube todo el contenido de la carpeta `app/`:
   - `.htaccess` (Asegúrate de marcar "Ver archivos ocultos" en el administrador).
   - `index.php`
   - `login.php`
   - `logout.php`
   - Carpetas `assets/`, `config/`, `core/`, `database/`, `logs/`, `modules/`.
5. Verifica los permisos de archivo en el servidor:
   - Carpetas en general: `0755`
   - Carpeta `logs/` y `database/`: `0775` (deben tener permisos de escritura para el usuario de PHP).

---

### Paso 2: Crear la Base de Datos MySQL en Hostinger

### Paso 2: Base de Datos MySQL en Hostinger (¡Ya Creada y Migrada!)

La base de datos en Hostinger ya se encuentra conectada, probada y migrada con las 13 tablas oficiales y datos iniciales:
- **Host**: `srv1264.hstgr.io` (o `localhost` / `193.203.166.105`)
- **Base de Datos**: `u666149057_TCS`
- **Usuario**: `u666149057_spartangray`
- **Tablas Verificadas (13)**: `matrices`, `sucursales`, `talleres`, `usuarios`, `equipos`, `ordenes_servicio`, `reportes_mantenimiento`, `reporte_consumibles`, `proveedores`, `inventario_refacciones`, `checklists_diarios`, `mensajes`, `audit_logs`.

---

### Paso 3: Configuración en `config/database.php`

El archivo `app/config/database.php` ya contiene la configuración oficial activa:
```php
define('DB_HOST', getenv('DB_HOST') ?: 'srv1264.hstgr.io');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'u666149057_TCS');
define('DB_USER', getenv('DB_USER') ?: 'u666149057_spartangray');
define('DB_PASS', getenv('DB_PASS') ?: 'p6Z@9DSFX_K7Pxb**-*-*');
define('DB_CHARSET', 'utf8mb4');
```
*Nota: `Database.php` intentará conectarse dinámicamente vía `srv1264.hstgr.io`, `localhost` o `193.203.166.105` de manera transparente.*

> [!TIP]
> **Modo Autónomo de Respaldo**:  
> Si por alguna razón la red externa se interrumpe temporalmente, el sistema activa automáticamente persistencia local en `database/store.json`, garantizando alta disponibilidad sin caídas de servicio.

---

### Paso 5: Configuración de Versión de PHP y Extensiones

1. En hPanel, busca la sección **Avanzado** $\rightarrow$ **Configuración de PHP**.
2. Selecciona **PHP 8.1**, **PHP 8.2** o **PHP 8.3** (recomendado).
3. En la pestaña **Extensiones de PHP**, verifica que estén activadas:
   - `pdo_mysql` (Indispensable para la base de datos)
   - `mbstring` (Soporte multilingüe UTF-8)
   - `json` (Procesamiento de matrices técnicas)
   - `curl` y `openssl` (Cifrado y seguridad perimetral)
4. En la pestaña **Opciones de PHP**, asegúrate de que:
   - `display_errors` esté **Desactivado** (Por seguridad OWASP).
   - `upload_max_filesize` sea al menos `16M`.

---

### Paso 6: Activar Certificado SSL (HTTPS) Gratuito

1. En hPanel, dirígete a **Seguridad** $\rightarrow$ **SSL**.
2. Haz clic en **Instalar SSL** (Let's Encrypt gratuito provisto por Hostinger).
3. Activa la opción **Forzar HTTPS**.  
   *Nota: El archivo `.htaccess` provisto ya incluye reglas automáticas para redirigir todo el tráfico HTTP a HTTPS seguro.*

---

### Credenciales de Acceso Iniciales

| Rol | Correo Electrónico | Contraseña Inicial |
| :--- | :--- | :--- |
| **Administrador General** | `fernando.ruiz@servicio-tcsmotriz.com.mx` | `TCS@2026!` |
| **Soporte Técnico** | `soporte@servicio-tcsmotriz.com.mx` | `TCS@2026!` |
| **Técnico Especialista** | `hector.morales@servicio-tcsmotriz.com.mx` | `TCS@2026!` |
| **Cliente (Ford Interlomas)** | `cmendoza@fordinterlomas.com` | `TCS@2026!` |
| **Cliente (Nissan Santa Fe)** | `elena.torres@nissansantafe.com` | `TCS@2026!` |
| **Cliente (Taller Ramírez)** | `contacto@taller-ramirez.com` | `TCS@2026!` |
