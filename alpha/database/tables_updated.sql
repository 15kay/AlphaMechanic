-- 1. Persons Table (must be created first)
CREATE TABLE Persons (
    PersonID INT PRIMARY KEY IDENTITY(1,1),
    FName VARCHAR(100),
    LName VARCHAR(100),
    Gender VARCHAR(10),
    Username VARCHAR(100) UNIQUE,
    Email VARCHAR(100) UNIQUE,
    Password VARCHAR(100),
    PhoneNumber VARCHAR(15),
    PersonType CHAR(2)  -- 'M' for Mechanic, 'A' for Admin, 'CU' for Customer, 'C' for Courier
);

-- 2. Product Table (created before Reservation table)
CREATE TABLE Product (
    ProductID INT PRIMARY KEY IDENTITY(1,1),
    ProductName VARCHAR(255) NOT NULL,
    Description VARCHAR(255),
    Price DECIMAL(10, 2),
    Quantity INT
);
-- Address Table
CREATE TABLE Address (
    AddressID INT PRIMARY KEY IDENTITY(1,1),
    Street VARCHAR(255) NOT NULL,
    Suburb VARCHAR(100),
    Town VARCHAR(100),
    Province VARCHAR(100),
    PostalCode VARCHAR(10)
);

-- 3. Customer Table (after Persons)
CREATE TABLE Customer (
    CustomerID INT PRIMARY KEY IDENTITY(1,1),
    Name VARCHAR(100) NOT NULL,
    PersonID INT NOT NULL,
    AddressID INT,
    FOREIGN KEY (PersonID) REFERENCES Persons(PersonID),
    FOREIGN KEY (AddressID) REFERENCES Address(AddressID)
);

-- 4. Orders Table (after Customer)
CREATE TABLE Orders (
    OrderID INT PRIMARY KEY IDENTITY(1,1),
    OrderNumber VARCHAR(50) NOT NULL,
    Parts VARCHAR(255),
    Status VARCHAR(50),
    OrderDate DATE,
    CustomerID INT,
    TotalAmount DECIMAL(10, 2),
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID)
);

-- 5. Courier Table (after Customer and Orders)
CREATE TABLE Courier (
    CourierID INT PRIMARY KEY IDENTITY(1,1),
    CustomerID INT NOT NULL,
    OrderID INT NOT NULL,
    OrderDate DATE NOT NULL,
    Status VARCHAR(50) NOT NULL,
    CourierService VARCHAR(100),
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID),
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID)
);

-- 6. Admin Table (can be created anytime after Persons)
CREATE TABLE Admin (
    AdminID INT PRIMARY KEY IDENTITY(1,1),
    StaffNumber VARCHAR(50)
);

-- 7. Courier Company Table (after Courier)
CREATE TABLE CourierCompany (
    CourierCompanyID INT PRIMARY KEY IDENTITY(1,1),
    CompanyName VARCHAR(100),
    CourierID INT,
    FOREIGN KEY (CourierID) REFERENCES Courier(CourierID)
);

-- 8. Appointment Table (after Customer)
CREATE TABLE Appointment (
    AppointmentID INT PRIMARY KEY IDENTITY(1,1),
    CustomerID INT,
    CarModel VARCHAR(100),
    ServiceType VARCHAR(100),
    ServiceDate DATE,
    Comments TEXT,
    AppointmentStatus VARCHAR(50),
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID)
);

-- 9. Deliveries Table (after Courier and Orders)
CREATE TABLE Deliveries (
    DeliveryID INT PRIMARY KEY IDENTITY(1,1),
    OrderID INT,
    Street VARCHAR(255),
    Suburb VARCHAR(100),
    Town VARCHAR(100),
    Province VARCHAR(100),
    PostalCode VARCHAR(10),
    ScheduledDate DATE,
    CourierID INT,
    Status VARCHAR(50),
    FOREIGN KEY (CourierID) REFERENCES Courier(CourierID),
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID)
);

-- 10. Completed Deliveries Table (after Deliveries)
CREATE TABLE CompletedDeliveries (
    CompletedID INT PRIMARY KEY IDENTITY(1,1),
    DeliveryID INT,
    DeliveryDate DATE,
    FOREIGN KEY (DeliveryID) REFERENCES Deliveries(DeliveryID)
);

-- 11. Reservation Table (after Customer and Product)
CREATE TABLE Reservation (
    ReservationID INT PRIMARY KEY IDENTITY(1,1),
    CustomerID INT,
    ProductID INT,
    ReservationDate DATE,
    ReservationStatus VARCHAR(50),
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID),
    FOREIGN KEY (ProductID) REFERENCES Product(ProductID)
);

-- 12. Payments Table (after Reservation)
CREATE TABLE Payments (
    PaymentID INT PRIMARY KEY IDENTITY(1,1),
    ReservationID INT,
    Amount DECIMAL(10, 2),
    PaymentDate DATE,
    PaymentMethod VARCHAR(50),
    FOREIGN KEY (ReservationID) REFERENCES Reservation(ReservationID)
);
