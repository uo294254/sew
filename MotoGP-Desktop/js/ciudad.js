"use strict";
class Ciudad {
    #nombre;
    #pais;
    #gentilicio;
    #poblacion;
    #coordenadas;

    constructor(nombre, pais, gentilicio) {
        this.#nombre = nombre;
        this.#pais = pais;
        this.#gentilicio = gentilicio;
    }

    inicializarDatos(poblacion, coordenadas) {
        this.#poblacion = poblacion;
        this.#coordenadas = coordenadas;
    }

    getNombre() {
        const pNombre = document.createElement("p");
        pNombre.textContent = "Nombre: " + this.#nombre;
        return pNombre;
    }

    getPais() {
        const pPais = document.createElement("p");
        pPais.textContent = "País: " + this.#pais;
        return pPais;
    }

    getInfoSecundaria() {
        const ul = document.createElement("ul");

        const liGentilicio = document.createElement("li");
        liGentilicio.textContent = "Gentilicio: " + this.#gentilicio;
        ul.appendChild(liGentilicio);

        const liPoblacion = document.createElement("li");
        liPoblacion.textContent = "Población: " + this.#poblacion + " habitantes";
        ul.appendChild(liPoblacion);

        return ul;
    }

    mostrarCoordenadas() {
        const pCoordenadas = document.createElement("p");
        pCoordenadas.textContent = "Coordenadas: " + this.#coordenadas;
        return pCoordenadas;
    }

    getMeteorologiaCarrera() {
        const lat = 36.681;
        const lon = -6.138;
    
        const start_date = "2025-04-27";
        const end_date = "2025-04-27";

        const url = `https://archive-api.open-meteo.com/v1/archive?latitude=${lat}&longitude=${lon}&hourly=temperature_2m,apparent_temperature,precipitation,relative_humidity_2m,windspeed_10m,winddirection_10m&daily=sunrise,sunset&timezone=Europe%2FMadrid&start_date=${start_date}&end_date=${end_date}`;
        
        $.ajax({
            dataType: "json",
            url: url,
            method: "GET",
            success: (datos) => {
                this.procesarJSONCarrera(datos);
            },
            error: () => {
                $("main").append("<p>No se pudo obtener la meteorología de la carrera.</p>");
            }
        });
    }



    procesarJSONCarrera(datos) {
        if (!datos?.hourly) {
            $("main").append("<p>No hay datos de carrera.</p>");
            return;
        }

        const salidaSol = datos.daily.sunrise[0];
        const puestaSol = datos.daily.sunset[0];

        const horas = datos.hourly.time;
        const temperatura = datos.hourly.temperature_2m;
        const sensacion = datos.hourly.apparent_temperature;
        const lluvia = datos.hourly.precipitation;
        const humedad = datos.hourly.relative_humidity_2m;
        const vientoVel = datos.hourly.windspeed_10m;
        const vientoDir = datos.hourly.winddirection_10m;

        const $articulo = $("<article></article>");
        $articulo.append("<h3>Datos meteorológicos del día de la carrera</h3>");
        $articulo.append(`<p>Salida del sol: ${salidaSol}</p>`);
        $articulo.append(`<p>Puesta del sol: ${puestaSol}</p>`);

        const $tabla = $("<table></table>");
        $tabla.append("<tr><th>Hora</th><th>Temp (°C)</th><th>Sensación</th><th>Lluvia (mm)</th><th>Humedad (%)</th><th>Viento (km/h)</th><th>Dirección (°)</th></tr>");

        for (let i = 0; i < horas.length; i++) {
            if (horas[i].endsWith("14:00") || horas[i].endsWith("15:00")) {
                $tabla.append(`
                    <tr>
                        <td>${horas[i]}</td>
                        <td>${temperatura[i]}</td>
                        <td>${sensacion[i]}</td>
                        <td>${lluvia[i]}</td>
                        <td>${humedad[i]}</td>
                        <td>${vientoVel[i]}</td>
                        <td>${vientoDir[i]}</td>
                    </tr>
                `);
            }
        }

        $articulo.append($tabla);
        $("main section").append($articulo);
    }

    getMeteorologiaEntrenos() {
        const lat = 36.681;
        const lon = -6.138;
        const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&hourly=temperature_2m,precipitation,windspeed_10m,relative_humidity_2m&timezone=Europe%2FMadrid&past_days=3`;

        $.ajax({
            dataType: "json",
            url: url,
            method: "GET",
            success: (datos) => {
                this.procesarJSONEntrenos(datos);
            },
            error: () => {
                $("main").append("<p>No se pudo obtener la meteorología de los entrenamientos.</p>");
            }
        });
    }

    procesarJSONEntrenos(datos) {
        const totalHoras = datos.hourly.time.length;
        const horas = datos.hourly.time.slice(totalHoras - 6);
        const temperatura = datos.hourly.temperature_2m.slice(totalHoras - 6);
        const lluvia = datos.hourly.precipitation.slice(totalHoras - 6);
        const viento = datos.hourly.windspeed_10m.slice(totalHoras - 6);
        const humedad = datos.hourly.relative_humidity_2m.slice(totalHoras - 6);


        const $articulo = $("<article></article>");
        $articulo.append("<h3>Datos meteorológicos de los entrenamientos</h3>");

        const $tabla = $("<table></table>");
        $tabla.append("<tr><th>Hora</th><th>Temp (°C)</th><th>Lluvia (mm)</th><th>Viento (km/h)</th><th>Humedad (%)</th></tr>");

        for (let i = 0; i < horas.length; i++) {
            $tabla.append(`
                <tr>
                    <td>${horas[i]}</td>
                    <td>${temperatura[i]}</td>
                    <td>${lluvia[i]}</td>
                    <td>${viento[i]}</td>
                    <td>${humedad[i]}</td>
                </tr>
            `);
        }

        $articulo.append($tabla);
        $("main section").append($articulo);
    }

}

const jerez = new Ciudad("Jerez de la Frontera", "España", "jerezano");
jerez.inicializarDatos("213.688", "Latitud 36.681, Longitud -6.138");

const seccion = document.querySelector("main section");
seccion.appendChild(jerez.getNombre());
seccion.appendChild(jerez.getPais());
seccion.appendChild(jerez.getInfoSecundaria());
seccion.appendChild(jerez.mostrarCoordenadas());

jerez.getMeteorologiaCarrera();
jerez.getMeteorologiaEntrenos();
