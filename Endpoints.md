# Endpoints

- ``POST /auth/signup``: Registro de usuario
  - **username**: string
  - **email**: string
  - **password**: string

- ``POST /auth/login``: Retorna header Set-Cookie con la sesión.
  - **username**: string
  - **password**: string

- ``POST /auth/logout``: Invalida la cookie enviada en el header, terminando la sesión.

- ``GET /auth/me``: Retorna información de la cookie enviada

- ``GET /users/{id}|me``: Obtener un usuario

- ``PATCH /users/{id}|me``: Actualizar información de un usuario. Los usuarios solo podrán actualizar /users/me
  - **alimentaryRestrictions**: string[] *(De restricciones alimentarias del usuario: ["celiac", "diabetic", "hypertensive"])*
  - **profilePicture**: string *(UUID de la imágen de foto de perfil)*

- ``GET /users/me/commerces``: Obtener comercios subidos por el usuario autenticado

- ``GET /users/me/products``: Obtener productos subidos por el usuario autenticado

- ``GET /users/me/reviews``: Obtener reseñas subidas por el usuario autenticado

- ``GET /users/me/posts``: Obtener publicaciones subidas por el usuario autenticado

- `GET /commerces`: Obtener lista de comercios
  - **lat**: string *(Rango de latitud en el mapa, dos floats divididos por una coma)*
  - **lon**: string *(Rango de longitud en el mapa, dos floats divididos por una coma)*
  - **name**: string *(Filtrar por nombre del comercio, búsqueda parcial)*
  - **minPrice**: integer *(Precio mínimo)*
  - **maxPrice**: integer *(Precio máximo)*
  - **restrictions**: string *(Lista de restricciones separadas por comas. Ej: "celiac,diabetic,hypertension", mostraría comercios que ofrecen comida apta para estas restricciones)*
  - **commerceTypes**: string *(Lista de tipos de comercio separados por comas. Ej: "kiosco,supermercado")*
  - **orderBy**: string *(Por cual atributo y como ordenar los resultados: name_asc, name_desc, rating_asc, rating_desc, price_asc, price_desc)*
  - **unverified**: *(Agregar a la query '&unverified' para incluir comercios no verificados)*

- ``GET /commerces/{id}``: Obtener un comercio & sus imagenes

- ``GET /commerces/check-location``: Check para ver si existe un comercio en las coordenadas especificadas
  - **coords**: string *(Coordenadas, dos floats divididos por una coma "latitud,longitud")*

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
  - **images**: string[] *(Array de UUIDs de imágenes. Solo se pueden utilizar imágenes subidas por el usuario)*
  - **commerceSchedules**: object[] *(Horarios del comercio)*  
    - **weekday**: int *(0 = Domingo, 1 = Lunes, ..., 6 = Sábado)*  
    - **opensAt**: string *(Fecha-hora representando la hora de apertura)*  
    - **closesAt**: string *(Fecha-hora representando la hora de cierre)*

- ``PATCH /commerces/{id}``: Actualizar un comercio
  - Lleva los mismos parametros que el POST.
    - Los usuarios de rango Oro pueden modificar contactInfo, paymentMethods, y commerceSchedules.
    - Los usuarios de rango Platino también pueden verificar o desverificar comercios.
    - Los administradores pueden cambiar toda la información del comercio.
  - La verificación del comercio se hace pasando el atributo '**verified**: bool' en el JSON.

- ``DELETE /commerces/{id}``: Eliminar un comercio, solo para administradores

- ``GET /commerces/{id}/reviews``: Obtener lista de reseñas de un comercio
  - Retorna un atributo adicional **liked** para indicar el voto que el usuario le puso a la reseña (true, null, false)

- ``GET /commerces/{id}/reviews/{id}``: Obtener una reseña
  - Retorna un atributo adicional **liked** para indicar el voto que el usuario le puso a la reseña (true, null, false)

- ``POST /commerces/{id}/reviews``: Publicar una reseña para un comercio
  - **content**: string *(Contenido de la reseña)*
  - **positive**: bool *(Reseña positiva o negativa?)*

- ``PATCH /commerces/{id}/reviews/{id}``: Editar una reseña para un comercio
  - Lleva los mismos parametros que el POST.
    - Solo administradores o los creadores de la reseña en cuestión pueden modificarlo.
  - Los administradores pueden agregar el atributo '**visibility**: enum' en el JSON (public o private) para ocultar reviews.
  - Reviews ocultos no contarán para el rating total del comercio.

- ``PATCH /commerces/{id}/reviews/{id}/vote``: Votar una reseña como util/no util
  - **positive**: null|bool *(Voto negativo, positivo, o ninguno?)*

- ``DELETE /commerces/{id}/reviews/{id}``: Eliminar una reseña, solo para administradores

- ``GET /commerces/{id}/reports/{id}``: Obtener un reporte de un comercio, solo administradores

- ``GET /commerces/{id}/reports``: Obtener lista de reportes de un comercio, solo administradores
  - **resolved**: string *("true", "false" o "null")*

- ``POST /commerces/{id}/reports``: Publicar un reporte para un comercio
  - **content**: string *(Contenido textual del reporte)*
  - **type**: Enum *(En el caso de que se este reportando un comercio no verificado, 'type' se setea a "confirmation" o "rebuttal" para confirmar o denegar la existencia. También se puede setear a "issue" si solo se está reportando un problema)*
  - **image**: string *(UUID de una imágen para adjuntar, opcional)*

- ``PATCH /commerces/{id}/reports/{id}``: Modificar un reporte de un comercio, solo para administradores
  - **resolved**: bool|null *(Si se valida o no el reporte del usuario. Validar un reporte le otorgará puntos de gamificación al usuario)*

- ``GET /products``: Obtener lista de productos
  - **commerce**: int *(Comercio al cual pertenecen los productos)*
  - **name**: string *(Nombre del producto)*
  - **restrictions**: string *(Lista de restricciones separadas por comas. Ej: "celiac,diabetic,hypertension". Restricciones alimentarias a la cual tiene que ser apto el producto)*
  - **minPrice**: int *(Precio mínimo)*
  - **maxPrice**: int *(Precio máximo)*
  - **category**: string *(Lista de categorias separadas por comas. Ej: "food,drink". Categoría del producto.)*
  - **unverified**: *(Agregar a la query '&unverified' para incluir productos no verificados)*

- ``GET /products/{id}``: Obtener un producto

- ``POST /products``: Agregar un producto  
  - **commerceId**: integer *(ID del comercio al que pertenece el producto)*
  - **name**: string *(Nombre del producto)*
  - **brand**: string *(Marca del producto)*
  - **category**: string *(Categoría del producto, ej: "food", "drink")*
  - **price**: integer *(Precio del producto, debe ser ≥ 0)*
  - **aptFor**: string[] *(Lista de restricciones alimentarias para las que el producto es apto. Ej: ["celiac", "diabetic", "hypertensive"])*
  - **images**: string[] *(Array de UUIDs de imágenes. Solo se pueden utilizar imágenes subidas por el usuario)*

- ``PATCH /products/{id}``: Actualizar un producto
  - Lleva los mismos parametros que el POST (excepto commerceId).
    - Los usuarios de rango Plata pueden modificar price y aptFor.
    - Los usuarios de rango Oro o Platino también pueden verificar o desverificar productos.
    - Los administradores pueden cambiar toda la información del producto.
  - La verificación del producto se hace pasando el atributo '**verified**: bool' en el JSON.

- ``DELETE /products/{id}``: Eliminar un producto, solo para administradores

- ``GET /products/{id}/reports``: Obtener lista de reportes de un producto, solo administradores
  - **resolved**: string *("true", "false" o "null")*

- ``POST /products/{id}/reports``: Publicar un reporte para un producto
  - **content**: string *(Contenido textual del reporte)*
  - **type**: Enum *(En el caso de que se este reportando un producto no verificado, 'type' se setea a "confirmation" o "rebuttal" para confirmar o denegar la existencia. También se puede setear a "issue" si solo se está reportando un problema)*
  - **image**: string *(UUID de una imágen para adjuntar, opcional)*

- ``PATCH /commerces/{id}/reports/{id}``: Modificar un reporte de un producto, solo para administradores
  - **resolved**: bool|null *(Si se valida o no el reporte del usuario. Validar un reporte le otorgará puntos de gamificación al usuario)*

- ``GET /posts``: Retorna una lista de publicaciones
  - **page**: Página, si no se incluye este parametro, se setea a la página 1.

- ``GET /posts/{id}``: Retorna un post en específico
  - Retorna un atributo adicional **liked** para indicar el voto que el usuario le puso a la reseña (true, null, false)

- ``POST /posts``: Agergar una publicación
  - **title**: Titulo de la publicación.
  - **content**: Contenido de la publicación.
  - **tags**: Etiquetas de la publicación *(Solo tags de la tabla tags aceptados, se agregara el endpoints GET /tags luego)*
  - **visibility**: Visibilidad de la publicación *(public, delisted, private)*
  - **attachments**: string[] *(Array de UUIDs de imágenes. Solo se pueden utilizar imágenes subidas por el usuario)*

- ``PATCH /posts/{id}``: Modificar una publicación
  - Lleva los mismos parametros que el POST (excepto title).
  - Los administradores pueden modificar la información de todas las publicaciones, incluyendo title.

- ``PATCH /posts/{id}/vote``: Votar una publicación
  - **positive**: null|bool *(Voto negativo, positivo, o ninguno?)*

- ``DELETE /posts/{id}``: Eliminar una publicación, solo para administradores.

- ``GET /posts/{id}/comments``: Obtener lista de comentarios de una publicación.
  - **page**: Página, si no se incluye este parametro, se setea a la página 1.

- ``GET /posts/{id}/comments/{id}``: Obtener un comentario

- ``POST /posts/{id}/comments``: Agergar un comentario
  - **content**: Contenido del comentario.
  - **replyingTo**: ID del comentario al cual se le está respondiendo, null/no incluir si no se le está respondiendo a ninguno.

- ``PATCH /posts/{id}/comments/{id}``: Modificar un comentario
  - Lleva los mismos parametros que el POST (excepto replyingTo).
  - Los administradores pueden agregar el taributo '**visibility**: enum' en el JSON (public o private) para ocultar comentarios.

- ``DELETE /posts/{id}/comments/{id}``: Eliminar un comentario, solo para administradores.

- ``GET /images/{id}``: Obtener una imagen de su UUID, retorna binario.

- ``POST /images``: Subir una imagen y obtener su ID para referenciar en otros endpoints. Se utiliza form-data en lugar de JSON.
  - **file**: Archivo de imagen a subir.

- ``DELETE /images/{id}``: Eliminar una imagen, solo para administradores.