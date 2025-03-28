# Laravel Setup Guide

## 1. Clonar el Proyecto desde GitHub

Si ya tienes un repositorio en GitHub, clónalo en tu máquina local:

git clone https://github.com/tu-usuario/VENTAS.git
cd VENTAS

## 2. Instalación de Dependencias con Composer

Dado que el directorio `vendor/` no se sube al repositorio, es necesario reinstalar las dependencias:

composer install

Esto garantizará que todas las librerías, incluyendo `realrashid/sweet-alert`, sean reinstaladas correctamente.

## 3. Configurar el Timezone

Abre el archivo `config/app.php` y modifica la línea correspondiente al timezone:

'timezone' => 'America/La_Paz',

## 4. Migraciones y Seeders

Ejecuta las migraciones para crear las tablas de la base de datos:

php artisan migrate

Si tienes seeders configurados, ejecútalos con:

php artisan db:seed

## 5. Verificación y Creación del Enlace Simbólico

### Verificar el enlace simbólico manualmente

Ejecuta en la terminal:

ls -l public/storage

Debe mostrar un enlace simbólico apuntando a `storage/app/public/`. Si el enlace no existe o está roto, elimínalo y créalo de nuevo con:

rm -rf public/storage
php artisan storage:link

Luego, revisa si el enlace simbólico se creó correctamente:

ls -l public/storage
