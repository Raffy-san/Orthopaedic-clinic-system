CREATE TABLE IF NOT EXISTS notifications (
  NotificationID int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  UserID int(10) UNSIGNED NOT NULL,
  Title varchar(150) NOT NULL,
  Message text NOT NULL,
  Type varchar(50) NOT NULL DEFAULT 'general',
  ReferenceID int(10) UNSIGNED DEFAULT NULL,
  IsRead tinyint(1) NOT NULL DEFAULT 0,
  CreatedAt timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (NotificationID),
  KEY idx_notifications_user_read (UserID, IsRead, CreatedAt),
  CONSTRAINT fk_notifications_user FOREIGN KEY (UserID) REFERENCES users (UserID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;