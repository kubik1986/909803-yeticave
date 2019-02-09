CREATE DATABASE yeticave
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;
USE yeticave;

CREATE TABLE categories (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  class VARCHAR(100),
  PRIMARY KEY (id),
  UNIQUE INDEX name_uq_idx (name),
  UNIQUE INDEX class_uq_idx (class)
);

CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  password VARCHAR(64) NOT NULL,
  email VARCHAR(255) NOT NULL,
  reg_date DATETIME NOT NULL,
  avatar VARCHAR(255),
  contacts TEXT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX email_uq_idx (email)
);

CREATE TABLE lots (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (id),
  FOREIGN KEY (category_id) REFERENCES categories (id),
  FOREIGN KEY (author_id) REFERENCES users (id),
  FOREIGN KEY (winner_id) REFERENCES users (id),
  FULLTEXT INDEX name_ft_idx (name),
  FULLTEXT INDEX description_ft_idx (description),
  INDEX winner_expiry_date_idx (winner_id,expiry_date)
);

CREATE TABLE bets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  adding_date DATETIME NOT NULL,
  amount INT NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  lot_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users (id),
  FOREIGN KEY (lot_id) REFERENCES lots (id)
);
