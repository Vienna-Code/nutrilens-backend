# NutriLens Backend

Backend para el proyecto NutriLens

## Setup

Instalar los requisitos:
- PHP 8.4
- MySQL
- Composer
- Symfony CLI

Clonar repositorio
```
git clone https://github.com/Vienna-Code/nutrilens-backend.git
cd nutrilens-backend
```

Instalar dependencias
```
composer install
```

Crea una copia de .env llamado '.env.local' y ajusta el driver de la base de datos a necesidad
```
DATABASE_URL="mysql://root@127.0.0.1:3306/nutrilens?serverVersion=10.6.20-MariaDB&charset=utf8"
```

Crear y Migrar base de datos
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Popular base de datos con datos de prueba
```
php bin/console doctrine:fixtures:load
```

Iniciar webserver (En 127.0.0.1:8000)
```
symfony serve
```

**ATENCIÓN:** Todos los usuarios de la base de datos llevan la contraseña "fakeuserpassword".
El nombre de usuario del administrador es "viennacode"

## Endpoints

Se pueden encontrar todos los endpoints del backend en el archivo `Endpoints.md`.
También se incluye una colección de Post Man para probar la base de datos.