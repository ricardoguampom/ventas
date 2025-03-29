# Laravel Setup Guide

## Requisitos del Sistema

Para ejecutar este sistema, es necesario contar con los siguientes requisitos mínimos de hardware y software:

### **Requisitos de Hardware**
- **RAM**: 16 GB
- **Disco Duro**: 500 GB SSD

### **Requisitos de Software**
- **PHP**: 8.2.27
- **Composer**: 2.8.4
- **MySQL**: 8.0.30
- **Apache**: 2.4.54

### **Otros Requisitos**
- **Sistema Operativo**: Linux, macOS o Windows (con entorno adecuado de desarrollo)
- **Acceso a Internet**: Para descargar dependencias y actualizaciones.

Asegúrate de que tu máquina cumpla con estos requisitos antes de proceder con la instalación y configuración del proyecto.

---

## 1. Clonar el Proyecto desde GitHub

git clone https://github.com/ricardoguampom/ventas.git
cd ventas

### Instalación de Dependencias con Composer
Dado que el directorio vendor/ no se sube al repositorio, es necesario reinstalar las dependencias:

composer install

Esto garantizará que todas las librerías, incluyendo realrashid/sweet-alert, sean reinstaladas correctamente.

### Solución a Errores Comunes
Error bootstrap/cache:
Si encuentras un error indicando que el directorio bootstrap/cache no existe o no tiene permisos adecuados, sigue estos pasos:

### Crear el Directorio bootstrap/cache:

mkdir -p bootstrap/cache

En Windows (PowerShell):

New-Item -ItemType Directory -Path .\bootstrap\cache

### Asegurar que el Directorio sea Escribible: En Linux/macOS:
chmod -R 775 bootstrap/cache

En Windows (PowerShell):

icacls .\bootstrap\cache /grant "Usuarios":(OI)(CI)F

Volver a Ejecutar composer install:

composer install

## Error Illuminate\Encryption\MissingAppKeyException:

Si ves el error Illuminate\Encryption\MissingAppKeyException, significa que Laravel no puede encontrar la clave de cifrado de la aplicación. Para solucionarlo:

### Generar la clave de la aplicación:

php artisan key:generate

### Verificar el archivo .env: Asegúrate de que APP_KEY esté configurado correctamente en tu archivo .env:

APP_KEY=base64:your_generated_key_here

### Limpiar la caché de la configuración (opcional):

php artisan config:clear

## Configurar el Timezone

### Abre el archivo config/app.php y modifica la línea correspondiente al timezone:

'timezone' => 'America/La_Paz',

## Migraciones y Seeders
### Ejecuta las migraciones para crear las tablas de la base de datos:

php artisan migrate

### Si tienes seeders configurados, ejecútalos con:

php artisan db:seed

## Verificación y Creación del Enlace Simbólico

### Verificar el Enlace Simbólico
Ejecuta en la terminal:

ls -l public/storage

Debe mostrar un enlace simbólico apuntando a storage/app/public/. Si el enlace no existe o está roto, elimínalo y créalo de nuevo con:

rm -rf public/storage
php artisan storage:link

Luego, revisa si el enlace simbólico se creó correctamente:

ls -l public/storage