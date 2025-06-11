-- Create database
CREATE DATABASE IF NOT EXISTS avi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE avi_db;

-- Create usuarios table
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Create ideas table
CREATE TABLE IF NOT EXISTS ideas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    tipo_idea VARCHAR(50) NOT NULL,
    descripcion_corta VARCHAR(200) NOT NULL,
    motivacion TEXT,
    problema_oportunidad TEXT,
    ubicacion VARCHAR(100),
    audiencia_objetivo TEXT,
    diferenciador TEXT,
    recursos_disponibles TEXT,
    primer_paso TEXT,
    posibles_barreras TEXT,
    vision_6_meses TEXT,
    medicion_exito TEXT,
    fecha_recordatorio DATE,
    estado VARCHAR(20) DEFAULT 'activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create ideas_explorador table
CREATE TABLE IF NOT EXISTS ideas_explorador (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo_idea VARCHAR(50) NOT NULL,
    descripcion_corta VARCHAR(200) NOT NULL,
    motivacion TEXT,
    problema_oportunidad TEXT,
    ubicacion VARCHAR(100),
    audiencia_objetivo TEXT,
    recursos_disponibles TEXT,
    primer_paso TEXT,
    recomendaciones JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Create seguimiento_ideas table
CREATE TABLE IF NOT EXISTS seguimiento_ideas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_idea INT NOT NULL,
    detalle TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado_actual VARCHAR(50) NOT NULL,
    archivo_opcional VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_idea) REFERENCES ideas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create notificaciones_ideas table
CREATE TABLE IF NOT EXISTS notificaciones_ideas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_idea INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leida BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_idea) REFERENCES ideas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Add indexes for better performance
CREATE INDEX idx_ideas_usuario ON ideas(id_usuario);
CREATE INDEX idx_seguimiento_idea ON seguimiento_ideas(id_idea);
CREATE INDEX idx_notificaciones_idea ON notificaciones_ideas(id_idea);
CREATE INDEX idx_ideas_tipo ON ideas(tipo_idea);
CREATE INDEX idx_ideas_estado ON ideas(estado);
