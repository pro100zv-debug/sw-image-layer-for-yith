![SW Image Layer for YITH Product](.wp-assets/banner-1544x500.png)

# SW Image Layer for YITH Product

Superposición de la imagen del complemento de YITH sobre la foto principal del producto.

Pensado para tiendas que ofrecen un bordado, un estampado o un parche: el cliente elige una opción con imagen en **YITH WooCommerce Product Add-ons & Extra Options Premium** y ve al momento cómo queda sobre la prenda.

**[⬇ Descargar el plugin (última versión)](https://github.com/pro100zv-debug/sw-image-layer-for-yith/releases/latest/download/sw-image-layer-for-yith.zip)**

## Qué hace

- Coloca la imagen de la opción elegida sobre la **primera foto** de la galería del producto.
- Funciona en todos los productos donde YITH muestra el complemento, con una **posición por defecto**.
- En cada producto se puede escribir una **posición (X, Y) y un ancho en %** propios, con vista previa en el editor, o marcar **«No mostrar la imagen»**. Al ser porcentajes, se ve igual en ordenador y en móvil.
- La imagen se mantiene al cambiar de color (variación).
- En móvil, si la galería es un carrusel, vuelve a la primera foto al elegir una opción.
- Mientras hay imagen elegida, el zoom al pasar el ratón no se muestra sobre la primera foto.
- **Buscador** encima de la lista: no distingue mayúsculas ni tildes («aguila» encuentra «Águila»).
- Lista con un número fijo de filas visibles y desplazamiento, para que el botón de compra no quede muy abajo.
- Texto de ayuda en lugar del aviso de error de YITH; desaparece al elegir una opción.
- Si YITH oculta la lista (por ejemplo, el cliente cambia «Sí» por «No»), se borran la opción elegida, la búsqueda y la imagen.

El precio, el carrito, el pedido y los correos los gestiona YITH como siempre.

## Requisitos

- WordPress 6.5 o superior, PHP 7.4 o superior
- WooCommerce 8.0 o superior
- YITH WooCommerce Product Add-ons & Extra Options **Premium**
- Probado con el tema WoodMart (galería en cuadrícula y en carrusel)

## Idiomas

Los ajustes del plugin se muestran en el idioma del perfil del usuario (o en el del sitio): **inglés**, **español** y **ruso**. Otras variantes del español (es_MX, es_AR…) usan la traducción de España; el resto de idiomas, el inglés.

Los textos que ve el cliente en la ficha (buscador, ayuda, sin resultados) se escriben en los ajustes, en el idioma de la tienda.

## Instalación

1. Descarga el ZIP con el enlace de arriba.
2. En WordPress: **Plugins → Añadir nuevo → Subir plugin** → elige el ZIP → **Instalar ahora** → **Activar**.

Las siguientes versiones aparecen en **Plugins** con el botón **Actualizar**, como cualquier otro plugin.

## Configuración

1. **En YITH**, crea un bloque con:
   - un complemento **Sí / No** (tipo *Label or image*), con el precio en «Sí»;
   - un complemento con las opciones e **imagen en cada opción** (tipo *Label or image*), que se muestre solo cuando «Sí» está elegido (*Conditional logic*).
   Los productos donde aparece el bloque (por producto o por categoría) se eligen en YITH.
2. **WooCommerce → Ajustes → SW Image Layer**: elige el complemento con imágenes, ajusta los textos, las filas visibles, el color y la **posición por defecto**.
3. **En cada producto que lo necesite**: *Datos del producto* → pestaña **SW Image Layer**:
   - escribe X, Y y ancho propios mirando la vista previa (campo vacío = posición por defecto);
   - o marca **«No mostrar la imagen»** para que en ese producto no se coloque sobre la foto.

### Imágenes recomendadas

Cuadradas, 400 × 400 px, WebP con fondo transparente y el mismo margen en todas, para que el mismo ancho en % se vea igual con cualquier imagen.

### Cambiar el aspecto

Los colores y medidas son variables CSS. Ejemplo para el CSS del tema:

```css
:root {
  --swil-accent: #811609;  /* opción elegida y texto de ayuda */
  --swil-border: #dcdcdc;  /* bordes de la lista */
  --swil-thumb: 44px;      /* tamaño de la miniatura */
  --swil-row-h: 64px;      /* alto de cada fila */
}
```

## Publicar una versión

1. Sube el número en `Version:` de `sw-image-layer-for-yith.php` y en `SWIL_VERSION`.
2. Añade debajo de «Cambios» una sección `### X.Y.Z` con la lista de cambios.
3. Si cambian textos de la administración, actualiza `languages/` (.pot, .po y .mo).
4. Sube los cambios a `main`. La acción «Publicar versión» crea el release `vX.Y.Z` con el ZIP; los sitios ven «Actualizar» en Plugins.

## Cambios

### 1.2.1
- Las nuevas versiones se detectan antes: la información de GitHub se guarda 1 hora en lugar de 6.
- «Comprobar de nuevo» en Escritorio → Actualizaciones también busca la última versión en GitHub.
- Nuevo banner.

### 1.2.0
- Traducciones: inglés como idioma base, español y ruso. Los ajustes se muestran en el idioma del perfil del usuario.

### 1.1.0
- La imagen se muestra en todos los productos donde YITH muestra el complemento; ya no hay que activarla producto por producto.
- Nueva posición por defecto (X, Y, ancho) en los ajustes; en el producto, los campos vacíos usan esa posición.
- Nueva casilla «No mostrar la imagen» en el producto.
- Corregido: las casillas desactivadas («Buscador» y la del producto) se tomaban como activadas.

### 1.0.2
- El buscador ya no sobresale del recuadro de la lista.

### 1.0.1
- El enlace del autor lleva a sergioweb.es.
- Se quita el enlace «Visitar la web del plugin».
- YITH WooCommerce Product Add-ons & Extra Options Premium aparece como plugin requerido.

### 1.0.0
- Primera versión.

## Autor

[Sergio](https://sergioweb.es/)

## Licencia

GPL-2.0-or-later
