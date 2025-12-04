"use strict";

class Circuito {
    constructor() {
        this.comprobarApiFile();
        
        const inputArchivo = document.querySelector('input[type="file"]:first-of-type');
        this.seccionInfo = document.querySelector('section:first-of-type');

        if (inputArchivo) {
            inputArchivo.addEventListener("change", (e) => {
                const archivo = e.target.files[0];
                this.leerArchivoHTML(archivo);
            });
        }                
    }

    comprobarApiFile() {
        if (!(window.File && window.FileReader && window.FileList && window.Blob)) {
            const mensaje = document.createElement("p");
            mensaje.textContent = "El navegador no soporta la API File.";
            document.body.appendChild(mensaje);
        }
    }

    leerArchivoHTML(archivo) {
        const lector = new FileReader();
        lector.onload = () => {
            this.mostrarContenido(lector.result);
        };
        lector.readAsText(archivo, "UTF-8");
    }

    mostrarContenido(textoHTML) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(textoHTML, "text/html");

        this.seccionInfo.innerHTML = "";

        const titulo = doc.querySelector("h1")?.textContent;
        if (titulo) {
            const h2 = document.createElement("h2");
            h2.textContent = titulo;
            this.seccionInfo.appendChild(h2);
        }

        const tablaOriginal = doc.querySelector("table");
        if (tablaOriginal) {
            const tablaNueva = document.createElement("table");

            tablaOriginal.querySelectorAll("tr").forEach(fila => {
                const nuevaFila = document.createElement("tr");

                fila.querySelectorAll("td").forEach(td => {
                    const nuevoTD = document.createElement("td");
                    nuevoTD.textContent = td.textContent;
                    nuevaFila.appendChild(nuevoTD);
                });

                tablaNueva.appendChild(nuevaFila);
            });

            this.seccionInfo.appendChild(tablaNueva);
        }
        
        doc.querySelectorAll("p").forEach(p => {
            const nuevoP = document.createElement("p");
            nuevoP.textContent = p.textContent;
            this.seccionInfo.appendChild(nuevoP);
        });

        doc.querySelectorAll("img").forEach(img => {
            const nuevaImg = document.createElement("img");
            nuevaImg.src = img.getAttribute("src").replace("../", "");
            nuevaImg.alt = img.getAttribute("alt");
            this.seccionInfo.appendChild(nuevaImg);
        });

        doc.querySelectorAll("video").forEach(video => {
            const nuevoVideo = document.createElement("video");
            nuevoVideo.controls = true;

            const source = video.querySelector("source");
            if (source) {
                const nuevoSource = document.createElement("source");
                nuevoSource.src = source.getAttribute("src").replace("../", "");
                nuevoSource.type = source.getAttribute("type");
                nuevoVideo.appendChild(nuevoSource);
            }

            this.seccionInfo.appendChild(nuevoVideo);
        });
    }
}

class CargadorSVG {

    constructor() {    
        this.comprobarApiFile();

        const inputArchivo = document.querySelector('input[type="file"]:nth-of-type(2)');
        this.seccionSVG = document.querySelector('section:nth-of-type(2)');

        if (inputArchivo) {
            inputArchivo.addEventListener("change", (e) => {
                const archivo = e.target.files[0];
                this.leerArchivoSVG(archivo);
            });     
        } 
    }

    comprobarApiFile() {
        if (!(window.File && window.FileReader && window.FileList && window.Blob)) {
            const mensaje = document.createElement("p");
            mensaje.textContent = "El navegador no soporta la API File.";
            document.body.appendChild(mensaje);
        }
    }

    leerArchivoSVG(archivo) {
        const lector = new FileReader();
        lector.onload = () => {
            this.insertarSVG(lector.result);
        };
        lector.readAsText(archivo, "UTF-8");
    }       

    insertarSVG(contenido) {
        const parser = new DOMParser();
        const docSVG = parser.parseFromString(contenido, 'image/svg+xml');

        const elementoSVG = docSVG.documentElement;

        this.seccionSVG.innerHTML = "";
        this.seccionSVG.appendChild(elementoSVG);
    }

}

"use strict";

class CargadorKML {
    constructor() {
        this.inputArchivo = document.querySelector('input[type="file"]:nth-of-type(3)');
        this.divMapa = document.querySelector('div:last-of-type');
        this.mapa = null;

        this.inicializarMapa();

        if (this.inputArchivo) {
            this.inputArchivo.addEventListener("change", (e) => {
                const archivo = e.target.files[0];
                if (archivo) this.leerArchivoKML(archivo);
            });
        }
    }

    inicializarMapa() {
        this.mapa = new google.maps.Map(this.divMapa, {
            center: { lat: 36.7094, lng: -6.0322 },
            zoom: 15,
            mapTypeId: 'roadmap'
        });
    }

    leerArchivoKML(archivo) {
        const lector = new FileReader();
        lector.onload = () => {
            this.insertarCapaKML(lector.result);
        };
        lector.readAsText(archivo, "UTF-8");
    }

    insertarCapaKML(contenidoKML) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(contenidoKML, "text/xml");

        const lineString = doc.querySelector("LineString > coordinates");
        if (lineString) {
            const coordsArray = lineString.textContent.trim().split(/\s+/).map(coord => {
                const [lon, lat] = coord.split(",");
                return { lat: parseFloat(lat), lng: parseFloat(lon) };
            });

            const polilinea = new google.maps.Polyline({
                path: coordsArray,
                geodesic: true,
                strokeColor: "#FF0000",
                strokeWeight: 3
            });

            polilinea.setMap(this.mapa);
            if (coordsArray.length > 0) this.mapa.setCenter(coordsArray[0]);
        }

        const puntos = doc.querySelectorAll("Placemark Point coordinates");
        puntos.forEach(coordElem => {
            const [lon, lat] = coordElem.textContent.trim().split(",");
            new google.maps.Marker({
                position: { lat: parseFloat(lat), lng: parseFloat(lon) },
                map: this.mapa
            });
        });
    }
}


const circuito = new Circuito();
const cargadorSVG  = new CargadorSVG();
const cargadorKML = new CargadorKML();
