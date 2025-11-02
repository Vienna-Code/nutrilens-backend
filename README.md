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

Instalar dependencias
```
composer install
```

Crea una copia de .env llamado '.env.local' y ajusta el driver de la base de datos
```
DATABASE_URL="mysql://root@127.0.0.1:3306/nutrilens?serverVersion=10.6.20-MariaDB&charset=utf8"
```

Crear y Migrar base de datos
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Popular base de datos
```
php bin/console doctrine:fixtures:load
```

Iniciar webserver (En 127.0.0.1:8000)
```
symfony serve
```

## Documentación

Accede a la documentación abriendo 127.0.0.1:8000/api en un navegador