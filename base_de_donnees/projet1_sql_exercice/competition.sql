\sql


-- 1
CREATE DATABASE competition;
USE competition;

CREATE TABLE COULEUR (
    NumC INT PRIMARY KEY,
    NomC VARCHAR(255),
    CodeE INT,
    CodeP INT,
    FOREIGN KEY (CodeE) REFERENCES EQUIPE(CodeE),
    FOREIGN KEY (CodeP) REFERENCES PAYS(CodeP)
);

CREATE TABLE EQUIPE (
    CodeE INT PRIMARY KEY,
    NomE VARCHAR(255)
);

CREATE TABLE PAYS (
    CodeP INT PRIMARY KEY,
    NomP VARCHAR(255)
);

CREATE TABLE ETAPE (
    NumET INT PRIMARY KEY,
    Date DATE,
    VilleDep VARCHAR(255),
    VilleArr VARCHAR(255),
    NbKrn INT
);

CREATE TABLE PARTICIPER (
    NumC INT,
    NumET INT,
    TempsRealise INT,
    PRIMARY KEY (NumC, NumET),
    FOREIGN KEY (NumC) REFERENCES COULEUR(NumC),
    FOREIGN KEY (NumET) REFERENCES ETAPE(NumET)
);

-- 2
INSERT INTO EQUIPE (CodeE, NomE) VALUES (0, 'Liverpool'), (1, 'AC Milan'), (2, 'Real Madrid');
-- 3
INSERT INTO PAYS (CodeP, NomP) VALUES (0, 'Angleterre'), (1, 'Italie'), (2, 'Espagne');

-- 4
INSERT INTO ETAPE (NumET, Date, VilleDep, VilleArr, NbKrn) VALUES
(0, '2023-11-07', 'Paris', 'Marseille', 774),
(1, '2023-11-08', 'Tunis', 'Nabeul', 60),
(2, '2023-11-09', 'Cotonu', 'Ibadan', 253);

-- 5
INSERT INTO COULEUR (NumC, NomC, CodeE, CodeP) VALUES
(0, 'John', 0, 0),
(1, 'Akrem', 0, 1),
(2, 'Ali', 1, 2),
(3, 'Brad', 1, 1),
(4, 'Chris', 2, 2),
(5, 'James', 2, 0);

-- 6
INSERT INTO PARTICIPER (NumC, NumET, TempsRealise) VALUES
(0, 0, 10),
(0, 1, 20),
(1, 2, 30),
(1, 2, 40),
(2, 2, 50),
(2, 2, 60);

-- 7
SELECT NomC FROM COULEUR
JOIN PARTICIPER ON COULEUR.NumC = PARTICIPER.NumC
WHERE TempsRealise > 30;

-- 8
SELECT NomC FROM COULEUR WHERE NomC LIKE 'a%';

-- 9
SELECT COUNT(DISTINCT NumC) AS NombreDeCOULEURS FROM PARTICIPER WHERE NumET = 2;

-- 10

ALTER TABLE EQUIPE ADD COLUMN CouleurEq VARCHAR(50);

UPDATE EQUIPE SET CouleurEq = 'Rouge' WHERE CodeE = 0;
UPDATE EQUIPE SET CouleurEq = 'Bleu' WHERE CodeE = 1;
UPDATE EQUIPE SET CouleurEq = 'Verte' WHERE CodeE = 2;

SELECT * FROM EQUIPE;