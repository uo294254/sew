"use strict";
class Carrusel {
    #busqueda;
    #actual;
    #maximo;
    #fotos;

    constructor(busqueda) {
        this.#busqueda = busqueda;
        this.#actual = 0;
        this.#maximo = 4;
        this.#fotos = [];
        this.url = "https://www.flickr.com/services/feeds/photos_public.gne?format=json&jsoncallback=?"; 
    }

    get busqueda() {
        return this.#busqueda;
    }

    set busqueda(nuevaBusqueda) {
        this.#busqueda = nuevaBusqueda;
    }

    getFotografias() {
        $.ajax({
            dataType: "json",
            url: this.url,
            data: {
                tags: this.busqueda,
                tagmode: "any",
                format: "json"
            },
            method: 'GET',
            success: (datos) => {
                $("pre").text(JSON.stringify(datos, null, 2));
                this.procesarJSONFotografias(datos);
                this.mostrarFotografias();
            },
            error: () => {
                $("h3").html("¡Tenemos problemas! No puedo obtener JSON de <a href='https://www.flickr.com/'>Flickr</a>");
                $("pre, p").remove();
            }
        });
    }

    procesarJSONFotografias(datos) {
        this.#fotos = datos.items.slice(0, this.#maximo).map(item => {
            const url640 = item.media.m.replace("_m.jpg", "_z.jpg");
            return {
                titulo: item.title,
                enlace: item.link,
                url: url640
            };
        });
    }

    mostrarFotografias() {
        if (this.#fotos.length === 0) return;

        const articulo = $(`<article></article>`);
        const encabezado = $(`<h2>Imágenes del circuito de Circuito de Jerez – Angel Nieto </h2>`);
        articulo.append(encabezado);

        const contenedor = $("<p></p>");
        articulo.append(contenedor);

        $("main").append(articulo);

        this.$contenedor = contenedor;

        this.cambiarFotografia();
        setInterval(this.cambiarFotografia.bind(this), 3000);
    }

    cambiarFotografia() {
        if (this.#actual < this.#maximo) {
            this.#actual++;
        } else {
            this.#actual = 0;
        }

        const foto = this.#fotos[this.#actual];
        this.$contenedor.html(`<ul>
                <li>
                    <a href="${foto.enlace}" target="_blank">
                        <img src="${foto.url}" alt="Foto MotoGP" width="200">
                    </a>
                </li>
            </ul>`);
    }
}

const miCarrusel = new Carrusel("CircuitoDeJerezAngelNieto");
miCarrusel.getFotografias();