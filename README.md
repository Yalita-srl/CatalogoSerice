🍽️ Catalogo Service - API de Restaurantes
API RESTful para la gestión de restaurantes, categorías de menú y productos desarrollada con Laravel.

📋 Características
✅ Gestión completa de restaurantes

✅ Categorías de menú por restaurante

✅ Productos con imágenes

✅ API RESTful documentada con Swagger

✅ Dockerizado para desarrollo

✅ Base de datos MySQL + Redis

🚀 Ejecución con Docker (Recomendado para desarrollo)
Prerrequisitos
Docker

Docker Compose

Pasos Rápidos
Clonar y configurar el proyecto:

bash
git clone <tu-repositorio>
cd CatalogoService

# Crear directorio necesario
mkdir -p docker/nginx/conf.d
Ejecutar con Docker:

bash
# Construir y levantar contenedores
docker-compose up -d --build

# Instalar dependencias y configurar
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan storage:link
Acceder a la aplicación:

🌐 Aplicación: http://localhost:8000

📚 Documentación API: http://localhost:8000/api/documentation

🗄️ Base de datos: localhost:3306

🔴 Redis: localhost:6379

Comandos Útiles con Docker
bash
# Ver logs en tiempo real
docker-compose logs -f app

# Acceder al contenedor de la aplicación
docker-compose exec app bash

# Ejecutar migraciones adicionales
docker-compose exec app php artisan migrate

# Ejecutar tests
docker-compose exec app php artisan test

# Instalar nuevas dependencias
docker-compose exec app composer require nombre/paquete

# Detener servicios
docker-compose down

# Detener y eliminar volúmenes (cuidado: borra datos)
docker-compose down -v
🖥️ Ejecución Sin Docker (Desarrollo Local)
Prerrequisitos
PHP 8.2+

Composer

MySQL 8.0+

Redis (opcional)

Pasos de Instalación
Clonar el proyecto:

bash
git clone <tu-repositorio>
cd CatalogoService
Instalar dependencias:

bash
composer install
Configurar entorno:

bash
# Copiar archivo de entorno
cp .env.example .env

# Generar key de la aplicación
php artisan key:generate
Configurar base de datos:

Crear una base de datos MySQL llamada db_catalogo

Configurar en .env:

env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_catalogo
DB_USERNAME=root
DB_PASSWORD=
Ejecutar migraciones:

bash
php artisan migrate
php artisan db:seed
php artisan storage:link
Iniciar servidor:

bash
php artisan serve
Acceder a la aplicación:

🌐 Aplicación: http://localhost:8000

📚 Documentación API: http://localhost:8000/api/documentation