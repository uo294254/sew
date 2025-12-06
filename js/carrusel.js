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
                this.procesarJSONFotografias(datos);
                this.mostrarFotografias();
            },
            error: () => {
                $("article h2").html("¡Tenemos problemas! No puedo obtener JSON de <a href='https://www.flickr.com/'>Flickr</a>");
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

        this.$contenedor = $("article h2");

        this.cambiarFotografia();
        setInterval(this.cambiarFotografia.bind(this), 3000);
    }

    cambiarFotografia() {
        const foto = this.#fotos[this.#actual];
        
        $("article h2 ~ img").remove();
        
        this.$contenedor.after(`<img src="${foto.url}" alt="${foto.titulo}" />`);

        if (this.#actual < this.#maximo - 1) {
            this.#actual++;
        } else {
            this.#actual = 0;
        }
    }
}

const miCarrusel = new Carrusel("CircuitoDeJerezAngelNieto");
miCarrusel.getFotografias();