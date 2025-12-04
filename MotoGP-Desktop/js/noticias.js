"use strict";

class Noticias {
    constructor(busqueda) {
        this.busqueda = busqueda;
        this.urlBase = "https://api.thenewsapi.com/v1/news/all";
        this.seccionNoticias = document.createElement("section");
        document.querySelector("main").appendChild(this.seccionNoticias);
    }

    async buscar() {
        const url = `${this.urlBase}?api_token=2t9Rp9MMJnequCHbmDdyUocLyspUH2EYjUadIgxF&search=${this.busqueda}&language=es`;

        try {
            const respuesta = await fetch(url);
            if (!respuesta.ok) throw new Error("Error al consultar las noticias");
            const datos = await respuesta.json();
            this.procesarInformacion(datos);
        } catch (error) {
            const pError = document.createElement("p");
            pError.textContent = "No se pudieron obtener las noticias: " + error.message;
            this.seccionNoticias.appendChild(pError);
        }
    }

    procesarInformacion(datos) {
        if (!datos || !datos.data || datos.data.length === 0) {
            const pSinNoticias = document.createElement("p");
            pSinNoticias.textContent = "No hay noticias disponibles.";
            this.seccionNoticias.appendChild(pSinNoticias);
            return;
        }

        const articulo = document.createElement("article");
        const h3 = document.createElement("h3");
        h3.textContent = "Últimas noticias de MotoGP";
        articulo.appendChild(h3);

        const ul = document.createElement("ul");

        datos.data.forEach(noticia => {
            const li = document.createElement("li");
            const a = document.createElement("a");
            a.href = noticia.url;
            a.textContent = noticia.title;
            a.target = "_blank";
            li.appendChild(a);

            if (noticia.description) {
                const pDesc = document.createElement("p");
                pDesc.textContent = noticia.description;
                li.appendChild(pDesc);
            }

            if (noticia.source) {
                const pFuente = document.createElement("p");
                pFuente.textContent = "Fuente: " + noticia.source;
                li.appendChild(pFuente);
            }

            ul.appendChild(li);
        });

        articulo.appendChild(ul);
        this.seccionNoticias.appendChild(articulo);
    }
}

const noticiasMotoGP = new Noticias("MotoGP");
noticiasMotoGP.buscar();