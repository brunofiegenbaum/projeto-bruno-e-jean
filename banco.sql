CREATE DATABASE IF NOT EXISTS projetosemestre;

USE projetosemestre;

CREATE TABLE
    IF NOT EXISTS usuarios (
        id_usuario INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL
    );

CREATE TABLE
    IF NOT EXISTS veiculos (
        id_veiculo INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        marca VARCHAR(50) NOT NULL,
        modelo VARCHAR(50) NOT NULL,
        ano INT NOT NULL,
        preco DECIMAL(10, 2) NOT NULL,
        quilometragem INT NOT NULL DEFAULT 0,
        foto VARCHAR(255) DEFAULT NULL,
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
    );

-- Insere usuarios de exemplo somente quando o email ainda nao existe.
INSERT INTO
    usuarios (nome, email)
SELECT
    'Bruno',
    'bruno@exemplo.com'
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            usuarios
        WHERE
            email = 'bruno@exemplo.com'
    );

INSERT INTO
    usuarios (nome, email)
SELECT
    'Jean',
    'jean@exemplo.com'
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            usuarios
        WHERE
            email = 'jean@exemplo.com'
    );

INSERT INTO
    usuarios (nome, email)
SELECT
    'Carlos',
    'carlos@exemplo.com'
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            usuarios
        WHERE
            email = 'carlos@exemplo.com'
    );

INSERT INTO
    usuarios (nome, email)
SELECT
    'Ana',
    'ana@exemplo.com'
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            usuarios
        WHERE
            email = 'ana@exemplo.com'
    );

INSERT INTO
    usuarios (nome, email)
SELECT
    'Pedro',
    'pedro@exemplo.com'
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            usuarios
        WHERE
            email = 'pedro@exemplo.com'
    );