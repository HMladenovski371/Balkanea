Ammenities tabeli

mali bagovi popraveni -- Табела за групи на аменитети
CREATE TABLE IF NOT EXISTS Y7FXuNUTt_amenity_groups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL
);
-- Табела за аменитети
CREATE TABLE IF NOT EXISTS Y7FXuNUTt_amenities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    popularity INT UNSIGNED NOT NULL DEFAULT 0
);
-- Релации аменитет :left_right_arrow: група
CREATE TABLE IF NOT EXISTS Y7FXuNUTt_amenity_group_relations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    amenity_id INT UNSIGNED NOT NULL,
    amenity_group_id INT UNSIGNED NOT NULL,
    UNIQUE KEY unique_amenity_group (amenity_id, amenity_group_id),
    FOREIGN KEY (amenity_id) REFERENCES Y7FXuNUTt_amenities(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_group_id) REFERENCES Y7FXuNUTt_amenity_groups(id) ON DELETE CASCADE
);
-- Хотел :left_right_arrow: група на аменитети
CREATE TABLE IF NOT EXISTS Y7FXuNUTt_hotel_amenity_groups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hotel_hid VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    amenity_group_id INT UNSIGNED NOT NULL,
    UNIQUE KEY unique_hotel_group (hotel_hid, amenity_group_id),
    FOREIGN KEY (hotel_hid) REFERENCES Y7FXuNUTt_st_hotel(external_hid) ON DELETE CASCADE,
    FOREIGN KEY (amenity_group_id) REFERENCES Y7FXuNUTt_amenity_groups(id) ON DELETE CASCADE
);
-- Соба :left_right_arrow: аменитети
CREATE TABLE IF NOT EXISTS Y7FXuNUTt_room_amenities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT UNSIGNED NOT NULL,
    amenity_id INT UNSIGNED NOT NULL,
    is_free BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_room_amenity (post_id, amenity_id),
    FOREIGN KEY (post_id) REFERENCES Y7FXuNUTt_posts(ID) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES Y7FXuNUTt_amenities(id) ON DELETE CASCADE
);



ALTER TABLE Y7FXuNUTt_amenities 
ADD COLUMN is_free TINYINT(1) NOT NULL DEFAULT 0;


ALTER TABLE Y7FXuNUTt_amenities 
MODIFY COLUMN popularity TINYINT UNSIGNED NOT NULL DEFAULT 0;



//
CREATE TABLE Y7FXuNUTt_rg_ext (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    hotel_room_id BIGINT NOT NULL,
    class INT DEFAULT 0,
    quality INT DEFAULT 0,
    sex INT DEFAULT 0,
    bathroom INT DEFAULT 0,
    bedding INT DEFAULT 0,
    family INT DEFAULT 0,
    capacity INT DEFAULT 0,
    club INT DEFAULT 0,
    bedrooms INT DEFAULT 0,
    balcony INT DEFAULT 0,
    floor INT DEFAULT 0,
    view INT DEFAULT 0,
    FOREIGN KEY (hotel_room_id) REFERENCES Y7FXuNUTt_hotel_room(id) ON DELETE CASCADE
);

//Reviews

DROP TABLE IF EXISTS reviews; DROP TABLE IF EXISTS hotel_reviews;
CREATE TABLE Y7FXUNUTt_reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hid_id VARCHAR(255) NOT NULL,
    review_id INT UNSIGNED NOT NULL,
    review_plus TEXT NULL,
    review_minus TEXT NULL,
    created_date DATE NOT NULL,
    author VARCHAR(255) NOT NULL,
    adults_count TINYINT UNSIGNED NOT NULL DEFAULT 1,
    children_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
    room_name VARCHAR(500) NOT NULL,
    nights_count TINYINT UNSIGNED NOT NULL DEFAULT 1,
    overall_rating DECIMAL(3,1) NOT NULL,
    cleanness_rating DECIMAL(3,1) NULL,
    location_rating DECIMAL(3,1) NULL,
    price_rating DECIMAL(3,1) NULL,
    services_rating DECIMAL(3,1) NULL,
    room_rating DECIMAL(3,1) NULL,
    meal_rating DECIMAL(3,1) NULL,
    wifi_quality VARCHAR(20) DEFAULT 'unspecified',
    hygiene_quality VARCHAR(20) DEFAULT 'unspecified',
    traveller_type VARCHAR(20) DEFAULT 'unspecified',
    trip_type VARCHAR(20) DEFAULT 'unspecified',
    images JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX unique_review_id (review_id),
    INDEX idx_review_id (review_id),
    INDEX idx_created_date (created_date),
    INDEX idx_author (author),
    INDEX idx_overall_rating (overall_rating),
    INDEX idx_traveller_type (traveller_type),
    INDEX idx_trip_type (trip_type),
    INDEX idx_hid_id (hid_id),
    CONSTRAINT fk_reviews_hotel FOREIGN KEY (hid_id)
        REFERENCES Y7FXuNUTt_st_hotel(external_hid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Y7FXuNUTt_hotel_reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hid_id VARCHAR(255) NOT NULL,
    overall_rating DECIMAL(3,1) NOT NULL,
    cleanness_rating DECIMAL(3,1) NULL,
    location_rating DECIMAL(3,1) NULL,
    price_rating DECIMAL(3,1) NULL,
    services_rating DECIMAL(3,1) NULL,
    room_rating DECIMAL(3,1) NULL,
    meal_rating DECIMAL(3,1) NULL,
    wifi_rating DECIMAL(3,1) NULL,
    hygiene_rating DECIMAL(3,1) NULL,
    total_reviews INT UNSIGNED NOT NULL DEFAULT 0,
    last_review_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX unique_hotel_review (hid_id),
    INDEX idx_hid_id (hid_id),
    INDEX idx_overall_rating (overall_rating),
    CONSTRAINT fk_hotel_reviews_hotel FOREIGN KEY (hid_id)
        REFERENCES Y7FXuNUTt_st_hotel(external_hid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


