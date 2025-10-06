# NutriLens Backend

Backend para el proyecto NutriLens

## Setup

Instalar los requisitos
- PHP 8.4
- MySQL
- Composer
- Symfony CLI

Clonar repositorio
```
git clone https://github.com/Vienna-Code/nutrilens-backend.git
cd nutrilens-backend
```

Crear y Migrar Base de Datos (Edita DATABASE_URL en .env si es necesario)
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Instalar dependencias
```
composer install
```

Iniciar webserver (En 127.0.0.1:8000)
```
symfony serve
```

## Documentación

Accede a la documentación abriendo 127.0.0.1:8000/api en un navegador
