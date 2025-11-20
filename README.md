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
  - **username**: string
  - **email**: string
  - **password**: string

- ``POST /auth/login``: Retorna header Set-Cookie con la sesión.
  - **username**: string
  - **password**: string

- ``POST /auth/logout``: Invalida la cookie enviada en el header, terminando la sesión.

- ``GET /auth/me``: Retorna información de la cookie enviada

- `GET /commerces`: Obtener lista de comercios
  - **lat**: string *(Rango de latitud en el mapa, dos floats divididos por una coma)*
  - **lon**: string *(Rango de longitud en el mapa, dos floats divididos por una coma)*
  - **name**: string *(Filtrar por nombre del comercio, búsqueda parcial)*
  - **minPrice**: integer *(Precio mínimo, debe ser ≥ 0)*
  - **maxPrice**: integer *(Precio máximo, debe ser ≥ 0)*
  - **restrictions**: string *(Lista de restricciones separadas por comas. Ej: "celiac,diabetic,hypertension", mostraría comercios que ofrecen comida apta para estas restricciones)*
  - **commerceTypes**: string *(Lista de tipos de comercio separados por comas. Ej: "kiosco,supermercado")*
  - **unverified**: *(Agregar a la query '&unverified' para incluir comercios no verificados)*

- ``GET /commerces/{id}``: Obtener un comercio & sus imagenes

- ``POST /commerces``: Agregar un comercio  
  - **name**: string *(Nombre del comercio)*  
  - **type**: string *(Tipo de comercio)*  
  - **coordsLat**: float *(Latitud del comercio)*  
  - **coordsLon**: float *(Longitud del comercio)*  
  - **address**: string *(Dirección)*  
  - **contactInfo**: object  
    - **number**: string *(Número de teléfono, ej: "+598...")*  
    - **email**: string *(Correo de contacto)*  
  - **paymentMethods**: string[] *(Métodos de pago aceptados, ej: ["credito", "debito"])*  
  - **commerceSchedules**: object[] *(Horarios del comercio)*  
    - **weekday**: int *(0 = Domingo, 1 = Lunes, ..., 6 = Sábado)*  
    - **opensAt**: string *(Fecha-hora representando la hora de apertura)*  
    - **closesAt**: string *(Fecha-hora representando la hora de cierre)*  

- ``GET /products``: Obtener lista de productos
  - **commerce**: int *(Comercio al cual pertenecen los productos)*
  - **unverified**: *(Agregar a la query '&unverified' para incluir productos no verificados)*

- ``GET /reviews``: Obtener lista de reviews
  - **commerce**: int *(Comercio al cual pertenecen los reviews)*