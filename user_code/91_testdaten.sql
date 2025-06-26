-- =============================================================================
-- TESTDATEN FÜR MOBS SYSTEM
-- =============================================================================
-- 5000 Mitglieder in 5 Regionalverbänden (Niedersachsen)
-- Jeweils 9-15 BSG pro Regionalverband
-- Alle y_user haben Rollenstufe 8
-- Vollständige individuelle Berechtigungen basierend auf BSG-Zugehörigkeit
-- =============================================================================

-- Deaktiviere Fremdschlüssel-Checks für bessere Performance
SET FOREIGN_KEY_CHECKS = 0;
SET AUTOCOMMIT = 0;

-- =============================================================================
-- 0. TRUNCATE ALLER RELEVANTEN TABELLEN (FÜR WIEDERHOLBARE AUSFÜHRUNG)
-- =============================================================================

TRUNCATE TABLE `b_individuelle_berechtigungen`;
TRUNCATE TABLE `b_mitglieder_in_sparten`;
TRUNCATE TABLE `b_mitglieder`;
TRUNCATE TABLE `y_user_details`;
TRUNCATE TABLE `y_user`;
TRUNCATE TABLE `b_sparte`;
TRUNCATE TABLE `b_bsg`;
TRUNCATE TABLE `b_regionalverband`;

-- Reset Auto-Increment
ALTER TABLE `b_mitglieder` AUTO_INCREMENT = 100001;
ALTER TABLE `y_user` AUTO_INCREMENT = 1000;

-- =============================================================================
-- 1. REGIONALVERBÄNDE (5 Städte in Niedersachsen)
-- =============================================================================

INSERT INTO `b_regionalverband` (`Verband`, `Kurzname`, `Internetadresse`, `BKV`, `Basisbeitrag`) VALUES
('BSV Hannover e.V.', 'BSV-H', 'www.bsv-hannover.de', 3001, 45.00),
('BSV Braunschweig e.V.', 'BSV-BS', 'www.bsv-braunschweig.de', 3002, 42.50),
('BSV Oldenburg e.V.', 'BSV-OL', 'www.bsv-oldenburg.de', 3003, 40.00),
('BSV Osnabrück e.V.', 'BSV-OS', 'www.bsv-osnabrueck.de', 3004, 43.75),
('BSV Göttingen e.V.', 'BSV-GÖ', 'www.bsv-goettingen.de', 3005, 41.25);

-- =============================================================================
-- 2. BSG (BETRIEBSSPORTGEMEINSCHAFTEN)
-- =============================================================================

-- BSG für Hannover (12 BSG)
INSERT INTO `b_bsg` (`Verband`, `BSG`, `RE_Name`, `RE_Name2`, `RE_Strasse_Nr`, `RE_Strasse2`, `RE_PLZ_Ort`, `VKZ`) VALUES
(1, 'BSG Volkswagen Nutzfahrzeuge', 'VW Nutzfahrzeuge', 'Personalabteilung', 'Frankfurter Str. 123', 'Gebäude B4', '30419 Hannover', 'VW001'),
(1, 'BSG Continental AG', 'Continental AG', 'HR-Abteilung', 'Vahrenwalder Str. 9', 'Postfach 169', '30165 Hannover', 'CO001'),
(1, 'BSG TUI Deutschland', 'TUI Deutschland GmbH', 'Mitarbeitervertretung', 'Karl-Wiechert-Allee 4', '', '30625 Hannover', 'TUI01'),
(1, 'BSG Hannover Rück', 'Hannover Rück SE', 'Personalwesen', 'Karl-Wiechert-Allee 50', '', '30625 Hannover', 'HR001'),
(1, 'BSG WABCO', 'WABCO Europe BVBA', 'Betriebsrat', 'Am Lindener Hafen 21', '', '30453 Hannover', 'WAB01'),
(1, 'BSG Johnson Controls', 'Johnson Controls', 'Personalabteilung', 'Vahrenwalder Str. 269-273', '', '30179 Hannover', 'JC001'),
(1, 'BSG Bahlsen', 'Bahlsen GmbH & Co. KG', 'HR', 'Podbielskistr. 11', '', '30163 Hannover', 'BAH01'),
(1, 'BSG Sennheiser', 'Sennheiser electronic', 'Betriebssport', 'Am Labor 1', '', '30900 Wedemark', 'SEN01'),
(1, 'BSG Stadtwerke Hannover', 'Stadtwerke Hannover AG', 'Personalrat', 'Ihmeplatz 2', '', '30449 Hannover', 'SWH01'),
(1, 'BSG Region Hannover', 'Region Hannover', 'Personalamt', 'Hildesheimer Str. 20', '', '30169 Hannover', 'RH001'),
(1, 'BSG ÜSTRA', 'ÜSTRA Hannoversche Verkehrsbetriebe', 'Mitarbeitervertretung', 'Göttinger Chaussee 76', '', '30453 Hannover', 'UES01'),
(1, 'BSG Deutsche Messe', 'Deutsche Messe AG', 'HR', 'Messegelände', '', '30521 Hannover', 'DM001');

-- BSG für Braunschweig (11 BSG)
INSERT INTO `b_bsg` (`Verband`, `BSG`, `RE_Name`, `RE_Name2`, `RE_Strasse_Nr`, `RE_Strasse2`, `RE_PLZ_Ort`, `VKZ`) VALUES
(2, 'BSG Volkswagen Braunschweig', 'Volkswagen AG', 'Werk Braunschweig', 'Gifhorner Str. 57', '', '38112 Braunschweig', 'VW002'),
(2, 'BSG Siemens Braunschweig', 'Siemens AG', 'Standort Braunschweig', 'Theodor-Heuss-Str. 2', '', '38122 Braunschweig', 'SIE01'),
(2, 'BSG Bosch Salzgitter', 'Robert Bosch GmbH', 'Werk Salzgitter', 'Zur Bettfedernfabrik 1', '', '38229 Salzgitter', 'BOS01'),
(2, 'BSG MAN Salzgitter', 'MAN Truck & Bus SE', 'Werk Salzgitter', 'Salzgitterscher Str. 200', '', '38239 Salzgitter', 'MAN01'),
(2, 'BSG Peiner Träger', 'Peiner Träger GmbH', 'Personalwesen', 'Gerhard-Domagk-Str. 5-7', '', '31226 Peine', 'PEI01'),
(2, 'BSG Stadt Braunschweig', 'Stadt Braunschweig', 'Personalamt', 'Platz der Deutschen Einheit 1', '', '38100 Braunschweig', 'SBS01'),
(2, 'BSG TU Braunschweig', 'Technische Universität', 'Personaldezernat', 'Universitätsplatz 2', '', '38106 Braunschweig', 'TUB01'),
(2, 'BSG Salzgitter AG', 'Salzgitter AG', 'Konzernpersonalwesen', 'Eisenhüttenstr. 99', '', '38239 Salzgitter', 'SAG01'),
(2, 'BSG BS Energy', 'BS Energy GmbH', 'Betriebsrat', 'Taubenstr. 7', '', '38106 Braunschweig', 'BSE01'),
(2, 'BSG Alstom', 'Alstom Transport Deutschland', 'HR-Abteilung', 'Lindenstr. 18', '', '38300 Wolfenbüttel', 'ALS01'),
(2, 'BSG Volkswagen Financial Services', 'VW FS AG', 'Personalbetreuung', 'Gifhorner Str. 57', '', '38112 Braunschweig', 'VWF01');

-- BSG für Oldenburg (10 BSG)
INSERT INTO `b_bsg` (`Verband`, `BSG`, `RE_Name`, `RE_Name2`, `RE_Strasse_Nr`, `RE_Strasse2`, `RE_PLZ_Ort`, `VKZ`) VALUES
(3, 'BSG EWE AG', 'EWE AG', 'Personalwesen', 'Tirpitzstr. 39', '', '26122 Oldenburg', 'EWE01'),
(3, 'BSG CEWE', 'CEWE Stiftung & Co. KGaA', 'HR', 'Meerweg 30-32', '', '26133 Oldenburg', 'CEW01'),
(3, 'BSG Uni Oldenburg', 'Carl von Ossietzky Universität', 'Personaldezernat', 'Ammerländer Heerstr. 114-118', '', '26129 Oldenburg', 'UOL01'),
(3, 'BSG Stadt Oldenburg', 'Stadt Oldenburg', 'Personalamt', 'Pferdemarkt 14', '', '26121 Oldenburg', 'SOL01'),
(3, 'BSG OOWV', 'Oldenburgisch-Ostfriesischer Wasserverband', 'Personal', 'Georgstr. 4', '', '26919 Brake', 'OOW01'),
(3, 'BSG Landessparkasse', 'Landessparkasse zu Oldenburg', 'Personalbetreuung', 'Berliner Platz 1', '', '26123 Oldenburg', 'LSO01'),
(3, 'BSG Nordwest-Zeitung', 'Nordwest-Zeitung', 'Redaktion/Verwaltung', 'Peterstr. 28-34', '', '26121 Oldenburg', 'NWZ01'),
(3, 'BSG BTC AG', 'BTC Business Technology', 'HR-Team', 'Escherweg 5', '', '26121 Oldenburg', 'BTC01'),
(3, 'BSG Jade Hochschule', 'Jade Hochschule', 'Personalverwaltung', 'Friedrich-Paffrath-Str. 101', '', '26389 Wilhelmshaven', 'JAD01'),
(3, 'BSG ROSEN Gruppe', 'ROSEN Technology', 'Personalwesen', 'Lingen Str. 2', '', '49811 Lingen', 'ROS01');

-- BSG für Osnabrück (13 BSG)
INSERT INTO `b_bsg` (`Verband`, `BSG`, `RE_Name`, `RE_Name2`, `RE_Strasse_Nr`, `RE_Strasse2`, `RE_PLZ_Ort`, `VKZ`) VALUES
(4, 'BSG Uni Osnabrück', 'Universität Osnabrück', 'Personaldezernat', 'Neuer Graben 29', '', '49074 Osnabrück', 'UOS01'),
(4, 'BSG Stadt Osnabrück', 'Stadt Osnabrück', 'Personalamt', 'Bierstr. 28', '', '49074 Osnabrück', 'SOS01'),
(4, 'BSG Stadtwerke Osnabrück', 'Stadtwerke Osnabrück AG', 'Personal', 'Alte Poststr. 9', '', '49074 Osnabrück', 'SWO01'),
(4, 'BSG Hochschule Osnabrück', 'Hochschule Osnabrück', 'Personalverwaltung', 'Albrechtstr. 30', '', '49076 Osnabrück', 'HSO01'),
(4, 'BSG KME Germany', 'KME Germany AG', 'HR-Abteilung', 'Klosterstr. 29', '', '49074 Osnabrück', 'KME01'),
(4, 'BSG Amazonen-Werke', 'Amazonen-Werke H. Dreyer', 'Personalwesen', 'Am Amazonen-Werke 9', '', '49205 Hasbergen', 'AMA01'),
(4, 'BSG Hellmann Worldwide', 'Hellmann Worldwide Logistics', 'HR', 'Elbestr. 1', '', '49078 Osnabrück', 'HEL01'),
(4, 'BSGFel­ix Schoeller Gruppe', 'Felix Schoeller Group', 'Personalbetreuung', 'Burg Gretesch', '', '49086 Osnabrück', 'FSG01'),
(4, 'BSG Landkreis Osnabrück', 'Landkreis Osnabrück', 'Personalamt', 'Am Schölerberg 1', '', '49082 Osnabrück', 'LOS01'),
(4, 'BSG Georgsmarienhütte', 'GMH Gruppe', 'Personalwesen', 'Duesbergweg 24', '', '49124 Georgsmarienhütte', 'GMH01'),
(4, 'BSG Sparkasse Osnabrück', 'Sparkasse Osnabrück', 'Personalbetreuung', 'Möserstr. 29', '', '49074 Osnabrück', 'SPO01'),
(4, 'BSG WAGO', 'WAGO Kontakttechnik', 'HR-Department', 'Hansastr. 27', '', '32423 Minden', 'WAG01'),
(4, 'BSG Piesberg', 'Museum Industriekultur', 'Verwaltung', 'Fürstenauer Weg 171', '', '49090 Osnabrück', 'PIE01');

-- BSG für Göttingen (9 BSG)
INSERT INTO `b_bsg` (`Verband`, `BSG`, `RE_Name`, `RE_Name2`, `RE_Strasse_Nr`, `RE_Strasse2`, `RE_PLZ_Ort`, `VKZ`) VALUES
(5, 'BSG Uni Göttingen', 'Georg-August-Universität', 'Personaldezernat', 'Wilhelmsplatz 1', '', '37073 Göttingen', 'UGÖ01'),
(5, 'BSG Max-Planck-Gesellschaft', 'Max-Planck-Gesellschaft', 'Personalwesen', 'Am Fassberg 11', '', '37077 Göttingen', 'MPG01'),
(5, 'BSG Sartorius AG', 'Sartorius AG', 'HR', 'Otto-Brenner-Str. 20', '', '37079 Göttingen', 'SAR01'),
(5, 'BSG Stadt Göttingen', 'Stadt Göttingen', 'Personalamt', 'Hiroshimaplatz 1-4', '', '37083 Göttingen', 'SGÖ01'),
(5, 'BSG Universitätsmedizin', 'Universitätsmedizin Göttingen', 'Personaldezernat', 'Robert-Koch-Str. 40', '', '37075 Göttingen', 'UMG01'),
(5, 'BSG Ottobock', 'Ottobock SE & Co. KGaA', 'Personalbetreuung', 'Max-Näder-Str. 15', '', '37115 Duderstadt', 'OTT01'),
(5, 'BSG Deutsches Primatenzentrum', 'Deutsches Primatenzentrum', 'Verwaltung', 'Kellnerweg 4', '', '37077 Göttingen', 'DPZ01'),
(5, 'BSG PFH Göttingen', 'Private Hochschule Göttingen', 'Personalverwaltung', 'Weender Landstr. 3-7', '', '37073 Göttingen', 'PFH01'),
(5, 'BSG Landkreis Göttingen', 'Landkreis Göttingen', 'Personalamt', 'Reinhäuser Landstr. 4', '', '37083 Göttingen', 'LGÖ01');

-- =============================================================================
-- 2.1 EINZEL-BSG (3-8 pro Verband) - Mitglieder mit eigener BSG
-- =============================================================================

-- Einzel-BSG für Hannover (5 BSG)
INSERT INTO `b_bsg` (`Verband`, `BSG`, `RE_Name`, `RE_Name2`, `RE_Strasse_Nr`, `RE_Strasse2`, `RE_PLZ_Ort`, `VKZ`) VALUES
(1, 'Lena Huber', 'Lena Huber', 'Einzelmitglied', 'Musterstr. 1', '', '30159 Hannover', 'EIN01'),
(1, 'Thomas Richter', 'Thomas Richter', 'Einzelmitglied', 'Beispielweg 2', '', '30159 Hannover', 'EIN02'),
(1, 'Sabrina Koch', 'Sabrina Koch', 'Einzelmitglied', 'Teststr. 3', '', '30159 Hannover', 'EIN03'),
(1, 'Michael Peters', 'Michael Peters', 'Einzelmitglied', 'Demoweg 4', '', '30159 Hannover', 'EIN04'),
(1, 'Julia Bauer', 'Julia Bauer', 'Einzelmitglied', 'Probestr. 5', '', '30159 Hannover', 'EIN05');

-- Einzel-BSG für Braunschweig (4 BSG)
INSERT INTO `b_bsg` (`Verband`, `BSG`, `RE_Name`, `RE_Name2`, `RE_Strasse_Nr`, `RE_Strasse2`, `RE_PLZ_Ort`, `VKZ`) VALUES
(2, 'Andreas Wolf', 'Andreas Wolf', 'Einzelmitglied', 'Einzelstr. 1', '', '38100 Braunschweig', 'EIN06'),
(2, 'Petra Lange', 'Petra Lange', 'Einzelmitglied', 'Soloweg 2', '', '38100 Braunschweig', 'EIN07'),
(2, 'Frank Zimmermann', 'Frank Zimmermann', 'Einzelmitglied', 'Alleinstr. 3', '', '38100 Braunschweig', 'EIN08'),
(2, 'Nicole Braun', 'Nicole Braun', 'Einzelmitglied', 'Privatweg 4', '', '38100 Braunschweig', 'EIN09');

-- Einzel-BSG für Oldenburg (6 BSG)
INSERT INTO `b_bsg` (`Verband`, `BSG`, `RE_Name`, `RE_Name2`, `RE_Strasse_Nr`, `RE_Strasse2`, `RE_PLZ_Ort`, `VKZ`) VALUES
(3, 'Markus Klein', 'Markus Klein', 'Einzelmitglied', 'Selbststr. 1', '', '26121 Oldenburg', 'EIN10'),
(3, 'Claudia Neumann', 'Claudia Neumann', 'Einzelmitglied', 'Eigenweg 2', '', '26121 Oldenburg', 'EIN11'),
(3, 'Stefan Schwarz', 'Stefan Schwarz', 'Einzelmitglied', 'Individualstr. 3', '', '26121 Oldenburg', 'EIN12'),
(3, 'Birgit Hartmann', 'Birgit Hartmann', 'Einzelmitglied', 'Personalweg 4', '', '26121 Oldenburg', 'EIN13'),
(3, 'Thorsten Meyer', 'Thorsten Meyer', 'Einzelmitglied', 'Separatstr. 5', '', '26121 Oldenburg', 'EIN14'),
(3, 'Kerstin Fischer', 'Kerstin Fischer', 'Einzelmitglied', 'Unikatweg 6', '', '26121 Oldenburg', 'EIN15');

-- Einzel-BSG für Osnabrück (3 BSG)
INSERT INTO `b_bsg` (`Verband`, `BSG`, `RE_Name`, `RE_Name2`, `RE_Strasse_Nr`, `RE_Strasse2`, `RE_PLZ_Ort`, `VKZ`) VALUES
(4, 'Oliver Weber', 'Oliver Weber', 'Einzelmitglied', 'Singlestr. 1', '', '49074 Osnabrück', 'EIN16'),
(4, 'Martina Schulz', 'Martina Schulz', 'Einzelmitglied', 'Alleinweg 2', '', '49074 Osnabrück', 'EIN17'),
(4, 'Dirk Hoffmann', 'Dirk Hoffmann', 'Einzelmitglied', 'Solostr. 3', '', '49074 Osnabrück', 'EIN18');

-- Einzel-BSG für Göttingen (7 BSG)
INSERT INTO `b_bsg` (`Verband`, `BSG`, `RE_Name`, `RE_Name2`, `RE_Strasse_Nr`, `RE_Strasse2`, `RE_PLZ_Ort`, `VKZ`) VALUES
(5, 'Christian König', 'Christian König', 'Einzelmitglied', 'Monarchenstr. 1', '', '37073 Göttingen', 'EIN19'),
(5, 'Silke Walter', 'Silke Walter', 'Einzelmitglied', 'Einzelweg 2', '', '37073 Göttingen', 'EIN20'),
(5, 'Jürgen Lang', 'Jürgen Lang', 'Einzelmitglied', 'Privatstr. 3', '', '37073 Göttingen', 'EIN21'),
(5, 'Anja Fuchs', 'Anja Fuchs', 'Einzelmitglied', 'Personalweg 4', '', '37073 Göttingen', 'EIN22'),
(5, 'Ralf Kaiser', 'Ralf Kaiser', 'Einzelmitglied', 'Kaiserstr. 5', '', '37073 Göttingen', 'EIN23'),
(5, 'Heike Scholz', 'Heike Scholz', 'Einzelmitglied', 'Gelehrtenweg 6', '', '37073 Göttingen', 'EIN24'),
(5, 'Matthias Jung', 'Matthias Jung', 'Einzelmitglied', 'Jugendstr. 7', '', '37073 Göttingen', 'EIN25');

-- =============================================================================
-- 3. SPARTEN PRO REGIONALVERBAND
-- =============================================================================

-- Sparten für Hannover
INSERT INTO `b_sparte` (`Verband`, `Sparte`, `Sportart`, `Spartenbeitrag`) VALUES
(1, 'Fußball Hannover', 24, 25.00),
(1, 'Tennis Hannover', NULL, 30.00),
(1, 'Volleyball Hannover', NULL, 20.00),
(1, 'Basketball Hannover', 5, 28.00),
(1, 'Schwimmen Hannover', NULL, 22.00),
(1, 'Laufen Hannover', NULL, 15.00),
(1, 'Badminton Hannover', 4, 18.00),
(1, 'Handball Hannover', 31, 24.00);

-- Sparten für Braunschweig
INSERT INTO `b_sparte` (`Verband`, `Sparte`, `Sportart`, `Spartenbeitrag`) VALUES
(2, 'Fußball Braunschweig', 24, 23.50),
(2, 'Tennis Braunschweig', NULL, 28.00),
(2, 'Tischtennis Braunschweig', NULL, 16.00),
(2, 'Volleyball Braunschweig', NULL, 19.00),
(2, 'Kegeln Braunschweig', NULL, 14.50),
(2, 'Laufen Braunschweig', NULL, 12.00),
(2, 'Radfahren Braunschweig', NULL, 18.50);

-- Sparten für Oldenburg
INSERT INTO `b_sparte` (`Verband`, `Sparte`, `Sportart`, `Spartenbeitrag`) VALUES
(3, 'Fußball Oldenburg', 24, 24.00),
(3, 'Segeln Oldenburg', NULL, 35.00),
(3, 'Tennis Oldenburg', NULL, 27.50),
(3, 'Handball Oldenburg', 31, 22.50),
(3, 'Schwimmen Oldenburg', NULL, 20.00),
(3, 'Laufen Oldenburg', NULL, 13.50);

-- Sparten für Osnabrück
INSERT INTO `b_sparte` (`Verband`, `Sparte`, `Sportart`, `Spartenbeitrag`) VALUES
(4, 'Fußball Osnabrück', 24, 26.00),
(4, 'Tennis Osnabrück', NULL, 29.50),
(4, 'Basketball Osnabrück', 5, 25.50),
(4, 'Volleyball Osnabrück', NULL, 21.00),
(4, 'Badminton Osnabrück', 4, 17.50),
(4, 'Fitness Osnabrück', 22, 19.50),
(4, 'Laufen Osnabrück', NULL, 14.00);

-- Sparten für Göttingen
INSERT INTO `b_sparte` (`Verband`, `Sparte`, `Sportart`, `Spartenbeitrag`) VALUES
(5, 'Fußball Göttingen', 24, 25.50),
(5, 'Tennis Göttingen', NULL, 31.00),
(5, 'Handball Göttingen', 31, 23.50),
(5, 'Volleyball Göttingen', NULL, 20.50),
(5, 'Tischtennis Göttingen', NULL, 15.50),
(5, 'Schwimmen Göttingen', NULL, 21.50);

-- Commit der Basis-Datenstrukturen
COMMIT;

-- =============================================================================
-- 4. Y_USER UND MITGLIEDER GENERIEREN
-- =============================================================================
-- Wir erstellen 5000 Mitglieder mit einer gleichmäßigen Verteilung

-- Variablen für die Schleife
SET @counter = 1;
SET @y_user_id = 1000;
SET @member_id = 100001;

-- Deutsche Vor- und Nachnamen für realistische Daten
-- Arrays würden hier helfen, aber MySQL unterstützt sie nicht direkt
-- Daher nutzen wir eine Auswahl-Logik basierend auf Modulo

DELIMITER $$

DROP PROCEDURE IF EXISTS generate_test_members$$

CREATE PROCEDURE generate_test_members()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_counter INT DEFAULT 1;
    DECLARE v_y_user_id INT DEFAULT 1000;
    DECLARE v_member_id INT DEFAULT 100001;
    DECLARE v_firstname VARCHAR(50);
    DECLARE v_lastname VARCHAR(50);
    DECLARE v_email VARCHAR(100);
    DECLARE v_birth_date DATE;
    DECLARE v_gender INT;
    DECLARE v_regionalverband_id INT;
    DECLARE v_bsg_id INT;
    DECLARE v_mail_notification TINYINT DEFAULT 1;
    DECLARE v_random_factor INT;
    DECLARE v_birth_year INT;
    DECLARE v_birth_month INT;
    DECLARE v_birth_day INT;
    
    -- Cursor für BSG IDs
    DECLARE bsg_cursor CURSOR FOR 
        SELECT id FROM b_bsg ORDER BY RAND();
    
    WHILE v_counter <= 5000 DO
        -- Bestimme Regionalverband basierend auf gleichmäßiger Verteilung
        SET v_regionalverband_id = ((v_counter - 1) MOD 5) + 1;
        
        -- Zufälligen Faktor für Namenswahl generieren
        SET v_random_factor = (v_counter MOD 100) + 1;
        
        -- Geschlecht bestimmen (1=männlich, 2=weiblich, 3=divers)
        SET v_gender = CASE 
            WHEN v_random_factor <= 45 THEN 1  -- 45% männlich
            WHEN v_random_factor <= 90 THEN 2  -- 45% weiblich  
            ELSE 3                              -- 10% divers
        END;
        
        -- Vornamen basierend auf Geschlecht und Zufallsfaktor
        SET v_firstname = CASE v_gender
            WHEN 1 THEN  -- Männliche Vornamen
                CASE (v_counter MOD 30) + 1
                    WHEN 1 THEN 'Alexander'
                    WHEN 2 THEN 'Michael'
                    WHEN 3 THEN 'Thomas'
                    WHEN 4 THEN 'Andreas'
                    WHEN 5 THEN 'Wolfgang'
                    WHEN 6 THEN 'Klaus'
                    WHEN 7 THEN 'Jürgen'
                    WHEN 8 THEN 'Peter'
                    WHEN 9 THEN 'Stefan'
                    WHEN 10 THEN 'Frank'
                    WHEN 11 THEN 'Martin'
                    WHEN 12 THEN 'Christian'
                    WHEN 13 THEN 'Daniel'
                    WHEN 14 THEN 'Markus'
                    WHEN 15 THEN 'Sebastian'
                    WHEN 16 THEN 'Matthias'
                    WHEN 17 THEN 'Thorsten'
                    WHEN 18 THEN 'Oliver'
                    WHEN 19 THEN 'Ralf'
                    WHEN 20 THEN 'Dirk'
                    WHEN 21 THEN 'Holger'
                    WHEN 22 THEN 'Bernd'
                    WHEN 23 THEN 'Uwe'
                    WHEN 24 THEN 'Hans'
                    WHEN 25 THEN 'Manfred'
                    WHEN 26 THEN 'Dieter'
                    WHEN 27 THEN 'Norbert'
                    WHEN 28 THEN 'Jochen'
                    WHEN 29 THEN 'Carsten'
                    ELSE 'Heiko'
                END
            WHEN 2 THEN  -- Weibliche Vornamen
                CASE (v_counter MOD 30) + 1
                    WHEN 1 THEN 'Sabine'
                    WHEN 2 THEN 'Andrea'
                    WHEN 3 THEN 'Petra'
                    WHEN 4 THEN 'Birgit'
                    WHEN 5 THEN 'Susanne'
                    WHEN 6 THEN 'Claudia'
                    WHEN 7 THEN 'Barbara'
                    WHEN 8 THEN 'Nicole'
                    WHEN 9 THEN 'Kerstin'
                    WHEN 10 THEN 'Martina'
                    WHEN 11 THEN 'Monika'
                    WHEN 12 THEN 'Stefanie'
                    WHEN 13 THEN 'Gabriele'
                    WHEN 14 THEN 'Anja'
                    WHEN 15 THEN 'Silke'
                    WHEN 16 THEN 'Katrin'
                    WHEN 17 THEN 'Marion'
                    WHEN 18 THEN 'Doris'
                    WHEN 19 THEN 'Christina'
                    WHEN 20 THEN 'Simone'
                    WHEN 21 THEN 'Heike'
                    WHEN 22 THEN 'Ingrid'
                    WHEN 23 THEN 'Beate'
                    WHEN 24 THEN 'Angelika'
                    WHEN 25 THEN 'Gisela'
                    WHEN 26 THEN 'Ursula'
                    WHEN 27 THEN 'Renate'
                    WHEN 28 THEN 'Karin'
                    WHEN 29 THEN 'Bettina'
                    ELSE 'Julia'
                END
            ELSE  -- Diverse Vornamen
                CASE (v_counter MOD 20) + 1
                    WHEN 1 THEN 'Alex'
                    WHEN 2 THEN 'Robin'
                    WHEN 3 THEN 'Sam'
                    WHEN 4 THEN 'Taylor'
                    WHEN 5 THEN 'Casey'
                    WHEN 6 THEN 'Jordan'
                    WHEN 7 THEN 'Avery'
                    WHEN 8 THEN 'Riley'
                    WHEN 9 THEN 'Sage'
                    WHEN 10 THEN 'River'
                    WHEN 11 THEN 'Phoenix'
                    WHEN 12 THEN 'Quinn'
                    WHEN 13 THEN 'Kai'
                    WHEN 14 THEN 'Blake'
                    WHEN 15 THEN 'Rowan'
                    WHEN 16 THEN 'Emery'
                    WHEN 17 THEN 'Hayden'
                    WHEN 18 THEN 'Cameron'
                    WHEN 19 THEN 'Logan'
                    ELSE 'Morgan'
                END
        END;
        
        -- Nachnamen
        SET v_lastname = CASE (v_counter MOD 50) + 1
            WHEN 1 THEN 'Müller'
            WHEN 2 THEN 'Schmidt'
            WHEN 3 THEN 'Schneider'
            WHEN 4 THEN 'Fischer'
            WHEN 5 THEN 'Weber'
            WHEN 6 THEN 'Meyer'
            WHEN 7 THEN 'Wagner'
            WHEN 8 THEN 'Becker'
            WHEN 9 THEN 'Schulz'
            WHEN 10 THEN 'Hoffmann'
            WHEN 11 THEN 'Schäfer'
            WHEN 12 THEN 'Koch'
            WHEN 13 THEN 'Bauer'
            WHEN 14 THEN 'Richter'
            WHEN 15 THEN 'Klein'
            WHEN 16 THEN 'Wolf'
            WHEN 17 THEN 'Schröder'
            WHEN 18 THEN 'Neumann'
            WHEN 19 THEN 'Schwarz'
            WHEN 20 THEN 'Zimmermann'
            WHEN 21 THEN 'Braun'
            WHEN 22 THEN 'Krüger'
            WHEN 23 THEN 'Hofmann'
            WHEN 24 THEN 'Hartmann'
            WHEN 25 THEN 'Lange'
            WHEN 26 THEN 'Schmitt'
            WHEN 27 THEN 'Werner'
            WHEN 28 THEN 'Schmitz'
            WHEN 29 THEN 'Krause'
            WHEN 30 THEN 'Meier'
            WHEN 31 THEN 'Lehmann'
            WHEN 32 THEN 'Huber'
            WHEN 33 THEN 'Mayer'
            WHEN 34 THEN 'Hermann'
            WHEN 35 THEN 'König'
            WHEN 36 THEN 'Walter'
            WHEN 37 THEN 'Schulze'
            WHEN 38 THEN 'Maier'
            WHEN 39 THEN 'Fuchs'
            WHEN 40 THEN 'Kaiser'
            WHEN 41 THEN 'Lang'
            WHEN 42 THEN 'Weiß'
            WHEN 43 THEN 'Peters'
            WHEN 44 THEN 'Scholz'
            WHEN 45 THEN 'Jung'
            WHEN 46 THEN 'Möller'
            WHEN 47 THEN 'Keller'
            WHEN 48 THEN 'Gross'
            WHEN 49 THEN 'Berger'
            ELSE 'Frank'
        END;
        
        -- Email erstellen
        SET v_email = CONCAT(LOWER(v_firstname), '.', LOWER(v_lastname), v_counter, '@test-mobs.de');
        
        -- Geburtsdatum generieren (Alter zwischen 18 und 65 Jahren)
        SET v_birth_year = 2025 - 18 - (v_counter MOD 48);  -- 48 Jahre Spanne
        SET v_birth_month = (v_counter MOD 12) + 1;
        SET v_birth_day = (v_counter MOD 28) + 1;  -- Max 28 um Februar abzudecken
        SET v_birth_date = STR_TO_DATE(CONCAT(v_birth_year, '-', v_birth_month, '-', v_birth_day), '%Y-%m-%d');
        
        -- BSG basierend auf Regionalverband bestimmen
        SELECT id INTO v_bsg_id FROM b_bsg 
        WHERE Verband = v_regionalverband_id 
        ORDER BY RAND() 
        LIMIT 1;
        
        -- Mail-Benachrichtigung (80% wollen Mails)
        SET v_mail_notification = IF((v_counter MOD 10) <= 7, 1, 2);
        
        -- Y_User einfügen (alle mit Role 8)
        INSERT INTO y_user (id, mail, locked, roles, run_trigger) 
        VALUES (v_y_user_id, v_email, 0, 8, 1);
        
        -- Y_User_Details für erweiterte Felder
        INSERT INTO y_user_details (userID, fieldID, fieldvalue) VALUES
        (v_y_user_id, 4, v_firstname),           -- Vorname
        (v_y_user_id, 5, v_lastname),            -- Nachname  
        (v_y_user_id, 6, v_mail_notification),   -- Mail_OK
        (v_y_user_id, 7, v_gender),              -- Geschlecht
        (v_y_user_id, 8, v_birth_date),          -- Geburtsdatum
        (v_y_user_id, 9, v_bsg_id);              -- BSG
        
        -- Mitglied einfügen
        INSERT INTO b_mitglieder (id, y_id, BSG, Vorname, Nachname, Mail, Geschlecht, Geburtsdatum, Mailbenachrichtigung, aktiv) 
        VALUES (v_member_id, v_y_user_id, v_bsg_id, v_firstname, v_lastname, v_email, v_gender, v_birth_date, v_mail_notification, 1);
        
        -- Individuelle Berechtigung für Stamm-BSG einfügen
        INSERT INTO b_individuelle_berechtigungen (Mitglied, BSG) 
        VALUES (v_member_id, v_bsg_id);
        
        -- Counter erhöhen
        SET v_counter = v_counter + 1;
        SET v_y_user_id = v_y_user_id + 1;
        SET v_member_id = v_member_id + 1;
        
        -- Zwischenspeichern alle 100 Einträge für bessere Performance
        IF v_counter MOD 100 = 0 THEN
            COMMIT;
        END IF;
        
    END WHILE;
    
    -- Finaler Commit
    COMMIT;
    
END$$

DELIMITER ;

-- Procedure ausführen
CALL generate_test_members();

-- Procedure löschen
DROP PROCEDURE generate_test_members;

-- =============================================================================
-- 4.1 EINZEL-BSG MITGLIEDER ERSTELLEN
-- =============================================================================
-- Spezielle Mitglieder für die Einzel-BSG erstellen

-- Y_User für Einzel-BSG Mitglieder
INSERT INTO y_user (mail, locked, roles, run_trigger) VALUES
('lena.huber@test-einzelbsg.de', 0, 8, 1),
('thomas.richter@test-einzelbsg.de', 0, 8, 1),
('sabrina.koch@test-einzelbsg.de', 0, 8, 1),
('michael.peters@test-einzelbsg.de', 0, 8, 1),
('julia.bauer@test-einzelbsg.de', 0, 8, 1),
('andreas.wolf@test-einzelbsg.de', 0, 8, 1),
('petra.lange@test-einzelbsg.de', 0, 8, 1),
('frank.zimmermann@test-einzelbsg.de', 0, 8, 1),
('nicole.braun@test-einzelbsg.de', 0, 8, 1),
('markus.klein@test-einzelbsg.de', 0, 8, 1),
('claudia.neumann@test-einzelbsg.de', 0, 8, 1),
('stefan.schwarz@test-einzelbsg.de', 0, 8, 1),
('birgit.hartmann@test-einzelbsg.de', 0, 8, 1),
('thorsten.meyer@test-einzelbsg.de', 0, 8, 1),
('kerstin.fischer@test-einzelbsg.de', 0, 8, 1),
('oliver.weber@test-einzelbsg.de', 0, 8, 1),
('martina.schulz@test-einzelbsg.de', 0, 8, 1),
('dirk.hoffmann@test-einzelbsg.de', 0, 8, 1),
('christian.koenig@test-einzelbsg.de', 0, 8, 1),
('silke.walter@test-einzelbsg.de', 0, 8, 1),
('juergen.lang@test-einzelbsg.de', 0, 8, 1),
('anja.fuchs@test-einzelbsg.de', 0, 8, 1),
('ralf.kaiser@test-einzelbsg.de', 0, 8, 1),
('heike.scholz@test-einzelbsg.de', 0, 8, 1),
('matthias.jung@test-einzelbsg.de', 0, 8, 1);

-- Y_User_Details für Einzel-BSG Mitglieder
INSERT INTO y_user_details (userID, fieldID, fieldvalue) VALUES
-- Lena Huber (y_user ID wird automatisch vergeben, nehmen wir 6000+)
(6000, 4, 'Lena'), (6000, 5, 'Huber'), (6000, 6, '1'), (6000, 7, '2'), (6000, 8, '1987-06-15'), (6000, 9, (SELECT id FROM b_bsg WHERE BSG = 'Lena Huber')),
(6001, 4, 'Thomas'), (6001, 5, 'Richter'), (6001, 6, '1'), (6001, 7, '1'), (6001, 8, '1983-09-22'), (6001, 9, (SELECT id FROM b_bsg WHERE BSG = 'Thomas Richter')),
(6002, 4, 'Sabrina'), (6002, 5, 'Koch'), (6002, 6, '1'), (6002, 7, '2'), (6002, 8, '1991-03-08'), (6002, 9, (SELECT id FROM b_bsg WHERE BSG = 'Sabrina Koch')),
(6003, 4, 'Michael'), (6003, 5, 'Peters'), (6003, 6, '1'), (6003, 7, '1'), (6003, 8, '1979-12-14'), (6003, 9, (SELECT id FROM b_bsg WHERE BSG = 'Michael Peters')),
(6004, 4, 'Julia'), (6004, 5, 'Bauer'), (6004, 6, '1'), (6004, 7, '2'), (6004, 8, '1988-05-27'), (6004, 9, (SELECT id FROM b_bsg WHERE BSG = 'Julia Bauer')),
(6005, 4, 'Andreas'), (6005, 5, 'Wolf'), (6005, 6, '1'), (6005, 7, '1'), (6005, 8, '1985-08-11'), (6005, 9, (SELECT id FROM b_bsg WHERE BSG = 'Andreas Wolf')),
(6006, 4, 'Petra'), (6006, 5, 'Lange'), (6006, 6, '1'), (6006, 7, '2'), (6006, 8, '1990-01-19'), (6006, 9, (SELECT id FROM b_bsg WHERE BSG = 'Petra Lange')),
(6007, 4, 'Frank'), (6007, 5, 'Zimmermann'), (6007, 6, '1'), (6007, 7, '1'), (6007, 8, '1982-11-03'), (6007, 9, (SELECT id FROM b_bsg WHERE BSG = 'Frank Zimmermann')),
(6008, 4, 'Nicole'), (6008, 5, 'Braun'), (6008, 6, '1'), (6008, 7, '2'), (6008, 8, '1986-07-25'), (6008, 9, (SELECT id FROM b_bsg WHERE BSG = 'Nicole Braun')),
(6009, 4, 'Markus'), (6009, 5, 'Klein'), (6009, 6, '1'), (6009, 7, '1'), (6009, 8, '1984-04-12'), (6009, 9, (SELECT id FROM b_bsg WHERE BSG = 'Markus Klein')),
(6010, 4, 'Claudia'), (6010, 5, 'Neumann'), (6010, 6, '1'), (6010, 7, '2'), (6010, 8, '1992-10-07'), (6010, 9, (SELECT id FROM b_bsg WHERE BSG = 'Claudia Neumann')),
(6011, 4, 'Stefan'), (6011, 5, 'Schwarz'), (6011, 6, '1'), (6011, 7, '1'), (6011, 8, '1989-02-28'), (6011, 9, (SELECT id FROM b_bsg WHERE BSG = 'Stefan Schwarz')),
(6012, 4, 'Birgit'), (6012, 5, 'Hartmann'), (6012, 6, '1'), (6012, 7, '2'), (6012, 8, '1987-12-16'), (6012, 9, (SELECT id FROM b_bsg WHERE BSG = 'Birgit Hartmann')),
(6013, 4, 'Thorsten'), (6013, 5, 'Meyer'), (6013, 6, '1'), (6013, 7, '1'), (6013, 8, '1981-09-05'), (6013, 9, (SELECT id FROM b_bsg WHERE BSG = 'Thorsten Meyer')),
(6014, 4, 'Kerstin'), (6014, 5, 'Fischer'), (6014, 6, '1'), (6014, 7, '2'), (6014, 8, '1993-06-23'), (6014, 9, (SELECT id FROM b_bsg WHERE BSG = 'Kerstin Fischer')),
(6015, 4, 'Oliver'), (6015, 5, 'Weber'), (6015, 6, '1'), (6015, 7, '1'), (6015, 8, '1980-03-14'), (6015, 9, (SELECT id FROM b_bsg WHERE BSG = 'Oliver Weber')),
(6016, 4, 'Martina'), (6016, 5, 'Schulz'), (6016, 6, '1'), (6016, 7, '2'), (6016, 8, '1988-11-09'), (6016, 9, (SELECT id FROM b_bsg WHERE BSG = 'Martina Schulz')),
(6017, 4, 'Dirk'), (6017, 5, 'Hoffmann'), (6017, 6, '1'), (6017, 7, '1'), (6017, 8, '1985-07-18'), (6017, 9, (SELECT id FROM b_bsg WHERE BSG = 'Dirk Hoffmann')),
(6018, 4, 'Christian'), (6018, 5, 'König'), (6018, 6, '1'), (6018, 7, '1'), (6018, 8, '1983-01-26'), (6018, 9, (SELECT id FROM b_bsg WHERE BSG = 'Christian König')),
(6019, 4, 'Silke'), (6019, 5, 'Walter'), (6019, 6, '1'), (6019, 7, '2'), (6019, 8, '1990-08-13'), (6019, 9, (SELECT id FROM b_bsg WHERE BSG = 'Silke Walter')),
(6020, 4, 'Jürgen'), (6020, 5, 'Lang'), (6020, 6, '1'), (6020, 7, '1'), (6020, 8, '1986-04-30'), (6020, 9, (SELECT id FROM b_bsg WHERE BSG = 'Jürgen Lang')),
(6021, 4, 'Anja'), (6021, 5, 'Fuchs'), (6021, 6, '1'), (6021, 7, '2'), (6021, 8, '1991-12-05'), (6021, 9, (SELECT id FROM b_bsg WHERE BSG = 'Anja Fuchs')),
(6022, 4, 'Ralf'), (6022, 5, 'Kaiser'), (6022, 6, '1'), (6022, 7, '1'), (6022, 8, '1984-10-21'), (6022, 9, (SELECT id FROM b_bsg WHERE BSG = 'Ralf Kaiser')),
(6023, 4, 'Heike'), (6023, 5, 'Scholz'), (6023, 6, '1'), (6023, 7, '2'), (6023, 8, '1989-05-17'), (6023, 9, (SELECT id FROM b_bsg WHERE BSG = 'Heike Scholz')),
(6024, 4, 'Matthias'), (6024, 5, 'Jung'), (6024, 6, '1'), (6024, 7, '1'), (6024, 8, '1987-02-08'), (6024, 9, (SELECT id FROM b_bsg WHERE BSG = 'Matthias Jung'));

-- Mitglieder für Einzel-BSG einfügen
INSERT INTO b_mitglieder (y_id, BSG, Vorname, Nachname, Mail, Geschlecht, Geburtsdatum, Mailbenachrichtigung, aktiv) VALUES
(6000, (SELECT id FROM b_bsg WHERE BSG = 'Lena Huber'), 'Lena', 'Huber', 'lena.huber@test-einzelbsg.de', 2, '1987-06-15', 1, 1),
(6001, (SELECT id FROM b_bsg WHERE BSG = 'Thomas Richter'), 'Thomas', 'Richter', 'thomas.richter@test-einzelbsg.de', 1, '1983-09-22', 1, 1),
(6002, (SELECT id FROM b_bsg WHERE BSG = 'Sabrina Koch'), 'Sabrina', 'Koch', 'sabrina.koch@test-einzelbsg.de', 2, '1991-03-08', 1, 1),
(6003, (SELECT id FROM b_bsg WHERE BSG = 'Michael Peters'), 'Michael', 'Peters', 'michael.peters@test-einzelbsg.de', 1, '1979-12-14', 1, 1),
(6004, (SELECT id FROM b_bsg WHERE BSG = 'Julia Bauer'), 'Julia', 'Bauer', 'julia.bauer@test-einzelbsg.de', 2, '1988-05-27', 1, 1),
(6005, (SELECT id FROM b_bsg WHERE BSG = 'Andreas Wolf'), 'Andreas', 'Wolf', 'andreas.wolf@test-einzelbsg.de', 1, '1985-08-11', 1, 1),
(6006, (SELECT id FROM b_bsg WHERE BSG = 'Petra Lange'), 'Petra', 'Lange', 'petra.lange@test-einzelbsg.de', 2, '1990-01-19', 1, 1),
(6007, (SELECT id FROM b_bsg WHERE BSG = 'Frank Zimmermann'), 'Frank', 'Zimmermann', 'frank.zimmermann@test-einzelbsg.de', 1, '1982-11-03', 1, 1),
(6008, (SELECT id FROM b_bsg WHERE BSG = 'Nicole Braun'), 'Nicole', 'Braun', 'nicole.braun@test-einzelbsg.de', 2, '1986-07-25', 1, 1),
(6009, (SELECT id FROM b_bsg WHERE BSG = 'Markus Klein'), 'Markus', 'Klein', 'markus.klein@test-einzelbsg.de', 1, '1984-04-12', 1, 1),
(6010, (SELECT id FROM b_bsg WHERE BSG = 'Claudia Neumann'), 'Claudia', 'Neumann', 'claudia.neumann@test-einzelbsg.de', 2, '1992-10-07', 1, 1),
(6011, (SELECT id FROM b_bsg WHERE BSG = 'Stefan Schwarz'), 'Stefan', 'Schwarz', 'stefan.schwarz@test-einzelbsg.de', 1, '1989-02-28', 1, 1),
(6012, (SELECT id FROM b_bsg WHERE BSG = 'Birgit Hartmann'), 'Birgit', 'Hartmann', 'birgit.hartmann@test-einzelbsg.de', 2, '1987-12-16', 1, 1),
(6013, (SELECT id FROM b_bsg WHERE BSG = 'Thorsten Meyer'), 'Thorsten', 'Meyer', 'thorsten.meyer@test-einzelbsg.de', 1, '1981-09-05', 1, 1),
(6014, (SELECT id FROM b_bsg WHERE BSG = 'Kerstin Fischer'), 'Kerstin', 'Fischer', 'kerstin.fischer@test-einzelbsg.de', 2, '1993-06-23', 1, 1),
(6015, (SELECT id FROM b_bsg WHERE BSG = 'Oliver Weber'), 'Oliver', 'Weber', 'oliver.weber@test-einzelbsg.de', 1, '1980-03-14', 1, 1),
(6016, (SELECT id FROM b_bsg WHERE BSG = 'Martina Schulz'), 'Martina', 'Schulz', 'martina.schulz@test-einzelbsg.de', 2, '1988-11-09', 1, 1),
(6017, (SELECT id FROM b_bsg WHERE BSG = 'Dirk Hoffmann'), 'Dirk', 'Hoffmann', 'dirk.hoffmann@test-einzelbsg.de', 1, '1985-07-18', 1, 1),
(6018, (SELECT id FROM b_bsg WHERE BSG = 'Christian König'), 'Christian', 'König', 'christian.koenig@test-einzelbsg.de', 1, '1983-01-26', 1, 1),
(6019, (SELECT id FROM b_bsg WHERE BSG = 'Silke Walter'), 'Silke', 'Walter', 'silke.walter@test-einzelbsg.de', 2, '1990-08-13', 1, 1),
(6020, (SELECT id FROM b_bsg WHERE BSG = 'Jürgen Lang'), 'Jürgen', 'Lang', 'juergen.lang@test-einzelbsg.de', 1, '1986-04-30', 1, 1),
(6021, (SELECT id FROM b_bsg WHERE BSG = 'Anja Fuchs'), 'Anja', 'Fuchs', 'anja.fuchs@test-einzelbsg.de', 2, '1991-12-05', 1, 1),
(6022, (SELECT id FROM b_bsg WHERE BSG = 'Ralf Kaiser'), 'Ralf', 'Kaiser', 'ralf.kaiser@test-einzelbsg.de', 1, '1984-10-21', 1, 1),
(6023, (SELECT id FROM b_bsg WHERE BSG = 'Heike Scholz'), 'Heike', 'Scholz', 'heike.scholz@test-einzelbsg.de', 2, '1989-05-17', 1, 1),
(6024, (SELECT id FROM b_bsg WHERE BSG = 'Matthias Jung'), 'Matthias', 'Jung', 'matthias.jung@test-einzelbsg.de', 1, '1987-02-08', 1, 1);

-- Individuelle Berechtigungen für Einzel-BSG Mitglieder
INSERT INTO b_individuelle_berechtigungen (Mitglied, BSG) 
SELECT m.id, m.BSG 
FROM b_mitglieder m 
WHERE m.y_id BETWEEN 6000 AND 6024;

-- =============================================================================
-- 5. SPARTEN-ANMELDUNGEN GENERIEREN
-- =============================================================================
-- Ca. 98% der Mitglieder werden in 1-3 Sparten angemeldet

DELIMITER $$

DROP PROCEDURE IF EXISTS generate_sparten_anmeldungen$$

CREATE PROCEDURE generate_sparten_anmeldungen()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_member_id BIGINT;
    DECLARE v_member_bsg BIGINT;
    DECLARE v_member_verband BIGINT;
    DECLARE v_sparte_id BIGINT;
    DECLARE v_num_sparten INT;
    DECLARE v_sparte_counter INT;
    DECLARE v_random_factor INT;
    
    -- Cursor für alle Mitglieder
    DECLARE member_cursor CURSOR FOR 
        SELECT m.id, m.BSG, b.Verband 
        FROM b_mitglieder m 
        JOIN b_bsg b ON m.BSG = b.id 
        WHERE m.id >= 100001;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN member_cursor;
    
    member_loop: LOOP
        FETCH member_cursor INTO v_member_id, v_member_bsg, v_member_verband;
        
        IF done THEN
            LEAVE member_loop;
        END IF;
        
        -- Zufällig bestimmen ob Mitglied in Sparten angemeldet wird (98% Chance)
        SET v_random_factor = (v_member_id MOD 100) + 1;
        
        IF v_random_factor <= 98 THEN
            -- Anzahl Sparten bestimmen (1-3)
            SET v_num_sparten = CASE 
                WHEN v_random_factor <= 50 THEN 1
                WHEN v_random_factor <= 85 THEN 2
                ELSE 3
            END;
            
            SET v_sparte_counter = 1;
            
            sparten_loop: WHILE v_sparte_counter <= v_num_sparten DO
                -- Zufällige Sparte des gleichen Verbandes wählen
                SELECT id INTO v_sparte_id 
                FROM b_sparte 
                WHERE Verband = v_member_verband 
                AND id NOT IN (
                    SELECT Sparte 
                    FROM b_mitglieder_in_sparten 
                    WHERE Mitglied = v_member_id
                )
                ORDER BY RAND() 
                LIMIT 1;
                
                -- Prüfen ob Sparte gefunden wurde
                IF v_sparte_id IS NOT NULL THEN
                    -- Sparten-Anmeldung einfügen
                    INSERT IGNORE INTO b_mitglieder_in_sparten (Sparte, Mitglied, BSG) 
                    VALUES (v_sparte_id, v_member_id, v_member_bsg);
                    
                    -- Individuelle Berechtigung für BSG über Sparte einfügen (falls noch nicht vorhanden)
                    INSERT IGNORE INTO b_individuelle_berechtigungen (Mitglied, BSG) 
                    VALUES (v_member_id, v_member_bsg);
                END IF;
                
                SET v_sparte_counter = v_sparte_counter + 1;
                SET v_sparte_id = NULL;
            END WHILE sparten_loop;
        END IF;
        
    END LOOP member_loop;
    
    CLOSE member_cursor;
    COMMIT;
    
END$$

DELIMITER ;

-- Procedure ausführen
CALL generate_sparten_anmeldungen();

-- Procedure löschen
DROP PROCEDURE generate_sparten_anmeldungen;

-- =============================================================================
-- 6. ZUSÄTZLICHE INDIVIDUELLE BERECHTIGUNGEN
-- =============================================================================
-- Einige Mitglieder erhalten Berechtigung für zusätzliche BSG
-- (z.B. für Gastspieler oder regionsübergreifende Aktivitäten)

INSERT INTO b_individuelle_berechtigungen (Mitglied, BSG)
SELECT 
    m.id,
    b.id
FROM b_mitglieder m
CROSS JOIN b_bsg b
JOIN b_bsg m_bsg ON m.BSG = m_bsg.id
WHERE m.id MOD 25 = 0  -- Jedes 25. Mitglied
AND b.Verband = m_bsg.Verband  -- Nur BSG im gleichen Verband
AND b.id != m_bsg.id  -- Nicht die eigene BSG
AND RAND() < 0.3  -- 30% Chance
ON DUPLICATE KEY UPDATE BSG = VALUES(BSG);  -- Ignore Duplikate

-- =============================================================================
-- 7. EDGE CASES UND BESONDERE TESTFÄLLE
-- =============================================================================

-- Mitglied ohne y_id (manuell angelegt)
INSERT INTO b_mitglieder (BSG, Vorname, Nachname, Mail, Geschlecht, Geburtsdatum, Mailbenachrichtigung, aktiv, Bemerkung) 
VALUES (1, 'Manual', 'Test', 'manual.test@test.de', 1, '1980-01-01', 1, 1, 'Manuell angelegtes Testmitglied ohne y_id');

-- Berechtigung für manuelles Mitglied
INSERT INTO b_individuelle_berechtigungen (Mitglied, BSG) 
VALUES (LAST_INSERT_ID(), 1);

-- Inaktive Mitglieder (2% der Mitglieder)
UPDATE b_mitglieder 
SET aktiv = 2 
WHERE id MOD 50 = 0 
AND id >= 100001 
LIMIT 100;

-- Mitglieder ohne Mail-Benachrichtigung
UPDATE b_mitglieder 
SET Mailbenachrichtigung = 2 
WHERE id MOD 17 = 0 
AND id >= 100001;

-- Sehr alte Mitglieder (über 70 Jahre)
UPDATE b_mitglieder 
SET Geburtsdatum = '1945-06-15' 
WHERE id MOD 83 = 0 
AND id >= 100001 
LIMIT 10;

-- Sehr junge Mitglieder (18 Jahre)
UPDATE b_mitglieder 
SET Geburtsdatum = '2007-01-01' 
WHERE id MOD 97 = 0 
AND id >= 100001 
LIMIT 15;

-- Mitglieder mit besonders langen Namen (Edge Case für UI)
UPDATE b_mitglieder 
SET Vorname = 'Alexander-Maximilian', Nachname = 'Freiherr von Müller-Schmidt' 
WHERE id = 100001;

UPDATE b_mitglieder 
SET Vorname = 'Dr. med. Christine-Elisabeth', Nachname = 'Doppelname-Testerin' 
WHERE id = 100002;

-- Mitglied mit Sonderzeichen im Namen
UPDATE b_mitglieder 
SET Vorname = 'José-María', Nachname = 'Fernández-González' 
WHERE id = 100003;

-- Mitglieder mit verschiedenen Mail-Domains
UPDATE b_mitglieder 
SET Mail = CONCAT(LEFT(LOWER(Vorname), 8), '.', LEFT(LOWER(Nachname), 8), '@gmail.com') 
WHERE id MOD 7 = 0 
AND id >= 100001 
AND id <= 100050;

UPDATE b_mitglieder 
SET Mail = CONCAT(LEFT(LOWER(Vorname), 8), '.', LEFT(LOWER(Nachname), 8), '@outlook.de') 
WHERE id MOD 11 = 0 
AND id >= 100051 
AND id <= 100100;

-- =============================================================================
-- 8. STATISTIKEN UND VALIDIERUNG
-- =============================================================================

-- Aktiviere Fremdschlüssel-Checks wieder
SET FOREIGN_KEY_CHECKS = 1;
SET AUTOCOMMIT = 1;

-- Validierungs-Queries als Kommentare für den Admin
/*
-- VALIDIERUNGS-QUERIES:

-- Anzahl Mitglieder pro Regionalverband prüfen
SELECT 
    rv.Verband, 
    COUNT(m.id) as Anzahl_Mitglieder,
    COUNT(DISTINCT m.BSG) as Anzahl_BSG
FROM b_regionalverband rv
LEFT JOIN b_bsg b ON rv.id = b.Verband
LEFT JOIN b_mitglieder m ON b.id = m.BSG
GROUP BY rv.id, rv.Verband
ORDER BY rv.id;

-- Sparten-Anmeldungen prüfen
SELECT 
    s.Sparte,
    COUNT(mis.id) as Anzahl_Anmeldungen
FROM b_sparte s
LEFT JOIN b_mitglieder_in_sparten mis ON s.id = mis.Sparte
GROUP BY s.id, s.Sparte
ORDER BY COUNT(mis.id) DESC;

-- Individuelle Berechtigungen prüfen
SELECT 
    COUNT(*) as Anzahl_Berechtigungen,
    COUNT(DISTINCT Mitglied) as Anzahl_Mitglieder_mit_Berechtigungen,
    COUNT(DISTINCT BSG) as Anzahl_BSG_mit_Berechtigungen
FROM b_individuelle_berechtigungen;

-- Y_User Rollen prüfen (alle sollten 8 haben)
SELECT roles, COUNT(*) 
FROM y_user 
WHERE id >= 1000 
GROUP BY roles;

-- Altersverteilung prüfen
SELECT 
    CASE 
        WHEN YEAR(CURDATE()) - YEAR(Geburtsdatum) < 25 THEN 'Unter 25'
        WHEN YEAR(CURDATE()) - YEAR(Geburtsdatum) < 35 THEN '25-34'
        WHEN YEAR(CURDATE()) - YEAR(Geburtsdatum) < 45 THEN '35-44'
        WHEN YEAR(CURDATE()) - YEAR(Geburtsdatum) < 55 THEN '45-54'
        WHEN YEAR(CURDATE()) - YEAR(Geburtsdatum) < 65 THEN '55-64'
        ELSE '65+'
    END as Altersgruppe,
    COUNT(*) as Anzahl
FROM b_mitglieder 
WHERE id >= 100001 
GROUP BY 
    CASE 
        WHEN YEAR(CURDATE()) - YEAR(Geburtsdatum) < 25 THEN 'Unter 25'
        WHEN YEAR(CURDATE()) - YEAR(Geburtsdatum) < 35 THEN '25-34'
        WHEN YEAR(CURDATE()) - YEAR(Geburtsdatum) < 45 THEN '35-44'
        WHEN YEAR(CURDATE()) - YEAR(Geburtsdatum) < 55 THEN '45-54'
        WHEN YEAR(CURDATE()) - YEAR(Geburtsdatum) < 65 THEN '55-64'
        ELSE '65+'
    END
ORDER BY Altersgruppe;

-- Geschlechterverteilung prüfen
SELECT 
    CASE Geschlecht
        WHEN 1 THEN 'Männlich'
        WHEN 2 THEN 'Weiblich'
        WHEN 3 THEN 'Divers'
        ELSE 'Unbekannt'
    END as Geschlecht,
    COUNT(*) as Anzahl,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM b_mitglieder WHERE id >= 100001), 2) as Prozent
FROM b_mitglieder 
WHERE id >= 100001 
GROUP BY Geschlecht
ORDER BY Geschlecht;

*/

-- =============================================================================
-- ENDE DER TESTDATEN-GENERIERUNG
-- =============================================================================
-- Zusammenfassung:
-- ✓ 5 Regionalverbände (Niedersächsische Städte)
-- ✓ 80 BSG total (55 reguläre + 25 Einzel-BSG)  
-- ✓ 5025 Mitglieder mit realistischen Daten (5000 reguläre + 25 Einzel-BSG)
-- ✓ Alle y_user haben Rollenstufe 8
-- ✓ Sparten-Anmeldungen für ca. 98% der Mitglieder
-- ✓ Korrekte individuelle Berechtigungen
-- ✓ Edge Cases für Testing
-- ✓ 25 Einzel-BSG (3-8 pro Verband) mit jeweils einem Mitglied
-- ✓ Truncate-Statements für wiederholbare Ausführung
-- ✓ Keine Einträge in rechte_regionalverband und rechte_bsg
-- =============================================================================

-- =============================================================================
-- REGENERATION PROMPT
-- =============================================================================
/*
Erstelle ein SQL-Script für Testdaten mit folgenden Anforderungen:

1. **Grundstruktur:**
   - 5000 Mitglieder in 5 Regionalverbänden (niedersächsische Städte: Hannover, Braunschweig, Oldenburg, Osnabrück, Göttingen)
   - Jeweils 9-15 reguläre BSG pro Regionalverband
   - 3-8 Einzel-BSG pro Regionalverband (BSG benannt nach dem einzigen Mitglied)
   - Alle y_user haben Rollenstufe 8

2. **Datenqualität:**
   - Realistische deutsche Namen (Vor- und Nachnamen)
   - Sinnvolle Geburtsdaten (Alter 18-65 Jahre)
   - Geschlechterverteilung: 45% männlich, 45% weiblich, 10% divers
   - Realistische Email-Adressen mit verschiedenen Domains

3. **Sparten und Anmeldungen:**
   - 98% der Mitglieder sind in 1-3 Sparten angemeldet
   - Sparten pro Regionalverband mit verschiedenen Sportarten
   - Spartenbeiträge zwischen 12-35 Euro

4. **Berechtigungen:**
   - Individuelle Berechtigungen für jede BSG, in der das Mitglied Stammmitglied ist
   - Zusätzliche Berechtigungen für BSG, in denen das Mitglied über Sparten angemeldet ist
   - KEINE Einträge in rechte_regionalverband und rechte_bsg

5. **Edge Cases:**
   - Mitglieder ohne y_id (manuell angelegt)
   - Inaktive Mitglieder (2%)
   - Sehr alte/junge Mitglieder
   - Lange Namen mit Sonderzeichen
   - Verschiedene Mail-Domains und -benachrichtigungseinstellungen

6. **Performance und Wartbarkeit:**
   - Truncate-Statements am Anfang für wiederholbare Ausführung
   - Stored Procedures für große Datenmengen
   - Batch-Commits für bessere Performance
   - Deaktivierung von Foreign Key Checks während Import

7. **Firmen und BSG:**
   - Realistische Firmennamen aus den jeweiligen Städten
   - VKZ-Codes für jede BSG
   - Vollständige Adressdaten mit Postleitzahlen

Schreibe die Queries in 91_testdaten.sql und stelle sicher, dass das Script mehrfach ausführbar ist.
*/
