-- =====================================================================
-- CarRental - schema.sql
-- Dung de dung nhanh database `carrentaldb` cho project CarRental_*.
--
-- File nay duoc doi chieu truc tiep voi cau truc bang THAT dang chay
-- tren MySQL/MariaDB cua may (qua SHOW CREATE TABLE / mysqldump --no-data),
-- khong chi suy doan tu code, nen phan CREATE TABLE khop 100% voi
-- database dang dung cho project (bao gom ca cac bang ma code PHP hien
-- tai chua dung toi: discounts, bookingdiscounts, carmaintenance,
-- notifications, reviews, roles - co the danh cho tinh nang mo rong sau nay).
--
-- Cach dung (may khac, database rong):
--   mysql -u root -p < schema.sql
-- hoac import truc tiep file nay bang phpMyAdmin (tab Import).
--
-- Config ket noi mac dinh (CarRental_Backend/config/database.php):
--   host=localhost, user=root, password="", database=carrentaldb, charset=utf8mb4
-- =====================================================================

CREATE DATABASE IF NOT EXISTS carrentaldb
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE carrentaldb;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- roles: RoleID quy uoc dung xuyen suot code PHP (config/auth.php):
-- 1 = Admin, 2 = Staff, 3 = Customer
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
    `RoleID`   INT(11) NOT NULL AUTO_INCREMENT,
    `RoleName` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`RoleID`),
    UNIQUE KEY `RoleName` (`RoleName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- brands (hang xe)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
    `BrandID`   INT(11) NOT NULL AUTO_INCREMENT,
    `BrandName` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`BrandID`),
    UNIQUE KEY `BrandName` (`BrandName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- cartypes (loai xe)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `cartypes`;
CREATE TABLE `cartypes` (
    `TypeID`      INT(11) NOT NULL AUTO_INCREMENT,
    `TypeName`    VARCHAR(100) NOT NULL,
    `Description` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`TypeID`),
    UNIQUE KEY `TypeName` (`TypeName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- discounts (ma giam gia)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `discounts`;
CREATE TABLE `discounts` (
    `DiscountID`       INT(11) NOT NULL AUTO_INCREMENT,
    `Code`             VARCHAR(50) NOT NULL,
    `DiscountPercent`  INT(11) DEFAULT NULL,
    `DiscountAmount`   DECIMAL(12,2) DEFAULT NULL,
    `MinBookingAmount` DECIMAL(12,2) DEFAULT 0.00,
    `ExpiryDate`       DATE DEFAULT NULL,
    `Quantity`         INT(11) DEFAULT 0,
    `Status`           VARCHAR(20) DEFAULT 'Active',
    `Description`      VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`DiscountID`),
    UNIQUE KEY `Code` (`Code`),
    CONSTRAINT `chk_discounts_percent` CHECK (`DiscountPercent` IS NULL OR (`DiscountPercent` >= 0 AND `DiscountPercent` <= 100)),
    CONSTRAINT `chk_discounts_amount` CHECK (`DiscountAmount` IS NULL OR `DiscountAmount` >= 0),
    CONSTRAINT `chk_discounts_min` CHECK (`MinBookingAmount` >= 0),
    CONSTRAINT `chk_discounts_qty` CHECK (`Quantity` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `UserID`                INT(11) NOT NULL AUTO_INCREMENT,
    `FullName`              VARCHAR(150) NOT NULL,
    `Email`                 VARCHAR(150) NOT NULL,
    `Phone`                 VARCHAR(20) DEFAULT NULL,
    -- Luu bcrypt hash (password_hash). Tuong thich nguoc: neu du lieu cu
    -- con la mat khau thuong, api/auth/login.php se tu dong rehash khi
    -- dang nhap thanh cong lan dau.
    `PasswordHash`          VARCHAR(255) NOT NULL,
    `Address`               VARCHAR(255) DEFAULT NULL,
    `Avatar`                VARCHAR(255) DEFAULT NULL,
    `LicenseNumber`         VARCHAR(50) DEFAULT NULL,
    `LicenseFrontImage`     VARCHAR(255) DEFAULT NULL,
    `LicenseBackImage`      VARCHAR(255) DEFAULT NULL,
    `LicenseVerifiedStatus` VARCHAR(30) DEFAULT 'Pending',
    `LicenseUploadedAt`     DATETIME DEFAULT NULL,
    `RoleID`                INT(11) NOT NULL,
    `Status`                VARCHAR(20) DEFAULT 'Active',
    `CreatedAt`             DATETIME DEFAULT CURRENT_TIMESTAMP,
    `UpdatedAt`             DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`UserID`),
    UNIQUE KEY `Email` (`Email`),
    UNIQUE KEY `Phone` (`Phone`),
    KEY `RoleID` (`RoleID`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- cars
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `cars`;
CREATE TABLE `cars` (
    `CarID`         INT(11) NOT NULL AUTO_INCREMENT,
    `CarName`       VARCHAR(150) NOT NULL,
    `BrandID`       INT(11) NOT NULL,
    `TypeID`        INT(11) NOT NULL,
    `Year`          INT(11) NOT NULL,
    `LicensePlate`  VARCHAR(20) NOT NULL,
    `Color`         VARCHAR(50) DEFAULT NULL,
    `Seats`         INT(11) NOT NULL,
    `Transmission`  VARCHAR(50) DEFAULT NULL,
    `FuelType`      VARCHAR(50) DEFAULT NULL,
    `PricePerDay`   DECIMAL(12,2) NOT NULL,
    `DepositAmount` DECIMAL(12,2) DEFAULT 0.00,
    `Status`        VARCHAR(50) DEFAULT 'Available',
    `Description`   VARCHAR(500) DEFAULT NULL,
    `MainImage`     VARCHAR(255) DEFAULT NULL,
    `FolderName`    VARCHAR(255) DEFAULT NULL,
    `Location`      VARCHAR(255) DEFAULT NULL,
    `Mileage`       INT(11) DEFAULT 0,
    `CreatedAt`     DATETIME DEFAULT CURRENT_TIMESTAMP,
    `UpdatedAt`     DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `PricePerHour`  DECIMAL(12,2) DEFAULT 0.00,
    PRIMARY KEY (`CarID`),
    UNIQUE KEY `LicensePlate` (`LicensePlate`),
    KEY `BrandID` (`BrandID`),
    KEY `TypeID` (`TypeID`),
    CONSTRAINT `fk_cars_brand` FOREIGN KEY (`BrandID`) REFERENCES `brands` (`BrandID`),
    CONSTRAINT `fk_cars_type` FOREIGN KEY (`TypeID`) REFERENCES `cartypes` (`TypeID`),
    CONSTRAINT `chk_cars_year` CHECK (`Year` >= 2000 AND `Year` <= 2100),
    CONSTRAINT `chk_cars_seats` CHECK (`Seats` > 0),
    CONSTRAINT `chk_cars_price` CHECK (`PricePerDay` >= 0),
    CONSTRAINT `chk_cars_deposit` CHECK (`DepositAmount` >= 0),
    CONSTRAINT `chk_cars_mileage` CHECK (`Mileage` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- carimages
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `carimages`;
CREATE TABLE `carimages` (
    `ImageID`    INT(11) NOT NULL AUTO_INCREMENT,
    `CarID`      INT(11) NOT NULL,
    `ImageURL`   VARCHAR(255) NOT NULL,
    `IsMain`     TINYINT(1) DEFAULT 0,
    `UploadedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ImageType`  VARCHAR(50) DEFAULT 'gallery',
    PRIMARY KEY (`ImageID`),
    KEY `idx_carimages_carid` (`CarID`),
    CONSTRAINT `fk_carimages_cars` FOREIGN KEY (`CarID`) REFERENCES `cars` (`CarID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- carmaintenance (lich su bao tri xe)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `carmaintenance`;
CREATE TABLE `carmaintenance` (
    `MaintenanceID`   INT(11) NOT NULL AUTO_INCREMENT,
    `CarID`           INT(11) NOT NULL,
    `MaintenanceDate` DATE NOT NULL,
    `Description`     VARCHAR(500) DEFAULT NULL,
    `Cost`            DECIMAL(12,2) DEFAULT 0.00,
    `Status`          VARCHAR(50) DEFAULT 'Completed',
    `GarageName`      VARCHAR(150) DEFAULT NULL,
    `CreatedAt`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`MaintenanceID`),
    KEY `CarID` (`CarID`),
    CONSTRAINT `fk_carmaintenance_car` FOREIGN KEY (`CarID`) REFERENCES `cars` (`CarID`) ON DELETE CASCADE,
    CONSTRAINT `chk_carmaintenance_cost` CHECK (`Cost` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- bookings
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
    `BookingID`        INT(11) NOT NULL AUTO_INCREMENT,
    `UserID`           INT(11) NOT NULL,
    `CarID`            INT(11) NOT NULL,
    `StartDate`        DATE NOT NULL,
    `EndDate`          DATE NOT NULL,
    `PickupLocation`   VARCHAR(255) DEFAULT NULL,
    `ReturnLocation`   VARCHAR(255) DEFAULT NULL,
    `RentalDays`       INT(11) NOT NULL,
    `PricePerDay`      DECIMAL(12,2) NOT NULL,
    `DepositAmount`    DECIMAL(12,2) DEFAULT 0.00,
    `DiscountAmount`   DECIMAL(12,2) DEFAULT 0.00,
    `TotalPrice`       DECIMAL(12,2) NOT NULL,
    `Status`           VARCHAR(50) DEFAULT 'Pending',
    `Note`             VARCHAR(500) DEFAULT NULL,
    `CreatedAt`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `UpdatedAt`        DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `ActualReturnDate` DATETIME DEFAULT NULL,
    `OvertimeFee`      DECIMAL(12,2) DEFAULT 0.00,
    `DamageFee`        DECIMAL(12,2) DEFAULT 0.00,
    `CleaningFee`      DECIMAL(12,2) DEFAULT 0.00,
    `OtherFee`         DECIMAL(12,2) DEFAULT 0.00,
    `TotalPenalty`     DECIMAL(12,2) DEFAULT 0.00,
    `PenaltyReason`    VARCHAR(500) DEFAULT NULL,
    `ReturnFrontImage` VARCHAR(255) DEFAULT NULL,
    `ReturnBackImage`  VARCHAR(255) DEFAULT NULL,
    `ReturnNote`       VARCHAR(500) DEFAULT NULL,
    `ReturnStatus`     VARCHAR(50) DEFAULT 'NotReturned',
    PRIMARY KEY (`BookingID`),
    KEY `UserID` (`UserID`),
    KEY `CarID` (`CarID`),
    CONSTRAINT `fk_bookings_user` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`),
    CONSTRAINT `fk_bookings_car` FOREIGN KEY (`CarID`) REFERENCES `cars` (`CarID`),
    CONSTRAINT `chk_bookings_dates` CHECK (`EndDate` >= `StartDate`),
    CONSTRAINT `chk_bookings_days` CHECK (`RentalDays` > 0),
    CONSTRAINT `chk_bookings_price` CHECK (`PricePerDay` >= 0),
    CONSTRAINT `chk_bookings_deposit` CHECK (`DepositAmount` >= 0),
    CONSTRAINT `chk_bookings_discount` CHECK (`DiscountAmount` >= 0),
    CONSTRAINT `chk_bookings_total` CHECK (`TotalPrice` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- bookingdiscounts (ma giam gia da ap dung cho don thue)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `bookingdiscounts`;
CREATE TABLE `bookingdiscounts` (
    `BookingDiscountID` INT(11) NOT NULL AUTO_INCREMENT,
    `BookingID`         INT(11) NOT NULL,
    `DiscountID`        INT(11) NOT NULL,
    `AppliedValue`      DECIMAL(12,2) DEFAULT 0.00,
    `AppliedAt`         DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`BookingDiscountID`),
    UNIQUE KEY `BookingID` (`BookingID`,`DiscountID`),
    KEY `DiscountID` (`DiscountID`),
    CONSTRAINT `fk_bookingdiscounts_booking` FOREIGN KEY (`BookingID`) REFERENCES `bookings` (`BookingID`) ON DELETE CASCADE,
    CONSTRAINT `fk_bookingdiscounts_discount` FOREIGN KEY (`DiscountID`) REFERENCES `discounts` (`DiscountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- payments
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
    `PaymentID`       INT(11) NOT NULL AUTO_INCREMENT,
    `BookingID`       INT(11) NOT NULL,
    `Amount`          DECIMAL(12,2) NOT NULL,
    `PaymentMethod`   VARCHAR(50) DEFAULT NULL,
    `TransactionCode` VARCHAR(100) DEFAULT NULL,
    `PaymentDate`     DATETIME DEFAULT CURRENT_TIMESTAMP,
    `Status`          VARCHAR(50) DEFAULT 'Pending',
    `PaymentType`     VARCHAR(50) DEFAULT 'Rental',
    `Note`            VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (`PaymentID`),
    KEY `BookingID` (`BookingID`),
    CONSTRAINT `fk_payments_booking` FOREIGN KEY (`BookingID`) REFERENCES `bookings` (`BookingID`) ON DELETE CASCADE,
    CONSTRAINT `chk_payments_amount` CHECK (`Amount` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- reviews (danh gia xe sau khi thue)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
    `ReviewID`   INT(11) NOT NULL AUTO_INCREMENT,
    `UserID`     INT(11) NOT NULL,
    `CarID`      INT(11) NOT NULL,
    `BookingID`  INT(11) DEFAULT NULL,
    `Rating`     INT(11) NOT NULL,
    `Comment`    VARCHAR(500) DEFAULT NULL,
    `ReviewDate` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `Status`     VARCHAR(20) DEFAULT 'Visible',
    PRIMARY KEY (`ReviewID`),
    KEY `UserID` (`UserID`),
    KEY `CarID` (`CarID`),
    KEY `BookingID` (`BookingID`),
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`),
    CONSTRAINT `fk_reviews_car` FOREIGN KEY (`CarID`) REFERENCES `cars` (`CarID`),
    CONSTRAINT `fk_reviews_booking` FOREIGN KEY (`BookingID`) REFERENCES `bookings` (`BookingID`),
    CONSTRAINT `chk_reviews_rating` CHECK (`Rating` >= 1 AND `Rating` <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- blogs
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `blogs`;
CREATE TABLE `blogs` (
    `BlogID`    INT(11) NOT NULL AUTO_INCREMENT,
    `Title`     VARCHAR(255) NOT NULL,
    `Slug`      VARCHAR(255) NOT NULL,
    `Content`   LONGTEXT DEFAULT NULL,
    `Thumbnail` VARCHAR(255) DEFAULT NULL,
    `AuthorID`  INT(11) DEFAULT NULL,
    `CreatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `UpdatedAt` DATETIME DEFAULT NULL,
    `Status`    VARCHAR(20) DEFAULT 'Published',
    PRIMARY KEY (`BlogID`),
    UNIQUE KEY `Slug` (`Slug`),
    KEY `AuthorID` (`AuthorID`),
    CONSTRAINT `fk_blogs_author` FOREIGN KEY (`AuthorID`) REFERENCES `users` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- menus
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
    `MenuID`       INT(11) NOT NULL AUTO_INCREMENT,
    `MenuName`     VARCHAR(100) NOT NULL,
    `URL`          VARCHAR(255) DEFAULT NULL,
    `ParentID`     INT(11) DEFAULT NULL,
    `DisplayOrder` INT(11) DEFAULT 0,
    `IsActive`     TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`MenuID`),
    KEY `ParentID` (`ParentID`),
    CONSTRAINT `fk_menus_parent` FOREIGN KEY (`ParentID`) REFERENCES `menus` (`MenuID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- notifications
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `NotificationID` INT(11) NOT NULL AUTO_INCREMENT,
    `UserID`         INT(11) NOT NULL,
    `Title`          VARCHAR(255) NOT NULL,
    `Message`        VARCHAR(500) DEFAULT NULL,
    `IsRead`         TINYINT(1) DEFAULT 0,
    `CreatedAt`      DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`NotificationID`),
    KEY `UserID` (`UserID`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- SEED DATA (du lieu mau de setup nhanh)
-- =====================================================================

INSERT INTO `roles` (`RoleID`, `RoleName`) VALUES
    (1, 'Admin'),
    (2, 'Staff'),
    (3, 'Customer');

INSERT INTO `brands` (`BrandName`) VALUES
    ('Toyota'), ('Honda'), ('Ford'), ('Hyundai'), ('Kia'), ('Mercedes'), ('Volvo');

INSERT INTO `cartypes` (`TypeName`, `Description`) VALUES
    ('Sedan', 'Xe sedan 4-5 cho'),
    ('SUV', 'Xe the thao da dung'),
    ('Hatchback', 'Xe hatchback nho gon'),
    ('Pickup', 'Xe ban tai'),
    ('MPV', 'Xe gia dinh nhieu cho');

-- Tai khoan mau (mat khau da hash bang password_hash(), dang nhap qua
-- CarRental_Frontend/login.php nhu binh thuong):
--   admin@carrental.com    / admin123
--   staff@carrental.com    / admin123
--   customer@carrental.com / customer123
INSERT INTO `users`
    (`FullName`, `Email`, `Phone`, `PasswordHash`, `Address`, `LicenseVerifiedStatus`, `RoleID`, `Status`)
VALUES
    ('Quản trị viên', 'admin@carrental.com', '0900000001',
     '$2y$10$HCal/b3qMIaKO21xp5JSiOO/dDONi7hTMtCyy2sMqIg2g.FGiRIja',
     'Trụ sở chính', 'Verified', 1, 'Active'),
    ('Nhân viên hỗ trợ', 'staff@carrental.com', '0900000002',
     '$2y$10$HCal/b3qMIaKO21xp5JSiOO/dDONi7hTMtCyy2sMqIg2g.FGiRIja',
     'Trụ sở chính', 'Verified', 2, 'Active'),
    ('Nguyễn Văn Khách', 'customer@carrental.com', '0900000003',
     '$2y$10$DttYvtoFV/06quCNd7/Kz.UKEjfuXK7F6Wt8GxT02h65WMY7jmwU.',
     '123 Đường ABC, Quận 1, TP.HCM', 'Pending', 3, 'Active');

-- Xe mau
INSERT INTO `cars`
    (`CarName`, `BrandID`, `TypeID`, `Year`, `LicensePlate`, `Color`, `Seats`,
     `Transmission`, `FuelType`, `PricePerDay`, `DepositAmount`, `Status`,
     `Description`, `MainImage`, `FolderName`, `Location`, `Mileage`)
VALUES
    ('Toyota Vios 2022', 1, 1, 2022, '51A-123.45', 'Trắng', 5,
     'Automatic', 'Gasoline', 600000, 2000000, 'Available',
     'Xe sedan tiết kiệm nhiên liệu, phù hợp di chuyển trong thành phố.',
     '', '', 'TP.HCM', 15000),
    ('Honda CR-V 2023', 2, 2, 2023, '51A-678.90', 'Đen', 7,
     'Automatic', 'Gasoline', 1200000, 4000000, 'Available',
     'SUV 7 chỗ rộng rãi, phù hợp đi du lịch gia đình.',
     '', '', 'TP.HCM', 8000),
    ('Hyundai Accent 2023', 4, 3, 2023, '51A-111.22', 'Xanh', 5,
     'Manual', 'Gasoline', 500000, 1500000, 'Maintenance',
     'Xe hatchback nhỏ gọn, dễ di chuyển trong phố.',
     '', '', 'Hà Nội', 5000);

-- Menu mau
INSERT INTO `menus` (`MenuName`, `URL`, `ParentID`, `DisplayOrder`, `IsActive`) VALUES
    ('Trang chủ', 'index.php', NULL, 1, 1),
    ('Xe cho thuê', 'vehicle.php', NULL, 2, 1),
    ('Tin tức', 'blog.php', NULL, 3, 1),
    ('Liên hệ', 'contact.php', NULL, 4, 1);

-- Bai viet mau
INSERT INTO `blogs` (`Title`, `Slug`, `Content`, `Thumbnail`, `AuthorID`, `Status`) VALUES
    ('Kinh nghiệm thuê xe tự lái lần đầu', 'kinh-nghiem-thue-xe-tu-lai-lan-dau',
     '<p>Một vài lưu ý quan trọng khi bạn thuê xe tự lái lần đầu tiên...</p>',
     '', 1, 'Published');

-- Booking + payment mau (khach hang UserID=3 thue xe CarID=1)
INSERT INTO `bookings`
    (`UserID`, `CarID`, `StartDate`, `EndDate`, `PickupLocation`, `ReturnLocation`,
     `RentalDays`, `PricePerDay`, `DepositAmount`, `DiscountAmount`, `TotalPrice`, `Status`)
VALUES
    (3, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'Quận 1, TP.HCM', 'Quận 1, TP.HCM',
     2, 600000, 2000000, 0, 1200000, 'Pending');

INSERT INTO `payments`
    (`BookingID`, `Amount`, `PaymentMethod`, `PaymentType`, `TransactionCode`, `Status`, `Note`)
VALUES
    (1, 2000000, NULL, 'Deposit', '', 'Pending', 'Thanh toán tiền cọc giữ xe: Toyota Vios 2022');
