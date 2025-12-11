CREATE DATABASE IF NOT EXISTS UO294254_DB;
USE UO294254_DB;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    profesion VARCHAR(50) NOT NULL,
	edad INT NOT NULL,	
    genero VARCHAR(50) NOT NULL,
    nivel VARCHAR(50) NOT NULL
);

CREATE TABLE tests (
    id_test INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    dispositivo VARCHAR(50) NOT NULL,
    tiempo TIME DEFAULT NULL,
    completado BOOLEAN NOT NULL,
    comentarios TEXT,
    mejoras TEXT,
    valoracion INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE observaciones (
    id_facilitador INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    comentario TEXT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE respuestas (
    id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
    id_test INT NOT NULL,
    pregunta VARCHAR(255) NOT NULL,
    respuesta TEXT NOT NULL,
    FOREIGN KEY (id_test) REFERENCES tests(id_test)
);