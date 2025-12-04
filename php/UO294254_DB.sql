CREATE DATABASE IF NOT EXISTS UO294254_DB;
USE UO294254_DB;

CREATE TABLE profesiones (
    id_profesion INT AUTO_INCREMENT PRIMARY KEY,
    profesion VARCHAR(50) NOT NULL
);

CREATE TABLE generos (
    id_genero INT AUTO_INCREMENT PRIMARY KEY,
    genero VARCHAR(50) NOT NULL
);

CREATE TABLE pericias (
    id_pericia INT AUTO_INCREMENT PRIMARY KEY,
    nivel VARCHAR(50) NOT NULL
);

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_profesion INT NOT NULL,
	edad INT NOT NULL,	
    id_genero INT NOT NULL,
    id_pericia INT NOT NULL,
	FOREIGN KEY (id_profesion) REFERENCES profesiones(id_profesion),
    FOREIGN KEY (id_genero) REFERENCES generos(id_genero),
    FOREIGN KEY (id_pericia) REFERENCES pericias(id_pericia)
);

CREATE TABLE dispositivos (
    id_dispositivo INT AUTO_INCREMENT PRIMARY KEY,
    dispositivo VARCHAR(50) NOT NULL
);

CREATE TABLE tests (
    id_test INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_dispositivo INT NOT NULL,
    tiempo TIME DEFAULT NULL,
    completado BOOLEAN NOT NULL,
    comentarios TEXT,
    mejoras TEXT,
    valoracion INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
	FOREIGN KEY (id_dispositivo) REFERENCES dispositivos(id_dispositivo)
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