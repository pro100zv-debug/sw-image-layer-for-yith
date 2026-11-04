![SW Image Layer for YITH Product](.wp-assets/banner-1544x500.png)

# SW Image Layer for YITH Product

Superposición de la imagen del complemento de YITH sobre la foto principal del producto.

Pensado para tiendas que ofrecen un bordado, un estampado o un parche: el cliente elige una opción con imagen en **YITH WooCommerce Product Add-ons & Extra Options Premium** y ve al momento cómo queda sobre la prenda.

**[⬇ Descargar el plugin (última versión)](https://github.com/pro100zv-debug/sw-image-layer-for-yith/releases/latest/download/sw-image-layer-for-yith.zip)**

## Qué hace

- Coloca la imagen de la opción elegida sobre la **primera foto** de la galería del producto.
- **Posición (X, Y) y ancho en %** propios de cada producto, con vista previa en el editor. Al ser porcentajes, se ve igual en ordenador y en móvil.
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

## Instalación

1. Descarga el ZIP con el enlace de arriba.
2. En WordPress: **Plugins → Añadir nuevo → Subir plugin** → elige el ZIP → **Instalar ahora** → **Activar**.

Las siguientes versiones aparecen en **Plugins** con el botón **Actualizar**, como cualquier otro plugin.

## Configuración

1. **En YITH**, crea un bloque con:
   - un complemento **Sí / No** (tipo *Label or image*), con el precio en «Sí»;
   - un complemento con las opciones e **imagen en cada opción** (tipo *Label or image*), que se muestre solo cuando «Sí» está elegido (*Conditional logic*).
2. **WooCommerce → Ajustes → SW Image Layer**: elige el complemento con imágenes y ajusta los textos, las filas visibles y el color.
3. **En cada producto**: *Datos del producto* → pestaña **SW Image Layer** → activa «Mostrar imagen sobre la foto» y ajusta X, Y y ancho mirando la vista previa.

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
3. Sube los cambios a `main`. La acción «Publicar versión» crea el release `vX.Y.Z` con el ZIP; los sitios ven «Actualizar» en Plugins.

## Cambios

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
