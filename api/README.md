# API de Gestión de Ideas - AVI

Sistema completo de APIs REST en PHP y MySQL para gestionar ideas dentro de la aplicación AVI.

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)

## Instalación

1. Clonar el repositorio en tu servidor web
2. Configurar la base de datos:
   - Crear una base de datos MySQL
   - Importar el esquema desde `config/schema.sql`
   - Actualizar las credenciales en `config/database.php`

3. Configurar el servidor web:
   - Asegurarse que el directorio `api` sea accesible
   - Configurar el archivo .htaccess (para Apache):
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```

4. Configurar el cron job para recordatorios:
   ```bash
   # Ejecutar cada día a las 8:00 AM
   0 8 * * * php /ruta/a/api/cronjobs/recordatorios.php
   ```

## Estructura del Proyecto

```
api/
├── config/
│   ├── database.php    # Configuración de base de datos
│   └── schema.sql      # Esquema de la base de datos
├── controllers/
│   ├── IdeasController.php
│   └── UsuariosController.php
├── models/
│   ├── BaseModel.php
│   ├── Idea.php
│   ├── SeguimientoIdea.php
│   ├── NotificacionIdea.php
│   └── Usuario.php
├── routes/
│   └── api.php         # Definición de rutas
├── cronjobs/
│   └── recordatorios.php
└── index.php           # Punto de entrada
```

## Endpoints Principales

### Módulo de Ideas

#### Explorador de Ideas
```http
POST /api/ideas/explorador
```
Guía inicial para analizar nuevas ideas.

#### Crear Idea
```http
POST /api/ideas
```
Registrar una nueva idea en el sistema.

#### Agregar Seguimiento
```http
POST /api/ideas/{id}/seguimiento
```
Añadir actualización de progreso a una idea.

#### Obtener Recomendaciones
```http
GET /api/ideas/{id}/recomendaciones
```
Recibir sugerencias personalizadas.

### Módulo de Usuarios

#### Crear Usuario
```http
POST /api/usuarios
```
Registrar nuevo usuario.

#### Autenticar Usuario
```http
POST /api/usuarios/auth
```
Iniciar sesión.

#### Obtener Ideas de Usuario
```http
GET /api/usuarios/{id}/ideas
```
Listar todas las ideas de un usuario.

#### Obtener Resumen
```http
GET /api/usuarios/{id}/resumen
```
Ver estadísticas y resumen de actividad.

## Ejemplos de Uso

### 1. Crear Usuario
```json
POST /api/usuarios
{
    "nombre": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "password": "contraseña123"
}
```

### 2. Explorar Idea
```json
POST /api/ideas/explorador
{
    "tipo_idea": "Negocio",
    "descripcion_corta": "Tienda de productos naturales",
    "motivacion": "Ofrecer productos saludables",
    "problema_oportunidad": "Falta de opciones naturales",
    "ubicacion": "San José",
    "audiencia_objetivo": "Personas interesadas en salud",
    "recursos_disponibles": "Capital inicial $1000"
}
```

### 3. Crear Idea
```json
POST /api/ideas
{
    "id_usuario": 1,
    "tipo_idea": "Negocio",
    "descripcion_corta": "Cafetería sostenible",
    "motivacion": "Combinar salud y ecología",
    "problema_oportunidad": "Pocas opciones saludables económicas",
    "ubicacion": "San José",
    "audiencia_objetivo": "Adultos jóvenes y profesionales",
    "diferenciador": "Productos orgánicos locales",
    "recursos_disponibles": "$2000 y local",
    "primer_paso": "Definir menú inicial",
    "fecha_recordatorio": "2024-07-01"
}
```

## Seguridad

- Todas las contraseñas se almacenan hasheadas
- Implementación de CORS para acceso desde diferentes dominios
- Validación de datos en todas las entradas
- Manejo de errores y excepciones

## Mantenimiento

### Recordatorios Automáticos
El sistema ejecuta diariamente:
- Verificación de recordatorios programados
- Detección de ideas estancadas
- Limpieza de notificaciones antiguas

### Logs
Los logs del sistema se almacenan en:
- `/cronjobs/recordatorios.log` para el sistema de recordatorios
- Logs del servidor web para las peticiones API
