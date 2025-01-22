CREATE DATABASE dbmanager;

USE dbmanager;

CREATE TABLE cliente (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo_pessoa ENUM('F', 'J') NOT NULL,
    razao_social VARCHAR(100),
    cnpj VARCHAR(20),
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(15),
    cep VARCHAR(10) NOT NULL,
    rua VARCHAR(100) NOT NULL,
    numero VARCHAR(10) NOT NULL,
    complemento VARCHAR(50),
    bairro VARCHAR(50) NOT NULL,
    cidade VARCHAR(50) NOT NULL,
    estado CHAR(2) NOT NULL,
    coordenada VARCHAR(50),
    celular VARCHAR(25) NOT NULL,
    email VARCHAR(100) NOT NULL,
    codigo_cnae VARCHAR(255),
    descricao_cnae TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_tipo_pessoa CHECK (
        (tipo_pessoa = 'F' AND cpf IS NOT NULL AND razao_social IS NULL AND cnpj IS NULL) OR
        (tipo_pessoa = 'J' AND cnpj IS NOT NULL AND razao_social IS NOT NULL AND cpf IS NULL)
    )
);
ALTER TABLE cliente ADD COLUMN descricao_cnae TEXT AFTER codigo_cnae;


CREATE TABLE servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_proposta INT,
    cliente_id INT NOT NULL,
    cnpj_cpf VARCHAR(20) NOT NULL,
    cep VARCHAR(10) NOT NULL,
    rua VARCHAR(100) NOT NULL,
    numero VARCHAR(10) NOT NULL,
    complemento VARCHAR(50),
    bairro VARCHAR(50) NOT NULL,
    cidade VARCHAR(50) NOT NULL,
    estado CHAR(2) NOT NULL,
    coordenada VARCHAR(50),
    data_inicio DATE NOT NULL,
    data_termino DATE,
    data_pagamento DATE NOT NULL,
    valor_total DECIMAL(10, 2) NOT NULL,
    valor_entrada DECIMAL(10, 2),
    forma_pagamento VARCHAR(50),
    parcelamento INT,
    status_servico VARCHAR(30) NOT NULL,
    responsavel_execucao VARCHAR(100) NOT NULL,
    origem_demanda VARCHAR(50) NOT NULL,
    observacao TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES cliente(id)
);
ALTER TABLE servicos ADD COLUMN observacao TEXT after origem_demanda;



CREATE TABLE pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_proposta INT NOT NULL,
    parcela_num INT NOT NULL,
    status_pagamento VARCHAR(30) NOT NULL,
    valor_parcela DECIMAL(10, 2) NOT NULL,
    data_pagamento DATE NOT NULL,
    dia_pagamento DATE NOT NULL, 
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE despesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proposta INT,
    nome_despesa VARCHAR(100) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE despesas ADD COLUMN data TIMESTAMP DEFAULT CURRENT_TIMESTAMP;


CREATE TABLE tipos_servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_servico VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE servico_tipo_servico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    servico_id INT NOT NULL,
    tipo_servico_id INT NOT NULL,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (tipo_servico_id) REFERENCES tipos_servicos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY (servico_id, tipo_servico_id)
);


CREATE TABLE areas_atuacao (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
);


CREATE TABLE empresa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    razao_social VARCHAR(100),
    cnpj VARCHAR(20),
    nome VARCHAR(100),
    cpf VARCHAR(15),
    cep VARCHAR(10),
    rua VARCHAR(100),
    numero VARCHAR(10),
    complemento VARCHAR(50),
    bairro VARCHAR(50),
    cidade VARCHAR(50),
    estado CHAR(2),
    coordenada VARCHAR(50),
    telefone VARCHAR(25),
    celular VARCHAR(25),
    email VARCHAR(100),
    logo MEDIUMBLOB,
    atividades_secundarias TEXT;
    descricoes_secundarias TEXT;
    codigo_cnae VARCHAR(255),
    descricao_cnae TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE empresa ADD COLUMN atividades_secundarias TEXT AFTER email;
ALTER TABLE empresa ADD COLUMN descricoes_secundarias TEXT AFTER atividades_secundarias;
ALTER TABLE empresa ADD COLUMN descricao_cnae TEXT AFTER codigo_cnae;


CREATE TABLE despesas_fixas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE logos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_data MEDIUMBLOB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Apenas crie estas tabelas se ainda não existirem
CREATE TABLE IF NOT EXISTS socios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    porcentagem_sociedade DECIMAL(5,2) NOT NULL,
    porcentagem_comissao DECIMAL(5,2) NOT NULL,
    valor_pro_labore DECIMAL(10,2) DEFAULT 0.00
);

CREATE TABLE IF NOT EXISTS retiradas_socios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    socio_id INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    tipo ENUM('LABORE', 'COMISSAO', 'DISTRIBUICAO') NOT NULL,
    mes INT NOT NULL,
    ano INT NOT NULL,
    data_retirada DATE NOT NULL,
    FOREIGN KEY (socio_id) REFERENCES socios(id)
);

CREATE TABLE favoritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    card_id VARCHAR(50) NOT NULL,
    ordem INT NOT NULL,
    UNIQUE KEY unique_favorito (usuario_id, card_id)
);