# Especificación de Seguridad Perimetral, OWASP Top 10 e ISO 27001
## Plataforma de Mantenimiento y Telemetría Industrial TCS Motriz

---

### 1. Resumen de Seguridad Perimetral

La aplicación web de **TCS Motriz** ha sido diseñada e implementada bajo una estrategia de **Defensa en Profundidad (Defense in Depth)**, garantizando que el entorno de producción en **Hostinger** mantenga aislamiento de datos, control perimetral estricto y trazabilidad inmutable.

```
       [ Internet / Clientes / Técnicos / Administrador ]
                               │
                               ▼
        ┌──────────────────────────────────────────────┐
        │       CAPA 1: SEGURIDAD PERIMETRAL           │
        │  • Reglas .htaccess (Apache)                 │
        │  • Cabeceras HTTP (HSTS, CSP, X-Frame)       │
        │  • Bloqueo de extensiones sensibles          │
        │  • Forzado SSL/HTTPS y Rate Limiting         │
        └──────────────────────┬───────────────────────┘
                               │
                               ▼
        ┌──────────────────────────────────────────────┐
        │       CAPA 2: CONTROL DE ACCESO (RBAC)       │
        │  • Auth.php & Security.php                   │
        │  • Validación anti-IDOR por Sede             │
        │  • Tokens CSRF en todas las mutaciones       │
        │  • Hashes de contraseñas Bcrypt (Cost 12)    │
        └──────────────────────┬───────────────────────┘
                               │
                               ▼
        ┌──────────────────────────────────────────────┐
        │       CAPA 3: PERSISTENCIA Y AUDITORÍA       │
        │  • Consultas 100% preparadas (PDO)           │
        │  • Bitácora ISO 27001 A.12.4 inmutable       │
        │  • Captura silenciosa de errores PHP         │
        │  • MySQL Hostinger / Fallback Cifrado        │
        └──────────────────────────────────────────────┘
```

---

### 2. Matriz de Cumplimiento OWASP Top 10 (2021)

| Riesgo OWASP | Vector de Amenaza Mitigado | Implementación en TCS Motriz | Archivo / Componente |
| :--- | :--- | :--- | :--- |
| **A01: Broken Access Control** | Acceso cruzado no autorizado a equipos de otros talleres (IDOR) y escalamiento vertical. | Matriz estricta RBAC (`Auth::hasRole()`, `Auth::canAccessEquipment()`). Los clientes solo ven y operan sobre los equipos de su sucursal/taller asignado. | `core/Auth.php`<br>`index.php` |
| **A02: Cryptographic Failures** | Fuga o interceptación de credenciales y datos en tránsito. | Cifrado obligatorio HTTPS vía rewrite `.htaccess`. Contraseñas con `password_hash(..., PASSWORD_BCRYPT, ['cost' => 12])`. Cookies `HttpOnly`, `SameSite=Strict` y `Secure`. | `config/security.php`<br>`core/Security.php` |
| **A03: Injection (SQLi / XSS)** | Inyección de SQL malicioso en formularios o XSS reflejado/almacenado. | 100% de consultas parametrizadas mediante sentencias preparadas de PDO. Escapado HTML exhaustivo de todas las salidas dinámicas con `Security::e()`. | `core/Security.php`<br>`core/DataStore.php` |
| **A04: Insecure Design** | Flujos de aprobación inseguros o estados no validados. | Flujo de estados unidireccional: Solicitud $\rightarrow$ En Proceso $\rightarrow$ Concluido con emisión de reporte técnico foliado. | `modules/ordenes/`<br>`modules/reportes/` |
| **A05: Security Misconfiguration** | Listado de directorios abierto, cabeceras faltantes o archivos de configuración expuestos. | `.htaccess` endurecido con `Options -Indexes`, bloqueo de `.sql`, `.env`, `.log`, `.git`, `config/`, y cabeceras `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Content-Security-Policy`. | `app/.htaccess`<br>`logs/.htaccess` |
| **A06: Vulnerable and Outdated Components** | Vulnerabilidades en frameworks pesados o librerías desactualizadas de terceros. | Código nativo Vanilla PHP 8.x + Vanilla CSS/JS sin dependencias externas pesadas vulnerables ni dependencias de npm en runtime. | Código base nativo |
| **A07: Identification and Authentication Failures** | Ataques de fuerza bruta en logins y fijación de sesión (Session Fixation). | Regeneración forzada de ID de sesión en login (`session_regenerate_id(true)`). Limitador de intentos por IP (`Security::checkRateLimit()`, 5 intentos en 5 min). | `core/Auth.php`<br>`core/Security.php` |
| **A08: Software and Data Integrity Failures** | Manipulación de formularios y peticiones falsificadas (CSRF). | Validación obligatoria de tokens CSRF criptográficamente seguros en el 100% de los métodos POST. | `config/security.php`<br>`index.php` |
| **A09: Security Logging and Monitoring Failures** | Ausencia de registros ante auditorías o incidentes forenses. | Módulo inmutable `Logger::log()` conforme a ISO 27001 A.12.4 con almacenamiento dual (archivo físico append-only y tabla `audit_logs`). | `core/Logger.php`<br>`modules/auditoria/` |
| **A10: Server-Side Request Forgery & Error Handling** | Fuga de rutas internas, versiones de software o trazas de pila (stack traces). | Directivas `display_errors = 0`, `log_errors = 1`. Manejador global `try-catch` con respuestas genéricas industriales para el usuario. | `config/security.php`<br>`index.php` |

---

### 3. Controles del Estándar ISO/IEC 27001:2022

#### Control A.9: Control de Acceso
1. **Separación de Funciones (A.9.1)**:
   - **Administrador**: Gestión global de entidades, empresas matrices, sucursales, técnicos, clientes y auditoría.
   - **Soporte Técnico**: Monitoreo de incidencias, comunicación privada y asistencia de campo.
   - **Técnico Especialista**: Emisión de reportes técnicos, actualización de salud de elevadores, descuento de inventario y atención de órdenes.
   - **Cliente (Agencia / Taller)**: Acceso limitado a su inventario asignado, consulta de reportes expedidos y levantamiento de solicitudes de reparación.
2. **Autenticación Única (A.9.2)**: Identificación por correo corporativo y contraseña con política de complejidad.

#### Control A.10: Criptografía
- Uso exclusivo de algoritmos robustos reconocidos por NIST/OWASP (Bcrypt para credenciales, SHA-256 para tokens y hashes de auditoría).

#### Control A.12: Seguridad en las Operaciones
1. **Protección contra Malware (A.12.2)**: No se permite la ejecución de scripts subidos por usuarios. Bloqueo de tipos MIME no autorizados.
2. **Gestión de Bitácoras de Auditoría (A.12.4)**:
   - Los registros de auditoría almacenan: Fecha/Hora precisa, Identificador de usuario, Rol activo, Acción ejecutada, Entidad objetivo, IP remota, User-Agent y metadatos JSON del cambio.
   - Protección contra alteración: El directorio `app/logs/` está bloqueado por `.htaccess` impidiendo lectura o manipulación remota.

#### Control A.13: Seguridad de las Comunicaciones
- Aislamiento de canales de comunicación a través del módulo de Mensajería Privada perimetral, evitando el uso de canales externos no cifrados o inseguros para órdenes técnicas críticas.

---

### 4. Directivas de Manejo Seguro de Errores

En el archivo `config/security.php` se configuran las siguientes directivas para mitigar fugas de información:
```php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', LOGS_PATH . '/php_errors.log');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
```
Si ocurre una excepción grave de base de datos o sistema, el usuario final visualiza una pantalla industrial limpia:
> *"Ocurrió una excepción al procesar la solicitud. El evento ha sido registrado en la bitácora de seguridad."*

El detalle técnico completo (código de error, línea, traza) se escribe exclusivamente en `app/logs/php_errors.log` y en `security.log` bajo candado perimetral.
