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

## Rutas

- ``POST /auth/signup``: Registro de usuario
  - username: string
  - email: string
  - password: string

- ``POST /auth/login``: Retorna header Set-Cookie con la sesión.
  - username: string
  - password: string

- ``POST /auth/logout``: Invalida la cookie enviada en el header, terminando la sesión.

- ``GET /commerces``: Obtener lista de comercios
  - lat: string *(Rango de latitud en el mapa, dos floats dividido por una coma)*
  - lon: string *(Rango de longitud en el mapa, dos floats dividido por una coma)*
  - unverified *(Agregar a la query '&unverified' para incluir comercios no verificados)*

- ``GET /commerces/{id}``: Obtener un comercio & sus imagenes

- ``GET /products``: Obtener lista de productos
  - commerce: int *(Comercio al cual pertenecen los productos)*
  - unverified *(Agregar a la query '&unverified' para incluir productos no verificados)*

- ``GET /reviews``: Obtener lista de reviews
  - commerce: int *(Comercio al cual pertenecen los reviews)*