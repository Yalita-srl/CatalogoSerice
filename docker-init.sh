#!/bin/bash

echo "🚀 Inicializando aplicación Laravel en Docker..."

# Verificar si Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Docker no está instalado. Por favor instala Docker primero."
    exit 1
fi

# Verificar si Docker Compose está instalado
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose no está instalado. Por favor instálalo primero."
    exit 1
fi

echo "✅ Docker y Docker Compose encontrados"

# Construir los contenedores
echo "🔨 Construyendo contenedores..."
docker-compose build

# Iniciar los servicios
echo "🐳 Iniciando servicios en segundo plano..."
docker-compose up -d

echo "⏳ Esperando que los servicios estén listos..."
sleep 30

# Instalar dependencias de Composer
echo "📦 Instalando dependencias de Composer..."
docker-compose exec app composer install

# Generar key de la aplicación
echo "🔑 Generando key de la aplicación..."
docker-compose exec app php artisan key:generate

# Ejecutar migraciones
echo "🗃️ Ejecutando migraciones..."
docker-compose exec app php artisan migrate

# Ejecutar seeders
echo "🌱 Ejecutando seeders..."
docker-compose exec app php artisan db:seed

# Crear enlace de storage
echo "📁 Creando enlace de storage..."
docker-compose exec app php artisan storage:link

echo "🎉 ¡Aplicación lista!"
echo "📱 URL: http://localhost:8000"
echo "🗄️  MySQL: localhost:3306 (usuario: sail, password: password)"
echo "🔴 Redis: localhost:6379"
echo ""
echo "📝 Comandos útiles:"
echo "   Ver logs: docker-compose logs -f"
echo "   Acceder a la app: docker-compose exec app bash"
echo "   Detener servicios: docker-compose down"