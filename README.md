🍽️ Catalogo Service - API de Restaurantes
API RESTful para la gestión de restaurantes, categorías de menú y productos desarrollada con Laravel.


🚀 Ejecución con Docker (Recomendado para desarrollo)
Prerrequisitos
Docker

Docker Compose

Pasos Rápidos
Clonar y configurar el proyecto:

bash
git clone <tu-repositorio>
cd CatalogoService

Ejecutar con Docker:

bash
# Construir y levantar contenedores
docker-compose up -d --build

# Instalar dependencias y configurar
docker-compose exec app composer install

docker-compose exec app php artisan key:generate

docker-compose exec app php artisan migrate

Acceder a la aplicación:

🌐 Aplicación: http://localhost:8000

📚 Documentación API: http://localhost:8000/api/documentation

🗄️ Base de datos: localhost:3306


🖥️ Ejecución Sin Docker (Desarrollo Local)
Prerrequisitos
PHP 8.2+

Composer

MySQL 8.0+


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


Iniciar servidor:

bash
php artisan serve

# Acceder a la aplicación:

🌐 Aplicación: http://localhost:8000

📚 Documentación API: http://localhost:8000/api/documentation