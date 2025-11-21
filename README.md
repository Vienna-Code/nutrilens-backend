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
  - **orderBy**: string *(Por cual atributo y como ordenar los resultados: name_asc, name_desc, rating_asc, rating_desc, price_asc, price_desc)*
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

- ``PATCH /commerces``: Actualizar un comercio
  - Lleva los mismos parametros que el POST
    - Los usuarios de rango Oro pueden modificar contactInfo, paymentMethods, y commerceSchedules
    - Los usuarios de rango Platino también pueden verificar o desverificar comercios
    - Los administradores pueden cambiar toda la información del comercio
  - La verificación del comercio se hace pasando el atributo '**verified**: bool' en el JSON

- ``DELETE /commerces/{id}``: Eliminar un comercio, solo para administradores

- ``GET /products``: Obtener lista de productos
  - **commerce**: int *(Comercio al cual pertenecen los productos)*
  - **unverified**: *(Agregar a la query '&unverified' para incluir productos no verificados)*

- ``GET /products/{id}``: Obtener un producto

- ``POST /products``: Agregar un producto  
  - **commerceId**: integer *(ID del comercio al que pertenece el producto)*
  - **name**: string *(Nombre del producto)*
  - **brand**: string *(Marca del producto)*
  - **category**: string *(Categoría del producto, ej: "food", "drink")*
  - **price**: integer *(Precio del producto, debe ser ≥ 0)*
  - **aptFor**: string[] *(Lista de restricciones alimentarias para las que el producto es apto. Ej: ["celiac", "diabetic", "hypertensive"])*

- ``PATCH /products``: Actualizar un producto
  - Lleva los mismos parametros que el POST (excepto commerceId)
    - Los usuarios de rango Plata pueden modificar price y aptFor
    - Los usuarios de rango Oro o Platino también pueden verificar o desverificar productos
    - Los administradores pueden cambiar toda la información del producto
  - La verificación del producto se hace pasando el atributo '**verified**: bool' en el JSON

- ``DELETE /products/{id}``: Eliminar un producto, solo para administradores

- ``GET /reviews``: Obtener lista de reviews
  - **commerce**: int *(Comercio al cual pertenecen los reviews)*