CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    profesion VARCHAR(50) NOT NULL,
    edad INT NOT NULL,	
    genero VARCHAR(50) NOT NULL,
    nivel VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS tests (
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

CREATE TABLE IF NOT EXISTS observaciones (
    id_facilitador INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    comentario TEXT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE IF NOT EXISTS respuestas (
    id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
    id_test INT NOT NULL,
    pregunta VARCHAR(255) NOT NULL,
    respuesta TEXT NOT NULL,
    FOREIGN KEY (id_test) REFERENCES tests(id_test)
);