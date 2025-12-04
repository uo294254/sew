"use strict";
class Memoria {
    #tablero_bloqueado;
    #primera_carta;
    #segunda_carta;
    #cronometro;

    constructor() {
        this.#tablero_bloqueado = true;
        this.#primera_carta = null;
        this.#segunda_carta = null;

        this.#barajarCartas();
        this.#tablero_bloqueado = false;

        this.#cronometro = new Cronometro();
        this.#cronometro.arrancar();

        const cartas = document.querySelectorAll("main article");

        cartas.forEach((carta) => {
            carta.addEventListener("click", (event) => {
                mem.voltearCarta(carta);
            });
        });
    }

    voltearCarta(carta) {
        if (this.#tablero_bloqueado ||
            carta.dataset.estado === "volteada" ||
            carta.dataset.estado === "revelada") {
                return;
        }

        carta.dataset.estado = "volteada";

        if (this.#primera_carta == null) {
            this.#primera_carta = carta;
            return;
        }

        this.#segunda_carta = carta;
        this.#comprobarPareja();
    }

    #barajarCartas() {
       const tablero = document.querySelector("main");
       const cartas = Array.from(tablero.querySelectorAll("article"));

        for (let i = cartas.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [cartas[i], cartas[j]] = [cartas[j], cartas[i]];
        }

        for (let i = 0; i < cartas.length; i++) {
            tablero.appendChild(cartas[i]);
        }
    }

    #reiniciarAtributos() {
        this.#tablero_bloqueado = false;
        this.#primera_carta = null;
        this.#segunda_carta = null;
    }

    #deshabilitarCartas() {
        this.#primera_carta.dataset.estado = "revelada";
        this.#segunda_carta.dataset.estado = "revelada";

        this.#comprobarJuego();
        this.#reiniciarAtributos();
    }

    #comprobarJuego() {
        const tablero = document.querySelector("main");
        const cartas = Array.from(tablero.querySelectorAll("article"));

        for (let i = 0; i < cartas.length; i++) {
            if (cartas[i].dataset.estado != "revelada") {
                return; 
            }
        }

        this.#cronometro.parar();
        this.#tablero_bloqueado = true;
        alert("Has completado el juego");
    }

    #cubrirCartas() {
        this.#tablero_bloqueado = true;

        setTimeout(() => {
            this.#primera_carta.removeAttribute("data-estado");
            this.#segunda_carta.removeAttribute("data-estado");

            this.#reiniciarAtributos();
        }, 1500);
    }

    #comprobarPareja() {
        const img1 = this.#primera_carta.children[1].getAttribute('src');
        const img2 = this.#segunda_carta.children[1].getAttribute('src');

        (img1 === img2) ? this.#deshabilitarCartas() : this.#cubrirCartas();
    }

}
