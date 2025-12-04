"use strict";
class Cronometro {
    #tiempo;
    #corriendo;
    #inicio;
    
    constructor() {
        this.#tiempo = 0;
        this.#corriendo = null;
        this.#inicio = 0;
    }

    arrancar() {
        try {
            this.#inicio = Temporal.Now.instant() - this.#tiempo;
        } catch (err) {
            this.#inicio = new Date() - this.#tiempo;
        }
        this.#corriendo = setInterval(this.#actualizar.bind(this), 100);    
    }

    #actualizar() {
        let actual;
        try {
            actual = Temporal.Now.instant();
        } catch (err) {
            actual = new Date();
        }
        this.#tiempo = actual - this.#inicio;
        this.#mostrar();
    }

    #mostrar() {
        let totalMilisegundos = parseInt(this.#tiempo);
        let minutos = parseInt(totalMilisegundos / 60000);
        let segundos = parseInt((totalMilisegundos % 60000) / 1000);
        let decimas = parseInt((totalMilisegundos % 1000) / 100);

        let mm = minutos.toString().padStart(2, "0");
        let ss = segundos.toString().padStart(2, "0");
        let s = decimas.toString();

        let cadena = mm + ":" + ss + "." + s;

        let p = document.querySelector("main p");
        p.textContent = cadena;
    }

    parar() {
        clearInterval(this.#corriendo);
    }

    reiniciar() {
        clearInterval(this.#corriendo);
        this.#tiempo = 0;
        this.#mostrar();
    }
}

const crono = new Cronometro();

const botones = document.querySelectorAll("button");

for (let i = 0; i < botones.length; i++) {
    if (i === 0) {
        botones[i].addEventListener("click", () => {
            crono.arrancar();
        });
    } else if (i === 1) {
        botones[i].addEventListener("click", () => {
            crono.parar();
        });
    } else {
        botones[i].addEventListener("click", () => {
            crono.reiniciar();
        });
    }
}
