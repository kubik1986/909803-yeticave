CREATE DATABASE 909803_yeticave
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;
USE 909803_yeticave;

CREATE TABLE categories (
  category_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  class VARCHAR(100) UNIQUE
);

CREATE TABLE users (
  user_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  password VARCHAR(64) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  reg_date DATETIME NOT NULL,
  avatar VARCHAR(255),
  contacts TEXT NOT NULL
);

CREATE TABLE lots (
  lot_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  img VARCHAR(255) NOT NULL,
  price INT UNSIGNED NOT NULL,
  adding_date DATETIME NOT NULL,
  expiry_date DATE NOT NULL,
  bet_step INT UNSIGNED NOT NULL,
  category_id SMALLINT UNSIGNED NOT NULL,
  author_id INT UNSIGNED NOT NULL,
  winner_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (category_id) REFERENCES categories (category_id),
  FOREIGN KEY (author_id) REFERENCES users (user_id),
  FOREIGN KEY (winner_id) REFERENCES users (user_id),
  FULLTEXT INDEX name_description_ft_idx (name,description),
  INDEX winner_expiry_date_idx (winner_id,expiry_date)
);

CREATE TABLE bets (
  bet_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  adding_date DATETIME NOT NULL,
  amount INT NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  lot_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users (user_id),
  FOREIGN KEY (lot_id) REFERENCES lots (lot_id)
);
