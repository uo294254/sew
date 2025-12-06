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

    formatearHora(isoString) {
        const fecha = new Date(isoString);
        return fecha.toLocaleTimeString("es-ES", {
            hour: "2-digit",
            minute: "2-digit"
        });
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
        $articulo.append("<h3>Datos meteorológicos del día de la carrera (27-04-2025)</h3>");
        $articulo.append(`<p>Salida del sol: ${this.formatearHora(salidaSol)}</p>`);
        $articulo.append(`<p>Puesta del sol: ${this.formatearHora(puestaSol)}</p>`);

        for (let i = 0; i < horas.length; i++) {
            if (horas[i].endsWith("14:00") || horas[i].endsWith("15:00")) {
                const $lista = $("<ul></ul>");
                $lista.append(`<li>Hora: ${this.formatearHora(horas[i])}</li>`);
                $lista.append(`<li>Temperatura: ${temperatura[i]}°C</li>`);
                $lista.append(`<li>Sensación térmica: ${sensacion[i]}°C</li>`);
                $lista.append(`<li>Lluvia: ${lluvia[i]} mm</li>`);
                $lista.append(`<li>Humedad: ${humedad[i]}%</li>`);
                $lista.append(`<li>Velocidad del viento: ${vientoVel[i]} km/h</li>`);
                $lista.append(`<li>Dirección del viento: ${vientoDir[i]}°</li>`);
                
                $articulo.append($lista);
            }
        }

        $("main section").append($articulo);
    }

    getMeteorologiaEntrenos() {
        const lat = 36.681;
        const lon = -6.138;
        
        const start_date = "2025-04-24";
        const end_date = "2025-04-26";
        
        const url = `https://archive-api.open-meteo.com/v1/archive?latitude=${lat}&longitude=${lon}&hourly=temperature_2m,precipitation,windspeed_10m,relative_humidity_2m&timezone=Europe%2FMadrid&start_date=${start_date}&end_date=${end_date}`;

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
        if (!datos?.hourly) {
            $("main").append("<p>No hay datos de entrenamientos.</p>");
            return;
        }

        const horas = datos.hourly.time;
        const temperatura = datos.hourly.temperature_2m;
        const lluvia = datos.hourly.precipitation;
        const viento = datos.hourly.windspeed_10m;
        const humedad = datos.hourly.relative_humidity_2m;

        const datosPorDia = {};

        for (let i = 0; i < horas.length; i++) {
            const fecha = horas[i].split('T')[0];
            
            if (!datosPorDia[fecha]) {
                datosPorDia[fecha] = {
                    temperatura: [],
                    lluvia: [],
                    viento: [],
                    humedad: []
                };
            }

            datosPorDia[fecha].temperatura.push(temperatura[i]);
            datosPorDia[fecha].lluvia.push(lluvia[i]);
            datosPorDia[fecha].viento.push(viento[i]);
            datosPorDia[fecha].humedad.push(humedad[i]);
        }

        const $articulo = $("<article></article>");
        $articulo.append("<h3>Datos meteorológicos de los entrenamientos</h3>");

        for (const fecha in datosPorDia) {
            const datos = datosPorDia[fecha];

            const tempMedia = (datos.temperatura.reduce((a, b) => a + b, 0) / datos.temperatura.length).toFixed(2);
            const lluviaMedia = (datos.lluvia.reduce((a, b) => a + b, 0) / datos.lluvia.length).toFixed(2);
            const vientoMedia = (datos.viento.reduce((a, b) => a + b, 0) / datos.viento.length).toFixed(2);
            const humedadMedia = (datos.humedad.reduce((a, b) => a + b, 0) / datos.humedad.length).toFixed(2);

            const fechaObj = new Date(fecha + 'T12:00:00');
            const fechaFormateada = fechaObj.toLocaleDateString("es-ES", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric"
            });

            const $lista = $("<ul></ul>");
            $lista.append(`<li>Fecha: ${fechaFormateada}</li>`);
            $lista.append(`<li>Temperatura media: ${tempMedia}°C</li>`);
            $lista.append(`<li>Lluvia media: ${lluviaMedia} mm</li>`);
            $lista.append(`<li>Velocidad del viento media: ${vientoMedia} km/h</li>`);
            $lista.append(`<li>Humedad media: ${humedadMedia}%</li>`);
            
            $articulo.append($lista);
        }

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