"use strict";

class Circuito {
    constructor() {
        this.comprobarApiFile();
        
        const inputArchivo = document.querySelector('input[accept=".html"]');
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

        doc.querySelectorAll("section").forEach(s => {
            const section = s.cloneNode(true);
            this.seccionInfo.appendChild(section);
        });
    }

}

class CargadorSVG {

    constructor() {    
        this.comprobarApiFile();

        const inputArchivo = document.querySelector('input[accept=".svg"]');
        this.seccionSVG = document.querySelector('section:nth-of-type(2)');
        this.contenedorSVG = document.createElement('p');
        this.seccionSVG.appendChild(this.contenedorSVG);

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

        elementoSVG.setAttribute('width', '750');
        elementoSVG.setAttribute('height', '350');
        elementoSVG.setAttribute('viewBox', '0 0 750 350');

        this.contenedorSVG.innerHTML = "";
        this.contenedorSVG.appendChild(elementoSVG);
    }

}

class CargadorKML {
    constructor() {
        this.inputArchivo = document.querySelector('input[accept=".kml"]');
        this.divMapa = document.querySelector('div:last-of-type');
        this.mapa = null;

        if (this.inputArchivo) {
            this.inputArchivo.addEventListener("change", (e) => {
                this.inicializarMapa();
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
