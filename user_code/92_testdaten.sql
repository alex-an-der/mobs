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
TRUNCATE TABLE `b_bsg_rechte`;
TRUNCATE TABLE `b_regionalverband_rechte`;
TRUNCATE TABLE `sys_log`;
TRUNCATE TABLE `sys_rollback`;



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
                CASE (v_counter MOD 750) + 1
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
                    WHEN 30 THEN 'Heiko'
                    WHEN 31 THEN 'Benjamin'
                    WHEN 32 THEN 'Tobias'
                    WHEN 33 THEN 'Jan'
                    WHEN 34 THEN 'Lars'
                    WHEN 35 THEN 'Florian'
                    WHEN 36 THEN 'David'
                    WHEN 37 THEN 'Philipp'
                    WHEN 38 THEN 'Marc'
                    WHEN 39 THEN 'Marco'
                    WHEN 40 THEN 'Sascha'
                    WHEN 41 THEN 'Dennis'
                    WHEN 42 THEN 'Simon'
                    WHEN 43 THEN 'Tim'
                    WHEN 44 THEN 'Patrick'
                    WHEN 45 THEN 'Sven'
                    WHEN 46 THEN 'Kai'
                    WHEN 47 THEN 'Robert'
                    WHEN 48 THEN 'Rene'
                    WHEN 49 THEN 'Ingo'
                    WHEN 50 THEN 'Axel'
                    WHEN 51 THEN 'Jonas'
                    WHEN 52 THEN 'Felix'
                    WHEN 53 THEN 'Max'
                    WHEN 54 THEN 'Lukas'
                    WHEN 55 THEN 'Nils'
                    WHEN 56 THEN 'Pascal'
                    WHEN 57 THEN 'Marius'
                    WHEN 58 THEN 'Fabian'
                    WHEN 59 THEN 'Julian'
                    WHEN 60 THEN 'Adrian'
                    WHEN 61 THEN 'Dominik'
                    WHEN 62 THEN 'Rico'
                    WHEN 63 THEN 'Kevin'
                    WHEN 64 THEN 'Marvin'
                    WHEN 65 THEN 'Nico'
                    WHEN 66 THEN 'Luca'
                    WHEN 67 THEN 'Noah'
                    WHEN 68 THEN 'Leon'
                    WHEN 69 THEN 'Paul'
                    WHEN 70 THEN 'Finn'
                    WHEN 71 THEN 'Elias'
                    WHEN 72 THEN 'Ben'
                    WHEN 73 THEN 'Luis'
                    WHEN 74 THEN 'Emil'
                    WHEN 75 THEN 'Henry'
                    WHEN 76 THEN 'Aaron'
                    WHEN 77 THEN 'Adam'
                    WHEN 78 THEN 'Anton'
                    WHEN 79 THEN 'Arthur'
                    WHEN 80 THEN 'Benedikt'
                    WHEN 81 THEN 'Bruno'
                    WHEN 82 THEN 'Carl'
                    WHEN 83 THEN 'Clemens'
                    WHEN 84 THEN 'Constantin'
                    WHEN 85 THEN 'Damian'
                    WHEN 86 THEN 'Diego'
                    WHEN 87 THEN 'Edgar'
                    WHEN 88 THEN 'Eduard'
                    WHEN 89 THEN 'Eliah'
                    WHEN 90 THEN 'Emanuel'
                    WHEN 91 THEN 'Eric'
                    WHEN 92 THEN 'Erik'
                    WHEN 93 THEN 'Ethan'
                    WHEN 94 THEN 'Ferdinand'
                    WHEN 95 THEN 'Frederik'
                    WHEN 96 THEN 'Gabriel'
                    WHEN 97 THEN 'Georg'
                    WHEN 98 THEN 'Hannes'
                    WHEN 99 THEN 'Henri'
                    WHEN 100 THEN 'Hugo'
                    WHEN 101 THEN 'Ian'
                    WHEN 102 THEN 'Ignaz'
                    WHEN 103 THEN 'Jakob'
                    WHEN 104 THEN 'Jannik'
                    WHEN 105 THEN 'Jasper'
                    WHEN 106 THEN 'Jens'
                    WHEN 107 THEN 'Johann'
                    WHEN 108 THEN 'Johannes'
                    WHEN 109 THEN 'Jonathan'
                    WHEN 110 THEN 'Josef'
                    WHEN 111 THEN 'Joshua'
                    WHEN 112 THEN 'Julius'
                    WHEN 113 THEN 'Justin'
                    WHEN 114 THEN 'Kilian'
                    WHEN 115 THEN 'Konstantin'
                    WHEN 116 THEN 'Leonard'
                    WHEN 117 THEN 'Leopold'
                    WHEN 118 THEN 'Liam'
                    WHEN 119 THEN 'Linus'
                    WHEN 120 THEN 'Lorenzo'
                    WHEN 121 THEN 'Louis'
                    WHEN 122 THEN 'Ludwig'
                    WHEN 123 THEN 'Magnus'
                    WHEN 124 THEN 'Manuel'
                    WHEN 125 THEN 'Marlon'
                    WHEN 126 THEN 'Mathias'
                    WHEN 127 THEN 'Maximilian'
                    WHEN 128 THEN 'Moritz'
                    WHEN 129 THEN 'Nathan'
                    WHEN 130 THEN 'Niklas'
                    WHEN 131 THEN 'Oscar'
                    WHEN 132 THEN 'Otto'
                    WHEN 133 THEN 'Rafael'
                    WHEN 134 THEN 'Richard'
                    WHEN 135 THEN 'Robin'
                    WHEN 136 THEN 'Samuel'
                    WHEN 137 THEN 'Sebastian'
                    WHEN 138 THEN 'Theo'
                    WHEN 139 THEN 'Theodore'
                    WHEN 140 THEN 'Till'
                    WHEN 141 THEN 'Valentin'
                    WHEN 142 THEN 'Victor'
                    WHEN 143 THEN 'Vincent'
                    WHEN 144 THEN 'Wilhelm'
                    WHEN 145 THEN 'William'
                    WHEN 146 THEN 'Yannick'
                    WHEN 147 THEN 'Yorick'
                    WHEN 148 THEN 'Zacharias'
                    WHEN 149 THEN 'Zeno'
                    WHEN 150 THEN 'Friedrich'
                    WHEN 151 THEN 'Gunther'
                    WHEN 152 THEN 'Heinrich'
                    WHEN 153 THEN 'Helmut'
                    WHEN 154 THEN 'Hermann'
                    WHEN 155 THEN 'Joachim'
                    WHEN 156 THEN 'Karl'
                    WHEN 157 THEN 'Konrad'
                    WHEN 158 THEN 'Lothar'
                    WHEN 159 THEN 'Rainer'
                    WHEN 160 THEN 'Reinhard'
                    WHEN 161 THEN 'Roland'
                    WHEN 162 THEN 'Rudolf'
                    WHEN 163 THEN 'Siegfried'
                    WHEN 164 THEN 'Ulrich'
                    WHEN 165 THEN 'Volker'
                    WHEN 166 THEN 'Werner'
                    WHEN 167 THEN 'Willi'
                    WHEN 168 THEN 'Alfred'
                    WHEN 169 THEN 'Armin'
                    WHEN 170 THEN 'Bernhard'
                    WHEN 171 THEN 'Detlef'
                    WHEN 172 THEN 'Eberhard'
                    WHEN 173 THEN 'Egon'
                    WHEN 174 THEN 'Erwin'
                    WHEN 175 THEN 'Gerhard'
                    WHEN 176 THEN 'Gerd'
                    WHEN 177 THEN 'Gottfried'
                    WHEN 178 THEN 'Günter'
                    WHEN 179 THEN 'Hartmut'
                    WHEN 180 THEN 'Heinz'
                    WHEN 181 THEN 'Herbert'
                    WHEN 182 THEN 'Horst'
                    WHEN 183 THEN 'Jörg'
                    WHEN 184 THEN 'Kurt'
                    WHEN 185 THEN 'Manfred'
                    WHEN 186 THEN 'Norbert'
                    WHEN 187 THEN 'Otmar'
                    WHEN 188 THEN 'Reiner'
                    WHEN 189 THEN 'Rolf'
                    WHEN 190 THEN 'Rüdiger'
                    WHEN 191 THEN 'Günther'
                    WHEN 192 THEN 'Bernd'
                    WHEN 193 THEN 'Burkhard'
                    WHEN 194 THEN 'Dietmar'
                    WHEN 195 THEN 'Dieter'
                    WHEN 196 THEN 'Erich'
                    WHEN 197 THEN 'Ernst'
                    WHEN 198 THEN 'Franz'
                    WHEN 199 THEN 'Friedhelm'
                    WHEN 200 THEN 'Georg'
                    WHEN 201 THEN 'Gerold'
                    WHEN 202 THEN 'Gert'
                    WHEN 203 THEN 'Gisbert'
                    WHEN 204 THEN 'Gustav'
                    WHEN 205 THEN 'Hans-Jürgen'
                    WHEN 206 THEN 'Hans-Peter'
                    WHEN 207 THEN 'Harald'
                    WHEN 208 THEN 'Hartwig'
                    WHEN 209 THEN 'Hein'
                    WHEN 210 THEN 'Heiner'
                    WHEN 211 THEN 'Henning'
                    WHEN 212 THEN 'Hubertus'
                    WHEN 213 THEN 'Hugo'
                    WHEN 214 THEN 'Ingo'
                    WHEN 215 THEN 'Jens'
                    WHEN 216 THEN 'Johannes'
                    WHEN 217 THEN 'Jörn'
                    WHEN 218 THEN 'Josef'
                    WHEN 219 THEN 'Jürgen'
                    WHEN 220 THEN 'Karl-Heinz'
                    WHEN 221 THEN 'Klaus-Dieter'
                    WHEN 222 THEN 'Ludger'
                    WHEN 223 THEN 'Lutz'
                    WHEN 224 THEN 'Manfred'
                    WHEN 225 THEN 'Matthias'
                    WHEN 226 THEN 'Meinhard'
                    WHEN 227 THEN 'Norbert'
                    WHEN 228 THEN 'Olaf'
                    WHEN 229 THEN 'Oskar'
                    WHEN 230 THEN 'Otto'
                    WHEN 231 THEN 'Paul'
                    WHEN 232 THEN 'Rainer'
                    WHEN 233 THEN 'Reinhold'
                    WHEN 234 THEN 'Richard'
                    WHEN 235 THEN 'Siegbert'
                    WHEN 236 THEN 'Siegmund'
                    WHEN 237 THEN 'Stefan'
                    WHEN 238 THEN 'Theodor'
                    WHEN 239 THEN 'Udo'
                    WHEN 240 THEN 'Ulrich'
                    WHEN 241 THEN 'Volker'
                    WHEN 242 THEN 'Waldemar'
                    WHEN 243 THEN 'Walter'
                    WHEN 244 THEN 'Wilhelm'
                    WHEN 245 THEN 'Willi'
                    WHEN 246 THEN 'Winfried'
                    WHEN 247 THEN 'Wolf'
                    WHEN 248 THEN 'Wolfgang'
                    WHEN 249 THEN 'Wolfram'
                    WHEN 250 THEN 'Achim'
                    WHEN 251 THEN 'Adolf'
                    WHEN 252 THEN 'Albert'
                    WHEN 253 THEN 'Alois'
                    WHEN 254 THEN 'Anatol'
                    WHEN 255 THEN 'Andre'
                    WHEN 256 THEN 'Angelo'
                    WHEN 257 THEN 'Arno'
                    WHEN 258 THEN 'Bernd'
                    WHEN 259 THEN 'Berthold'
                    WHEN 260 THEN 'Bertram'
                    WHEN 261 THEN 'Björn'
                    WHEN 262 THEN 'Bodo'
                    WHEN 263 THEN 'Boris'
                    WHEN 264 THEN 'Branko'
                    WHEN 265 THEN 'Christoph'
                    WHEN 266 THEN 'Claus'
                    WHEN 267 THEN 'Cornelius'
                    WHEN 268 THEN 'Damien'
                    WHEN 269 THEN 'Darius'
                    WHEN 270 THEN 'Derek'
                    WHEN 271 THEN 'Detlef'
                    WHEN 272 THEN 'Dimitri'
                    WHEN 273 THEN 'Dirk'
                    WHEN 274 THEN 'Edmund'
                    WHEN 275 THEN 'Edwin'
                    WHEN 276 THEN 'Elmar'
                    WHEN 277 THEN 'Enrico'
                    WHEN 278 THEN 'Eugen'
                    WHEN 279 THEN 'Fabian'
                    WHEN 280 THEN 'Falko'
                    WHEN 281 THEN 'Felix'
                    WHEN 282 THEN 'Florian'
                    WHEN 283 THEN 'Franco'
                    WHEN 284 THEN 'Franz'
                    WHEN 285 THEN 'Fred'
                    WHEN 286 THEN 'Fritz'
                    WHEN 287 THEN 'Gero'
                    WHEN 288 THEN 'Gregor'
                    WHEN 289 THEN 'Guido'
                    WHEN 290 THEN 'Gustav'
                    WHEN 291 THEN 'Hans'
                    WHEN 292 THEN 'Hardy'
                    WHEN 293 THEN 'Harry'
                    WHEN 294 THEN 'Heiko'
                    WHEN 295 THEN 'Helge'
                    WHEN 296 THEN 'Henry'
                    WHEN 297 THEN 'Holger'
                    WHEN 298 THEN 'Igor'
                    WHEN 299 THEN 'Ingo'
                    WHEN 300 THEN 'Jan'
                    WHEN 301 THEN 'Janusz'
                    WHEN 302 THEN 'Jens'
                    WHEN 303 THEN 'Jeremy'
                    WHEN 304 THEN 'Joachim'
                    WHEN 305 THEN 'Jochen'
                    WHEN 306 THEN 'Joel'
                    WHEN 307 THEN 'Johan'
                    WHEN 308 THEN 'John'
                    WHEN 309 THEN 'Jonas'
                    WHEN 310 THEN 'Jordan'
                    WHEN 311 THEN 'Josef'
                    WHEN 312 THEN 'Jost'
                    WHEN 313 THEN 'Julian'
                    WHEN 314 THEN 'Justus'
                    WHEN 315 THEN 'Kalle'
                    WHEN 316 THEN 'Kaspar'
                    WHEN 317 THEN 'Kenny'
                    WHEN 318 THEN 'Kevin'
                    WHEN 319 THEN 'Kim'
                    WHEN 320 THEN 'Klaus'
                    WHEN 321 THEN 'Knut'
                    WHEN 322 THEN 'Lars'
                    WHEN 323 THEN 'Laurens'
                    WHEN 324 THEN 'Lennart'
                    WHEN 325 THEN 'Leo'
                    WHEN 326 THEN 'Leon'
                    WHEN 327 THEN 'Liam'
                    WHEN 328 THEN 'Linus'
                    WHEN 329 THEN 'Logan'
                    WHEN 330 THEN 'Lorenz'
                    WHEN 331 THEN 'Louis'
                    WHEN 332 THEN 'Luca'
                    WHEN 333 THEN 'Lucas'
                    WHEN 334 THEN 'Luis'
                    WHEN 335 THEN 'Lukas'
                    WHEN 336 THEN 'Mads'
                    WHEN 337 THEN 'Magnus'
                    WHEN 338 THEN 'Manuel'
                    WHEN 339 THEN 'Marc'
                    WHEN 340 THEN 'Marcel'
                    WHEN 341 THEN 'Marco'
                    WHEN 342 THEN 'Marcus'
                    WHEN 343 THEN 'Marian'
                    WHEN 344 THEN 'Mario'
                    WHEN 345 THEN 'Marius'
                    WHEN 346 THEN 'Mark'
                    WHEN 347 THEN 'Marko'
                    WHEN 348 THEN 'Markus'
                    WHEN 349 THEN 'Marlon'
                    WHEN 350 THEN 'Martin'
                    WHEN 351 THEN 'Marvin'
                    WHEN 352 THEN 'Mathias'
                    WHEN 353 THEN 'Mats'
                    WHEN 354 THEN 'Matt'
                    WHEN 355 THEN 'Matteo'
                    WHEN 356 THEN 'Matthew'
                    WHEN 357 THEN 'Matthias'
                    WHEN 358 THEN 'Maurice'
                    WHEN 359 THEN 'Max'
                    WHEN 360 THEN 'Maximilian'
                    WHEN 361 THEN 'Mehmet'
                    WHEN 362 THEN 'Michael'
                    WHEN 363 THEN 'Michel'
                    WHEN 364 THEN 'Miguel'
                    WHEN 365 THEN 'Mike'
                    WHEN 366 THEN 'Milan'
                    WHEN 367 THEN 'Milo'
                    WHEN 368 THEN 'Mohammed'
                    WHEN 369 THEN 'Moritz'
                    WHEN 370 THEN 'Mustafa'
                    WHEN 371 THEN 'Nathan'
                    WHEN 372 THEN 'Neven'
                    WHEN 373 THEN 'Nick'
                    WHEN 374 THEN 'Nico'
                    WHEN 375 THEN 'Nicolas'
                    WHEN 376 THEN 'Nils'
                    WHEN 377 THEN 'Noah'
                    WHEN 378 THEN 'Oliver'
                    WHEN 379 THEN 'Omar'
                    WHEN 380 THEN 'Oscar'
                    WHEN 381 THEN 'Pascal'
                    WHEN 382 THEN 'Patrick'
                    WHEN 383 THEN 'Paul'
                    WHEN 384 THEN 'Peer'
                    WHEN 385 THEN 'Peter'
                    WHEN 386 THEN 'Philip'
                    WHEN 387 THEN 'Philipp'
                    WHEN 388 THEN 'Pierre'
                    WHEN 389 THEN 'Pit'
                    WHEN 390 THEN 'Quentin'
                    WHEN 391 THEN 'Rafael'
                    WHEN 392 THEN 'Ralf'
                    WHEN 393 THEN 'Ramon'
                    WHEN 394 THEN 'Raphael'
                    WHEN 395 THEN 'Rasmus'
                    WHEN 396 THEN 'Raul'
                    WHEN 397 THEN 'Rene'
                    WHEN 398 THEN 'Ricardo'
                    WHEN 399 THEN 'Rico'
                    WHEN 400 THEN 'Robert'
                    WHEN 401 THEN 'Roberto'
                    WHEN 402 THEN 'Roger'
                    WHEN 403 THEN 'Roland'
                    WHEN 404 THEN 'Roman'
                    WHEN 405 THEN 'Ron'
                    WHEN 406 THEN 'Ronny'
                    WHEN 407 THEN 'Rudi'
                    WHEN 408 THEN 'Ryan'
                    WHEN 409 THEN 'Sam'
                    WHEN 410 THEN 'Samuel'
                    WHEN 411 THEN 'Sandro'
                    WHEN 412 THEN 'Sascha'
                    WHEN 413 THEN 'Sean'
                    WHEN 414 THEN 'Sebastian'
                    WHEN 415 THEN 'Sergej'
                    WHEN 416 THEN 'Simon'
                    WHEN 417 THEN 'Stefan'
                    WHEN 418 THEN 'Stephan'
                    WHEN 419 THEN 'Steve'
                    WHEN 420 THEN 'Steven'
                    WHEN 421 THEN 'Sven'
                    WHEN 422 THEN 'Thilo'
                    WHEN 423 THEN 'Thomas'
                    WHEN 424 THEN 'Thorben'
                    WHEN 425 THEN 'Thorsten'
                    WHEN 426 THEN 'Till'
                    WHEN 427 THEN 'Tim'
                    WHEN 428 THEN 'Timo'
                    WHEN 429 THEN 'Timothy'
                    WHEN 430 THEN 'Tino'
                    WHEN 431 THEN 'Tobias'
                    WHEN 432 THEN 'Tom'
                    WHEN 433 THEN 'Tommy'
                    WHEN 434 THEN 'Tony'
                    WHEN 435 THEN 'Torben'
                    WHEN 436 THEN 'Torsten'
                    WHEN 437 THEN 'Udo'
                    WHEN 438 THEN 'Ulli'
                    WHEN 439 THEN 'Uwe'
                    WHEN 440 THEN 'Valentin'
                    WHEN 441 THEN 'Victor'
                    WHEN 442 THEN 'Viktor'
                    WHEN 443 THEN 'Vincent'
                    WHEN 444 THEN 'Vito'
                    WHEN 445 THEN 'Vladimir'
                    WHEN 446 THEN 'Waldo'
                    WHEN 447 THEN 'Walther'
                    WHEN 448 THEN 'Wayne'
                    WHEN 449 THEN 'Willi'
                    WHEN 450 THEN 'Wim'
                    WHEN 451 THEN 'Xaver'
                    WHEN 452 THEN 'Yannick'
                    WHEN 453 THEN 'Yves'
                    WHEN 454 THEN 'Zachary'
                    WHEN 455 THEN 'Adrian'
                    WHEN 456 THEN 'Ahmed'
                    WHEN 457 THEN 'Alan'
                    WHEN 458 THEN 'Albert'
                    WHEN 459 THEN 'Alec'
                    WHEN 460 THEN 'Alex'
                    WHEN 461 THEN 'Alexandre'
                    WHEN 462 THEN 'Ali'
                    WHEN 463 THEN 'Alvin'
                    WHEN 464 THEN 'Andre'
                    WHEN 465 THEN 'Andrew'
                    WHEN 466 THEN 'Andy'
                    WHEN 467 THEN 'Angelo'
                    WHEN 468 THEN 'Antonio'
                    WHEN 469 THEN 'Arne'
                    WHEN 470 THEN 'Arthur'
                    WHEN 471 THEN 'Austin'
                    WHEN 472 THEN 'Barry'
                    WHEN 473 THEN 'Bastian'
                    WHEN 474 THEN 'Benedict'
                    WHEN 475 THEN 'Benny'
                    WHEN 476 THEN 'Bernard'
                    WHEN 477 THEN 'Billy'
                    WHEN 478 THEN 'Blake'
                    WHEN 479 THEN 'Bobby'
                    WHEN 480 THEN 'Brandon'
                    WHEN 481 THEN 'Brett'
                    WHEN 482 THEN 'Brian'
                    WHEN 483 THEN 'Bruce'
                    WHEN 484 THEN 'Bruno'
                    WHEN 485 THEN 'Bryan'
                    WHEN 486 THEN 'Carl'
                    WHEN 487 THEN 'Carlos'
                    WHEN 488 THEN 'Chad'
                    WHEN 489 THEN 'Charlie'
                    WHEN 490 THEN 'Chris'
                    WHEN 491 THEN 'Chuck'
                    WHEN 492 THEN 'Colin'
                    WHEN 493 THEN 'Connor'
                    WHEN 494 THEN 'Craig'
                    WHEN 495 THEN 'Dale'
                    WHEN 496 THEN 'Dan'
                    WHEN 497 THEN 'Danny'
                    WHEN 498 THEN 'Dave'
                    WHEN 499 THEN 'Dean'
                    WHEN 500 THEN 'Dennis'
                    WHEN 501 THEN 'Derek'
                    WHEN 502 THEN 'Diego'
                    WHEN 503 THEN 'Dominic'
                    WHEN 504 THEN 'Don'
                    WHEN 505 THEN 'Doug'
                    WHEN 506 THEN 'Drew'
                    WHEN 507 THEN 'Dustin'
                    WHEN 508 THEN 'Earl'
                    WHEN 509 THEN 'Eddie'
                    WHEN 510 THEN 'Edgar'
                    WHEN 511 THEN 'Edward'
                    WHEN 512 THEN 'Elijah'
                    WHEN 513 THEN 'Elliott'
                    WHEN 514 THEN 'Enrique'
                    WHEN 515 THEN 'Eric'
                    WHEN 516 THEN 'Ernesto'
                    WHEN 517 THEN 'Ethan'
                    WHEN 518 THEN 'Eugene'
                    WHEN 519 THEN 'Evan'
                    WHEN 520 THEN 'Fernando'
                    WHEN 521 THEN 'Floyd'
                    WHEN 522 THEN 'Francis'
                    WHEN 523 THEN 'Francisco'
                    WHEN 524 THEN 'Frank'
                    WHEN 525 THEN 'Fred'
                    WHEN 526 THEN 'Gabriel'
                    WHEN 527 THEN 'Gary'
                    WHEN 528 THEN 'George'
                    WHEN 529 THEN 'Gerald'
                    WHEN 530 THEN 'Gilbert'
                    WHEN 531 THEN 'Gordon'
                    WHEN 532 THEN 'Grant'
                    WHEN 533 THEN 'Greg'
                    WHEN 534 THEN 'Gregory'
                    WHEN 535 THEN 'Harold'
                    WHEN 536 THEN 'Harvey'
                    WHEN 537 THEN 'Hector'
                    WHEN 538 THEN 'Howard'
                    WHEN 539 THEN 'Hugh'
                    WHEN 540 THEN 'Ian'
                    WHEN 541 THEN 'Ivan'
                    WHEN 542 THEN 'Jack'
                    WHEN 543 THEN 'Jacob'
                    WHEN 544 THEN 'Jake'
                    WHEN 545 THEN 'James'
                    WHEN 546 THEN 'Jamie'
                    WHEN 547 THEN 'Jason'
                    WHEN 548 THEN 'Jay'
                    WHEN 549 THEN 'Jeff'
                    WHEN 550 THEN 'Jeffrey'
                    WHEN 551 THEN 'Jeremy'
                    WHEN 552 THEN 'Jerry'
                    WHEN 553 THEN 'Jesse'
                    WHEN 554 THEN 'Jim'
                    WHEN 555 THEN 'Jimmy'
                    WHEN 556 THEN 'Joe'
                    WHEN 557 THEN 'John'
                    WHEN 558 THEN 'Johnny'
                    WHEN 559 THEN 'Jon'
                    WHEN 560 THEN 'Jonathan'
                    WHEN 561 THEN 'Jordan'
                    WHEN 562 THEN 'Jose'
                    WHEN 563 THEN 'Joseph'
                    WHEN 564 THEN 'Josh'
                    WHEN 565 THEN 'Joshua'
                    WHEN 566 THEN 'Juan'
                    WHEN 567 THEN 'Justin'
                    WHEN 568 THEN 'Keith'
                    WHEN 569 THEN 'Kenneth'
                    WHEN 570 THEN 'Kent'
                    WHEN 571 THEN 'Kyle'
                    WHEN 572 THEN 'Lance'
                    WHEN 573 THEN 'Larry'
                    WHEN 574 THEN 'Lawrence'
                    WHEN 575 THEN 'Lee'
                    WHEN 576 THEN 'Leonard'
                    WHEN 577 THEN 'Lewis'
                    WHEN 578 THEN 'Lloyd'
                    WHEN 579 THEN 'Louis'
                    WHEN 580 THEN 'Luke'
                    WHEN 581 THEN 'Marcus'
                    WHEN 582 THEN 'Mark'
                    WHEN 583 THEN 'Martin'
                    WHEN 584 THEN 'Matthew'
                    WHEN 585 THEN 'Maurice'
                    WHEN 586 THEN 'Melvin'
                    WHEN 587 THEN 'Miguel'
                    WHEN 588 THEN 'Mitchell'
                    WHEN 589 THEN 'Nathan'
                    WHEN 590 THEN 'Neil'
                    WHEN 591 THEN 'Nicholas'
                    WHEN 592 THEN 'Nick'
                    WHEN 593 THEN 'Noah'
                    WHEN 594 THEN 'Norman'
                    WHEN 595 THEN 'Oscar'
                    WHEN 596 THEN 'Owen'
                    WHEN 597 THEN 'Pablo'
                    WHEN 598 THEN 'Patrick'
                    WHEN 599 THEN 'Paul'
                    WHEN 600 THEN 'Pedro'
                    WHEN 601 THEN 'Perry'
                    WHEN 602 THEN 'Pete'
                    WHEN 603 THEN 'Philip'
                    WHEN 604 THEN 'Phillip'
                    WHEN 605 THEN 'Ralph'
                    WHEN 606 THEN 'Randy'
                    WHEN 607 THEN 'Ray'
                    WHEN 608 THEN 'Raymond'
                    WHEN 609 THEN 'Ricardo'
                    WHEN 610 THEN 'Richard'
                    WHEN 611 THEN 'Rick'
                    WHEN 612 THEN 'Robert'
                    WHEN 613 THEN 'Roberto'
                    WHEN 614 THEN 'Roger'
                    WHEN 615 THEN 'Roland'
                    WHEN 616 THEN 'Ron'
                    WHEN 617 THEN 'Ronald'
                    WHEN 618 THEN 'Roy'
                    WHEN 619 THEN 'Russell'
                    WHEN 620 THEN 'Ryan'
                    WHEN 621 THEN 'Samuel'
                    WHEN 622 THEN 'Scott'
                    WHEN 623 THEN 'Sean'
                    WHEN 624 THEN 'Shane'
                    WHEN 625 THEN 'Shawn'
                    WHEN 626 THEN 'Stephen'
                    WHEN 627 THEN 'Steve'
                    WHEN 628 THEN 'Steven'
                    WHEN 629 THEN 'Terry'
                    WHEN 630 THEN 'Theodore'
                    WHEN 631 THEN 'Timothy'
                    WHEN 632 THEN 'Todd'
                    WHEN 633 THEN 'Tom'
                    WHEN 634 THEN 'Tommy'
                    WHEN 635 THEN 'Tony'
                    WHEN 636 THEN 'Travis'
                    WHEN 637 THEN 'Troy'
                    WHEN 638 THEN 'Tyler'
                    WHEN 639 THEN 'Vernon'
                    WHEN 640 THEN 'Victor'
                    WHEN 641 THEN 'Vincent'
                    WHEN 642 THEN 'Walter'
                    WHEN 643 THEN 'Warren'
                    WHEN 644 THEN 'Wayne'
                    WHEN 645 THEN 'Wesley'
                    WHEN 646 THEN 'William'
                    WHEN 647 THEN 'Willie'
                    WHEN 648 THEN 'Xavier'
                    WHEN 649 THEN 'Zachary'
                    WHEN 650 THEN 'Abel'
                    WHEN 651 THEN 'Abraham'
                    WHEN 652 THEN 'Adrien'
                    WHEN 653 THEN 'Alain'
                    WHEN 654 THEN 'Alejandro'
                    WHEN 655 THEN 'Alfredo'
                    WHEN 656 THEN 'Allan'
                    WHEN 657 THEN 'Allen'
                    WHEN 658 THEN 'Alonso'
                    WHEN 659 THEN 'Amos'
                    WHEN 660 THEN 'Andre'
                    WHEN 661 THEN 'Angel'
                    WHEN 662 THEN 'Anthony'
                    WHEN 663 THEN 'Antoine'
                    WHEN 664 THEN 'Arturo'
                    WHEN 665 THEN 'Augustus'
                    WHEN 666 THEN 'Axel'
                    WHEN 667 THEN 'Benjamin'
                    WHEN 668 THEN 'Benny'
                    WHEN 669 THEN 'Bernardo'
                    WHEN 670 THEN 'Boyd'
                    WHEN 671 THEN 'Bryce'
                    WHEN 672 THEN 'Calvin'
                    WHEN 673 THEN 'Cameron'
                    WHEN 674 THEN 'Cedric'
                    WHEN 675 THEN 'Christopher'
                    WHEN 676 THEN 'Clarence'
                    WHEN 677 THEN 'Claude'
                    WHEN 678 THEN 'Clifford'
                    WHEN 679 THEN 'Curtis'
                    WHEN 680 THEN 'Damian'
                    WHEN 681 THEN 'Damon'
                    WHEN 682 THEN 'Darren'
                    WHEN 683 THEN 'Daryl'
                    WHEN 684 THEN 'Dexter'
                    WHEN 685 THEN 'Donald'
                    WHEN 686 THEN 'Douglas'
                    WHEN 687 THEN 'Dwight'
                    WHEN 688 THEN 'Edgar'
                    WHEN 689 THEN 'Edmund'
                    WHEN 690 THEN 'Edwin'
                    WHEN 691 THEN 'Elmer'
                    WHEN 692 THEN 'Emilio'
                    WHEN 693 THEN 'Ernest'
                    WHEN 694 THEN 'Felipe'
                    WHEN 695 THEN 'Felix'
                    WHEN 696 THEN 'Floyd'
                    WHEN 697 THEN 'Frederick'
                    WHEN 698 THEN 'Geoffrey'
                    WHEN 699 THEN 'Gerard'
                    WHEN 700 THEN 'Giles'
                    WHEN 701 THEN 'Glenn'
                    WHEN 702 THEN 'Gustavo'
                    WHEN 703 THEN 'Hans'
                    WHEN 704 THEN 'Hank'
                    WHEN 705 THEN 'Harvey'
                    WHEN 706 THEN 'Heather'
                    WHEN 707 THEN 'Hector'
                    WHEN 708 THEN 'Henry'
                    WHEN 709 THEN 'Herman'
                    WHEN 710 THEN 'Irving'
                    WHEN 711 THEN 'Isaac'
                    WHEN 712 THEN 'Jackson'
                    WHEN 713 THEN 'Javier'
                    WHEN 714 THEN 'Jerome'
                    WHEN 715 THEN 'Joaquin'
                    WHEN 716 THEN 'Joel'
                    WHEN 717 THEN 'Jorge'
                    WHEN 718 THEN 'Julius'
                    WHEN 719 THEN 'Karl'
                    WHEN 720 THEN 'Kelvin'
                    WHEN 721 THEN 'Kevin'
                    WHEN 722 THEN 'Kurt'
                    WHEN 723 THEN 'Lamar'
                    WHEN 724 THEN 'Leroy'
                    WHEN 725 THEN 'Lionel'
                    WHEN 726 THEN 'Lowell'
                    WHEN 727 THEN 'Luther'
                    WHEN 728 THEN 'Marvin'
                    WHEN 729 THEN 'Mason'
                    WHEN 730 THEN 'Mickey'
                    WHEN 731 THEN 'Milton'
                    WHEN 732 THEN 'Moses'
                    WHEN 733 THEN 'Nathaniel'
                    WHEN 734 THEN 'Noel'
                    WHEN 735 THEN 'Omar'
                    WHEN 736 THEN 'Orlando'
                    WHEN 737 THEN 'Otis'
                    WHEN 738 THEN 'Pascal'
                    WHEN 739 THEN 'Preston'
                    WHEN 740 THEN 'Quinton'
                    WHEN 741 THEN 'Randall'
                    WHEN 742 THEN 'Raul'
                    WHEN 743 THEN 'Rex'
                    WHEN 744 THEN 'Rodney'
                    WHEN 745 THEN 'Salvador'
                    WHEN 746 THEN 'Samson'
                    WHEN 747 THEN 'Santiago'
                    WHEN 748 THEN 'Sergio'
                    WHEN 749 THEN 'Solomon'
                    ELSE 'Theodor'
                END
            WHEN 2 THEN  -- Weibliche Vornamen
                CASE (v_counter MOD 750) + 1
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
                    WHEN 30 THEN 'Julia'
                    WHEN 31 THEN 'Christine'
                    WHEN 32 THEN 'Britta'
                    WHEN 33 THEN 'Cornelia'
                    WHEN 34 THEN 'Daniela'
                    WHEN 35 THEN 'Elisabeth'
                    WHEN 36 THEN 'Franziska'
                    WHEN 37 THEN 'Gaby'
                    WHEN 38 THEN 'Hanna'
                    WHEN 39 THEN 'Iris'
                    WHEN 40 THEN 'Jana'
                    WHEN 41 THEN 'Katharina'
                    WHEN 42 THEN 'Laura'
                    WHEN 43 THEN 'Maria'
                    WHEN 44 THEN 'Nadine'
                    WHEN 45 THEN 'Olga'
                    WHEN 46 THEN 'Patricia'
                    WHEN 47 THEN 'Ramona'
                    WHEN 48 THEN 'Sandra'
                    WHEN 49 THEN 'Tanja'
                    WHEN 50 THEN 'Ulrike'
                    WHEN 51 THEN 'Vanessa'
                    WHEN 52 THEN 'Yvonne'
                    WHEN 53 THEN 'Astrid'
                    WHEN 54 THEN 'Bärbel'
                    WHEN 55 THEN 'Carmen'
                    WHEN 56 THEN 'Diana'
                    WHEN 57 THEN 'Eva'
                    WHEN 58 THEN 'Frieda'
                    WHEN 59 THEN 'Gudrun'
                    WHEN 60 THEN 'Helena'
                    WHEN 61 THEN 'Ilona'
                    WHEN 62 THEN 'Jessica'
                    WHEN 63 THEN 'Kristina'
                    WHEN 64 THEN 'Linda'
                    WHEN 65 THEN 'Melanie'
                    WHEN 66 THEN 'Nadia'
                    WHEN 67 THEN 'Olivia'
                    WHEN 68 THEN 'Paula'
                    WHEN 69 THEN 'Regina'
                    WHEN 70 THEN 'Silvia'
                    WHEN 71 THEN 'Theresa'
                    WHEN 72 THEN 'Ursula'
                    WHEN 73 THEN 'Vera'
                    WHEN 74 THEN 'Waltraud'
                    WHEN 75 THEN 'Anna'
                    WHEN 76 THEN 'Brigitte'
                    WHEN 77 THEN 'Carola'
                    WHEN 78 THEN 'Dagmar'
                    WHEN 79 THEN 'Edith'
                    WHEN 80 THEN 'Felicitas'
                    WHEN 81 THEN 'Gerda'
                    WHEN 82 THEN 'Hildegard'
                    WHEN 83 THEN 'Irmgard'
                    WHEN 84 THEN 'Jutta'
                    WHEN 85 THEN 'Konstanze'
                    WHEN 86 THEN 'Lieselotte'
                    WHEN 87 THEN 'Margarete'
                    WHEN 88 THEN 'Natalie'
                    WHEN 89 THEN 'Otilie'
                    WHEN 90 THEN 'Priska'
                    WHEN 91 THEN 'Rosa'
                    WHEN 92 THEN 'Sonja'
                    WHEN 93 THEN 'Tatjana'
                    WHEN 94 THEN 'Uschi'
                    WHEN 95 THEN 'Veronika'
                    WHEN 96 THEN 'Wilhelmine'
                    WHEN 97 THEN 'Xenia'
                    WHEN 98 THEN 'Yvette'
                    WHEN 99 THEN 'Zita'
                    WHEN 100 THEN 'Adelheid'
                    WHEN 101 THEN 'Beatrice'
                    WHEN 102 THEN 'Cäcilia'
                    WHEN 103 THEN 'Dorothea'
                    WHEN 104 THEN 'Elfriede'
                    WHEN 105 THEN 'Friederike'
                    WHEN 106 THEN 'Gerlinde'
                    WHEN 107 THEN 'Hannelore'
                    WHEN 108 THEN 'Ingeborg'
                    WHEN 109 THEN 'Johanna'
                    WHEN 110 THEN 'Klara'
                    WHEN 111 THEN 'Lena'
                    WHEN 112 THEN 'Marlene'
                    WHEN 113 THEN 'Nora'
                    WHEN 114 THEN 'Ophelia'
                    WHEN 115 THEN 'Pauline'
                    WHEN 116 THEN 'Quintina'
                    WHEN 117 THEN 'Rita'
                    WHEN 118 THEN 'Sieglinde'
                    WHEN 119 THEN 'Tabea'
                    WHEN 120 THEN 'Ulricke'
                    WHEN 121 THEN 'Valerie'
                    WHEN 122 THEN 'Wanda'
                    WHEN 123 THEN 'Xara'
                    WHEN 124 THEN 'Yasmin'
                    WHEN 125 THEN 'Zelda'
                    WHEN 126 THEN 'Anita'
                    WHEN 127 THEN 'Bertha'
                    WHEN 128 THEN 'Cora'
                    WHEN 129 THEN 'Dora'
                    WHEN 130 THEN 'Emma'
                    WHEN 131 THEN 'Fiona'
                    WHEN 132 THEN 'Greta'
                    WHEN 133 THEN 'Hilda'
                    WHEN 134 THEN 'Ida'
                    WHEN 135 THEN 'Josephine'
                    WHEN 136 THEN 'Karla'
                    WHEN 137 THEN 'Luise'
                    WHEN 138 THEN 'Marta'
                    WHEN 139 THEN 'Nina'
                    WHEN 140 THEN 'Olga'
                    WHEN 141 THEN 'Petra'
                    WHEN 142 THEN 'Quinta'
                    WHEN 143 THEN 'Ruth'
                    WHEN 144 THEN 'Stella'
                    WHEN 145 THEN 'Tina'
                    WHEN 146 THEN 'Una'
                    WHEN 147 THEN 'Vivian'
                    WHEN 148 THEN 'Wendy'
                    WHEN 149 THEN 'Xandra'
                    WHEN 150 THEN 'Yolanda'
                    WHEN 151 THEN 'Zoe'
                    WHEN 152 THEN 'Alicia'
                    WHEN 153 THEN 'Belle'
                    WHEN 154 THEN 'Celeste'
                    WHEN 155 THEN 'Delphine'
                    WHEN 156 THEN 'Esther'
                    WHEN 157 THEN 'Faith'
                    WHEN 158 THEN 'Grace'
                    WHEN 159 THEN 'Hope'
                    WHEN 160 THEN 'Irene'
                    WHEN 161 THEN 'Joy'
                    WHEN 162 THEN 'Kate'
                    WHEN 163 THEN 'Lara'
                    WHEN 164 THEN 'Mia'
                    WHEN 165 THEN 'Nele'
                    WHEN 166 THEN 'Oda'
                    WHEN 167 THEN 'Pia'
                    WHEN 168 THEN 'Quinn'
                    WHEN 169 THEN 'Ruby'
                    WHEN 170 THEN 'Sara'
                    WHEN 171 THEN 'Tara'
                    WHEN 172 THEN 'Uma'
                    WHEN 173 THEN 'Vida'
                    WHEN 174 THEN 'Willa'
                    WHEN 175 THEN 'Xyla'
                    WHEN 176 THEN 'Yara'
                    WHEN 177 THEN 'Zara'
                    WHEN 178 THEN 'Alba'
                    WHEN 179 THEN 'Bianca'
                    WHEN 180 THEN 'Clara'
                    WHEN 181 THEN 'Delia'
                    WHEN 182 THEN 'Elena'
                    WHEN 183 THEN 'Flora'
                    WHEN 184 THEN 'Gloria'
                    WHEN 185 THEN 'Heidi'
                    WHEN 186 THEN 'Inga'
                    WHEN 187 THEN 'Jasmin'
                    WHEN 188 THEN 'Kira'
                    WHEN 189 THEN 'Lina'
                    WHEN 190 THEN 'Maya'
                    WHEN 191 THEN 'Nelly'
                    WHEN 192 THEN 'Oliva'
                    WHEN 193 THEN 'Penelope'
                    WHEN 194 THEN 'Queenie'
                    WHEN 195 THEN 'Rhea'
                    WHEN 196 THEN 'Sophia'
                    WHEN 197 THEN 'Thea'
                    WHEN 198 THEN 'Ursa'
                    WHEN 199 THEN 'Viola'
                    WHEN 200 THEN 'Wilma'
                    WHEN 201 THEN 'Xiomara'
                    WHEN 202 THEN 'Yelena'
                    WHEN 203 THEN 'Zelma'
                    WHEN 204 THEN 'Amanda'
                    WHEN 205 THEN 'Brenda'
                    WHEN 206 THEN 'Cynthia'
                    WHEN 207 THEN 'Deborah'
                    WHEN 208 THEN 'Estelle'
                    WHEN 209 THEN 'Fatima'
                    WHEN 210 THEN 'Georgina'
                    WHEN 211 THEN 'Hannah'
                    WHEN 212 THEN 'Isabella'
                    WHEN 213 THEN 'Jennifer'
                    WHEN 214 THEN 'Kimberley'
                    WHEN 215 THEN 'Louisa'
                    WHEN 216 THEN 'Michelle'
                    WHEN 217 THEN 'Nancy'
                    WHEN 218 THEN 'Odette'
                    WHEN 219 THEN 'Priscilla'
                    WHEN 220 THEN 'Quorra'
                    WHEN 221 THEN 'Rebecca'
                    WHEN 222 THEN 'Stephanie'
                    WHEN 223 THEN 'Tamara'
                    WHEN 224 THEN 'Ursula'
                    WHEN 225 THEN 'Victoria'
                    WHEN 226 THEN 'Whitney'
                    WHEN 227 THEN 'Xanthe'
                    WHEN 228 THEN 'Yvonne'
                    WHEN 229 THEN 'Zora'
                    WHEN 230 THEN 'Amy'
                    WHEN 231 THEN 'Beth'
                    WHEN 232 THEN 'Carrie'
                    WHEN 233 THEN 'Donna'
                    WHEN 234 THEN 'Emily'
                    WHEN 235 THEN 'Frances'
                    WHEN 236 THEN 'Gail'
                    WHEN 237 THEN 'Holly'
                    WHEN 238 THEN 'Irma'
                    WHEN 239 THEN 'Julie'
                    WHEN 240 THEN 'Kelly'
                    WHEN 241 THEN 'Lisa'
                    WHEN 242 THEN 'Mandy'
                    WHEN 243 THEN 'Nora'
                    WHEN 244 THEN 'Opal'
                    WHEN 245 THEN 'Penny'
                    WHEN 246 THEN 'Qiana'
                    WHEN 247 THEN 'Rachel'
                    WHEN 248 THEN 'Susan'
                    WHEN 249 THEN 'Tracy'
                    WHEN 250 THEN 'Ulyana'
                    WHEN 251 THEN 'Valerie'
                    WHEN 252 THEN 'Wendy'
                    WHEN 253 THEN 'Xenia'
                    WHEN 254 THEN 'Yolanda'
                    WHEN 255 THEN 'Zelda'
                    WHEN 256 THEN 'Alice'
                    WHEN 257 THEN 'Betty'
                    WHEN 258 THEN 'Carol'
                    WHEN 259 THEN 'Dorothy'
                    WHEN 260 THEN 'Evelyn'
                    WHEN 261 THEN 'Florence'
                    WHEN 262 THEN 'Gladys'
                    WHEN 263 THEN 'Helen'
                    WHEN 264 THEN 'Irene'
                    WHEN 265 THEN 'Joan'
                    WHEN 266 THEN 'Karen'
                    WHEN 267 THEN 'Louise'
                    WHEN 268 THEN 'Margaret'
                    WHEN 269 THEN 'Nancy'
                    WHEN 270 THEN 'Olivia'
                    WHEN 271 THEN 'Patricia'
                    WHEN 272 THEN 'Queen'
                    WHEN 273 THEN 'Rose'
                    WHEN 274 THEN 'Shirley'
                    WHEN 275 THEN 'Teresa'
                    WHEN 276 THEN 'Una'
                    WHEN 277 THEN 'Virginia'
                    WHEN 278 THEN 'Winifred'
                    WHEN 279 THEN 'Xara'
                    WHEN 280 THEN 'Yvette'
                    WHEN 281 THEN 'Zoe'
                    WHEN 282 THEN 'Agnes'
                    WHEN 283 THEN 'Bonnie'
                    WHEN 284 THEN 'Catherine'
                    WHEN 285 THEN 'Diane'
                    WHEN 286 THEN 'Ellen'
                    WHEN 287 THEN 'Faye'
                    WHEN 288 THEN 'Georgia'
                    WHEN 289 THEN 'Hazel'
                    WHEN 290 THEN 'Ivy'
                    WHEN 291 THEN 'Janet'
                    WHEN 292 THEN 'Kay'
                    WHEN 293 THEN 'Lynn'
                    WHEN 294 THEN 'Marie'
                    WHEN 295 THEN 'Norma'
                    WHEN 296 THEN 'Opal'
                    WHEN 297 THEN 'Pearl'
                    WHEN 298 THEN 'Quinta'
                    WHEN 299 THEN 'Ruby'
                    WHEN 300 THEN 'Sandra'
                    WHEN 301 THEN 'Thelma'
                    WHEN 302 THEN 'Una'
                    WHEN 303 THEN 'Vera'
                    WHEN 304 THEN 'Wanda'
                    WHEN 305 THEN 'Xenia'
                    WHEN 306 THEN 'Yolanda'
                    WHEN 307 THEN 'Zelda'
                    WHEN 308 THEN 'Adelaide'
                    WHEN 309 THEN 'Beatrice'
                    WHEN 310 THEN 'Cecilia'
                    WHEN 311 THEN 'Delores'
                    WHEN 312 THEN 'Edna'
                    WHEN 313 THEN 'Fern'
                    WHEN 314 THEN 'Gladys'
                    WHEN 315 THEN 'Hazel'
                    WHEN 316 THEN 'Iris'
                    WHEN 317 THEN 'Joyce'
                    WHEN 318 THEN 'Katherine'
                    WHEN 319 THEN 'Lorraine'
                    WHEN 320 THEN 'Mildred'
                    WHEN 321 THEN 'Norma'
                    WHEN 322 THEN 'Opal'
                    WHEN 323 THEN 'Phyllis'
                    WHEN 324 THEN 'Queen'
                    WHEN 325 THEN 'Ruby'
                    WHEN 326 THEN 'Stella'
                    WHEN 327 THEN 'Thelma'
                    WHEN 328 THEN 'Una'
                    WHEN 329 THEN 'Violet'
                    WHEN 330 THEN 'Winifred'
                    WHEN 331 THEN 'Xenia'
                    WHEN 332 THEN 'Yvonne'
                    WHEN 333 THEN 'Zelda'
                    WHEN 334 THEN 'Abigail'
                    WHEN 335 THEN 'Bernice'
                    WHEN 336 THEN 'Constance'
                    WHEN 337 THEN 'Dolores'
                    WHEN 338 THEN 'Estelle'
                    WHEN 339 THEN 'Florence'
                    WHEN 340 THEN 'Grace'
                    WHEN 341 THEN 'Hazel'
                    WHEN 342 THEN 'Irma'
                    WHEN 343 THEN 'Josephine'
                    WHEN 344 THEN 'Katherine'
                    WHEN 345 THEN 'Lillian'
                    WHEN 346 THEN 'Myrtle'
                    WHEN 347 THEN 'Norma'
                    WHEN 348 THEN 'Opal'
                    WHEN 349 THEN 'Pearl'
                    WHEN 350 THEN 'Queen'
                    WHEN 351 THEN 'Ruth'
                    WHEN 352 THEN 'Stella'
                    WHEN 353 THEN 'Thelma'
                    WHEN 354 THEN 'Una'
                    WHEN 355 THEN 'Violet'
                    WHEN 356 THEN 'Winifred'
                    WHEN 357 THEN 'Xenia'
                    WHEN 358 THEN 'Yvonne'
                    WHEN 359 THEN 'Zelda'
                    WHEN 360 THEN 'Alexandra'
                    WHEN 361 THEN 'Bridget'
                    WHEN 362 THEN 'Camille'
                    WHEN 363 THEN 'Deanne'
                    WHEN 364 THEN 'Erica'
                    WHEN 365 THEN 'Francine'
                    WHEN 366 THEN 'Gwendolyn'
                    WHEN 367 THEN 'Heather'
                    WHEN 368 THEN 'Ingrid'
                    WHEN 369 THEN 'Jacqueline'
                    WHEN 370 THEN 'Kimberly'
                    WHEN 371 THEN 'Loretta'
                    WHEN 372 THEN 'Maureen'
                    WHEN 373 THEN 'Nichole'
                    WHEN 374 THEN 'Odessa'
                    WHEN 375 THEN 'Pamela'
                    WHEN 376 THEN 'Quentin'
                    WHEN 377 THEN 'Roxanne'
                    WHEN 378 THEN 'Suzanne'
                    WHEN 379 THEN 'Theresa'
                    WHEN 380 THEN 'Ursula'
                    WHEN 381 THEN 'Vanessa'
                    WHEN 382 THEN 'Wanda'
                    WHEN 383 THEN 'Xenia'
                    WHEN 384 THEN 'Yvonne'
                    WHEN 385 THEN 'Zelda'
                    WHEN 386 THEN 'Adrienne'
                    WHEN 387 THEN 'Beverly'
                    WHEN 388 THEN 'Charlene'
                    WHEN 389 THEN 'Denise'
                    WHEN 390 THEN 'Eileen'
                    WHEN 391 THEN 'Francine'
                    WHEN 392 THEN 'Gail'
                    WHEN 393 THEN 'Hilda'
                    WHEN 394 THEN 'Irene'
                    WHEN 395 THEN 'Janice'
                    WHEN 396 THEN 'Karen'
                    WHEN 397 THEN 'Linda'
                    WHEN 398 THEN 'Martha'
                    WHEN 399 THEN 'Nancy'
                    WHEN 400 THEN 'Olivia'
                    WHEN 401 THEN 'Patricia'
                    WHEN 402 THEN 'Queen'
                    WHEN 403 THEN 'Rose'
                    WHEN 404 THEN 'Sandra'
                    WHEN 405 THEN 'Teresa'
                    WHEN 406 THEN 'Una'
                    WHEN 407 THEN 'Victoria'
                    WHEN 408 THEN 'Wanda'
                    WHEN 409 THEN 'Xenia'
                    WHEN 410 THEN 'Yvonne'
                    WHEN 411 THEN 'Zelda'
                    WHEN 412 THEN 'Angela'
                    WHEN 413 THEN 'Brenda'
                    WHEN 414 THEN 'Cheryl'
                    WHEN 415 THEN 'Debra'
                    WHEN 416 THEN 'Evelyn'
                    WHEN 417 THEN 'Fiona'
                    WHEN 418 THEN 'Gloria'
                    WHEN 419 THEN 'Helen'
                    WHEN 420 THEN 'Irene'
                    WHEN 421 THEN 'Janet'
                    WHEN 422 THEN 'Karen'
                    WHEN 423 THEN 'Lisa'
                    WHEN 424 THEN 'Mary'
                    WHEN 425 THEN 'Nancy'
                    WHEN 426 THEN 'Olivia'
                    WHEN 427 THEN 'Patricia'
                    WHEN 428 THEN 'Queen'
                    WHEN 429 THEN 'Rose'
                    WHEN 430 THEN 'Sandra'
                    WHEN 431 THEN 'Teresa'
                    WHEN 432 THEN 'Una'
                    WHEN 433 THEN 'Victoria'
                    WHEN 434 THEN 'Wanda'
                    WHEN 435 THEN 'Xenia'
                    WHEN 436 THEN 'Yvonne'
                    WHEN 437 THEN 'Zelda'
                    WHEN 438 THEN 'Amelia'
                    WHEN 439 THEN 'Bonnie'
                    WHEN 440 THEN 'Cindy'
                    WHEN 441 THEN 'Diane'
                    WHEN 442 THEN 'Ellen'
                    WHEN 443 THEN 'Faye'
                    WHEN 444 THEN 'Gina'
                    WHEN 445 THEN 'Hazel'
                    WHEN 446 THEN 'Irene'
                    WHEN 447 THEN 'Joan'
                    WHEN 448 THEN 'Karen'
                    WHEN 449 THEN 'Linda'
                    WHEN 450 THEN 'Mary'
                    WHEN 451 THEN 'Nancy'
                    WHEN 452 THEN 'Olivia'
                    WHEN 453 THEN 'Patricia'
                    WHEN 454 THEN 'Queen'
                    WHEN 455 THEN 'Rose'
                    WHEN 456 THEN 'Sandra'
                    WHEN 457 THEN 'Teresa'
                    WHEN 458 THEN 'Una'
                    WHEN 459 THEN 'Victoria'
                    WHEN 460 THEN 'Wanda'
                    WHEN 461 THEN 'Xenia'
                    WHEN 462 THEN 'Yvonne'
                    WHEN 463 THEN 'Zelda'
                    WHEN 464 THEN 'Angie'
                    WHEN 465 THEN 'Beth'
                    WHEN 466 THEN 'Carla'
                    WHEN 467 THEN 'Debbie'
                    WHEN 468 THEN 'Elaine'
                    WHEN 469 THEN 'Frances'
                    WHEN 470 THEN 'Gail'
                    WHEN 471 THEN 'Helen'
                    WHEN 472 THEN 'Irene'
                    WHEN 473 THEN 'Joan'
                    WHEN 474 THEN 'Karen'
                    WHEN 475 THEN 'Linda'
                    WHEN 476 THEN 'Mary'
                    WHEN 477 THEN 'Nancy'
                    WHEN 478 THEN 'Olivia'
                    WHEN 479 THEN 'Patricia'
                    WHEN 480 THEN 'Queen'
                    WHEN 481 THEN 'Rose'
                    WHEN 482 THEN 'Sandra'
                    WHEN 483 THEN 'Teresa'
                    WHEN 484 THEN 'Una'
                    WHEN 485 THEN 'Victoria'
                    WHEN 486 THEN 'Wanda'
                    WHEN 487 THEN 'Xenia'
                    WHEN 488 THEN 'Yvonne'
                    WHEN 489 THEN 'Zelda'
                    WHEN 490 THEN 'April'
                    WHEN 491 THEN 'Brandy'
                    WHEN 492 THEN 'Candy'
                    WHEN 493 THEN 'Dawn'
                    WHEN 494 THEN 'Erin'
                    WHEN 495 THEN 'Faith'
                    WHEN 496 THEN 'Ginger'
                    WHEN 497 THEN 'Hope'
                    WHEN 498 THEN 'Ivy'
                    WHEN 499 THEN 'Joy'
                    WHEN 500 THEN 'Kim'
                    WHEN 501 THEN 'Lori'
                    WHEN 502 THEN 'Misty'
                    WHEN 503 THEN 'Nikki'
                    WHEN 504 THEN 'Paige'
                    WHEN 505 THEN 'Robin'
                    WHEN 506 THEN 'Stacy'
                    WHEN 507 THEN 'Tiffany'
                    WHEN 508 THEN 'Unity'
                    WHEN 509 THEN 'Vivian'
                    WHEN 510 THEN 'Wendy'
                    WHEN 511 THEN 'Xenia'
                    WHEN 512 THEN 'Yvonne'
                    WHEN 513 THEN 'Zelda'
                    WHEN 514 THEN 'Amber'
                    WHEN 515 THEN 'Brooke'
                    WHEN 516 THEN 'Crystal'
                    WHEN 517 THEN 'Destiny'
                    WHEN 518 THEN 'Erika'
                    WHEN 519 THEN 'Felicia'
                    WHEN 520 THEN 'Gina'
                    WHEN 521 THEN 'Heidi'
                    WHEN 522 THEN 'Irene'
                    WHEN 523 THEN 'Jenna'
                    WHEN 524 THEN 'Kelly'
                    WHEN 525 THEN 'Lindsey'
                    WHEN 526 THEN 'Megan'
                    WHEN 527 THEN 'Nicole'
                    WHEN 528 THEN 'Olivia'
                    WHEN 529 THEN 'Pamela'
                    WHEN 530 THEN 'Quinn'
                    WHEN 531 THEN 'Rachel'
                    WHEN 532 THEN 'Samantha'
                    WHEN 533 THEN 'Tara'
                    WHEN 534 THEN 'Una'
                    WHEN 535 THEN 'Victoria'
                    WHEN 536 THEN 'Whitney'
                    WHEN 537 THEN 'Xenia'
                    WHEN 538 THEN 'Yvonne'
                    WHEN 539 THEN 'Zelda'
                    WHEN 540 THEN 'Alicia'
                    WHEN 541 THEN 'Beth'
                    WHEN 542 THEN 'Carla'
                    WHEN 543 THEN 'Diana'
                    WHEN 544 THEN 'Emma'
                    WHEN 545 THEN 'Fiona'
                    WHEN 546 THEN 'Gail'
                    WHEN 547 THEN 'Hazel'
                    WHEN 548 THEN 'Irene'
                    WHEN 549 THEN 'Joan'
                    WHEN 550 THEN 'Karen'
                    WHEN 551 THEN 'Linda'
                    WHEN 552 THEN 'Mary'
                    WHEN 553 THEN 'Nancy'
                    WHEN 554 THEN 'Olivia'
                    WHEN 555 THEN 'Patricia'
                    WHEN 556 THEN 'Queen'
                    WHEN 557 THEN 'Rose'
                    WHEN 558 THEN 'Sandra'
                    WHEN 559 THEN 'Teresa'
                    WHEN 560 THEN 'Una'
                    WHEN 561 THEN 'Victoria'
                    WHEN 562 THEN 'Wanda'
                    WHEN 563 THEN 'Xenia'
                    WHEN 564 THEN 'Yvonne'
                    WHEN 565 THEN 'Zelda'
                    WHEN 566 THEN 'Aria'
                    WHEN 567 THEN 'Bella'
                    WHEN 568 THEN 'Chloe'
                    WHEN 569 THEN 'Daphne'
                    WHEN 570 THEN 'Ella'
                    WHEN 571 THEN 'Freya'
                    WHEN 572 THEN 'Gia'
                    WHEN 573 THEN 'Harper'
                    WHEN 574 THEN 'Isla'
                    WHEN 575 THEN 'Jade'
                    WHEN 576 THEN 'Kaia'
                    WHEN 577 THEN 'Layla'
                    WHEN 578 THEN 'Mila'
                    WHEN 579 THEN 'Nova'
                    WHEN 580 THEN 'Ophelia'
                    WHEN 581 THEN 'Piper'
                    WHEN 582 THEN 'Quinn'
                    WHEN 583 THEN 'Riley'
                    WHEN 584 THEN 'Sage'
                    WHEN 585 THEN 'Thea'
                    WHEN 586 THEN 'Uma'
                    WHEN 587 THEN 'Vera'
                    WHEN 588 THEN 'Willow'
                    WHEN 589 THEN 'Xara'
                    WHEN 590 THEN 'Yara'
                    WHEN 591 THEN 'Zara'
                    WHEN 592 THEN 'Avery'
                    WHEN 593 THEN 'Blake'
                    WHEN 594 THEN 'Cameron'
                    WHEN 595 THEN 'Drew'
                    WHEN 596 THEN 'Emery'
                    WHEN 597 THEN 'Finley'
                    WHEN 598 THEN 'Gray'
                    WHEN 599 THEN 'Hayden'
                    WHEN 600 THEN 'Indigo'
                    WHEN 601 THEN 'Jordan'
                    WHEN 602 THEN 'Kai'
                    WHEN 603 THEN 'Logan'
                    WHEN 604 THEN 'Morgan'
                    WHEN 605 THEN 'Nova'
                    WHEN 606 THEN 'Ocean'
                    WHEN 607 THEN 'Parker'
                    WHEN 608 THEN 'Quinn'
                    WHEN 609 THEN 'River'
                    WHEN 610 THEN 'Sage'
                    WHEN 611 THEN 'Taylor'
                    WHEN 612 THEN 'Uma'
                    WHEN 613 THEN 'Vale'
                    WHEN 614 THEN 'Winter'
                    WHEN 615 THEN 'Xen'
                    WHEN 616 THEN 'Yuki'
                    WHEN 617 THEN 'Zen'
                    WHEN 618 THEN 'Aspen'
                    WHEN 619 THEN 'Briar'
                    WHEN 620 THEN 'Cedar'
                    WHEN 621 THEN 'Dove'
                    WHEN 622 THEN 'Echo'
                    WHEN 623 THEN 'Fern'
                    WHEN 624 THEN 'Hazel'
                    WHEN 625 THEN 'Iris'
                    WHEN 626 THEN 'Juniper'
                    WHEN 627 THEN 'Kestrel'
                    WHEN 628 THEN 'Luna'
                    WHEN 629 THEN 'Meadow'
                    WHEN 630 THEN 'Neve'
                    WHEN 631 THEN 'Onyx'
                    WHEN 632 THEN 'Poppy'
                    WHEN 633 THEN 'Quartz'
                    WHEN 634 THEN 'Rain'
                    WHEN 635 THEN 'Sky'
                    WHEN 636 THEN 'Tempest'
                    WHEN 637 THEN 'Unity'
                    WHEN 638 THEN 'Violet'
                    WHEN 639 THEN 'Willow'
                    WHEN 640 THEN 'Xanthe'
                    WHEN 641 THEN 'Yarrow'
                    WHEN 642 THEN 'Zephyr'
                    WHEN 643 THEN 'Aria'
                    WHEN 644 THEN 'Beatrix'
                    WHEN 645 THEN 'Cordelia'
                    WHEN 646 THEN 'Dahlia'
                    WHEN 647 THEN 'Evangeline'
                    WHEN 648 THEN 'Francesca'
                    WHEN 649 THEN 'Genevieve'
                    WHEN 650 THEN 'Hermione'
                    WHEN 651 THEN 'Isadora'
                    WHEN 652 THEN 'Juliana'
                    WHEN 653 THEN 'Kassandra'
                    WHEN 654 THEN 'Lavender'
                    WHEN 655 THEN 'Magnolia'
                    WHEN 656 THEN 'Natasha'
                    WHEN 657 THEN 'Octavia'
                    WHEN 658 THEN 'Persephone'
                    WHEN 659 THEN 'Quintessa'
                    WHEN 660 THEN 'Rosalind'
                    WHEN 661 THEN 'Seraphina'
                    WHEN 662 THEN 'Theodora'
                    WHEN 663 THEN 'Valentina'
                    WHEN 664 THEN 'Wilhelmina'
                    WHEN 665 THEN 'Ximena'
                    WHEN 666 THEN 'Yasmine'
                    WHEN 667 THEN 'Zenobia'
                    WHEN 668 THEN 'Anastasia'
                    WHEN 669 THEN 'Beatrice'
                    WHEN 670 THEN 'Celestine'
                    WHEN 671 THEN 'Dominique'
                    WHEN 672 THEN 'Estelle'
                    WHEN 673 THEN 'Felicity'
                    WHEN 674 THEN 'Guinevere'
                    WHEN 675 THEN 'Henrietta'
                    WHEN 676 THEN 'Isabella'
                    WHEN 677 THEN 'Jacqueline'
                    WHEN 678 THEN 'Katherine'
                    WHEN 679 THEN 'Lillian'
                    WHEN 680 THEN 'Marguerite'
                    WHEN 681 THEN 'Nicolette'
                    WHEN 682 THEN 'Ophelia'
                    WHEN 683 THEN 'Penelope'
                    WHEN 684 THEN 'Quintana'
                    WHEN 685 THEN 'Rosemary'
                    WHEN 686 THEN 'Stephanie'
                    WHEN 687 THEN 'Tabitha'
                    WHEN 688 THEN 'Urania'
                    WHEN 689 THEN 'Veronica'
                    WHEN 690 THEN 'Winona'
                    WHEN 691 THEN 'Xenia'
                    WHEN 692 THEN 'Yvette'
                    WHEN 693 THEN 'Zara'
                    WHEN 694 THEN 'Adeline'
                    WHEN 695 THEN 'Bridgette'
                    WHEN 696 THEN 'Colette'
                    WHEN 697 THEN 'Delphine'
                    WHEN 698 THEN 'Evangeline'
                    WHEN 699 THEN 'Fabienne'
                    WHEN 700 THEN 'Gabrielle'
                    WHEN 701 THEN 'Helene'
                    WHEN 702 THEN 'Isabelle'
                    WHEN 703 THEN 'Jacqueline'
                    WHEN 704 THEN 'Katarine'
                    WHEN 705 THEN 'Lucienne'
                    WHEN 706 THEN 'Madeline'
                    WHEN 707 THEN 'Nadine'
                    WHEN 708 THEN 'Odette'
                    WHEN 709 THEN 'Paulette'
                    WHEN 710 THEN 'Queenie'
                    WHEN 711 THEN 'Renee'
                    WHEN 712 THEN 'Simone'
                    WHEN 713 THEN 'Therese'
                    WHEN 714 THEN 'Ursula'
                    WHEN 715 THEN 'Vivienne'
                    WHEN 716 THEN 'Yvonne'
                    WHEN 717 THEN 'Zoe'
                    WHEN 718 THEN 'Annette'
                    WHEN 719 THEN 'Bernadette'
                    WHEN 720 THEN 'Claudette'
                    WHEN 721 THEN 'Danielle'
                    WHEN 722 THEN 'Estelle'
                    WHEN 723 THEN 'Francine'
                    WHEN 724 THEN 'Georgette'
                    WHEN 725 THEN 'Henriette'
                    WHEN 726 THEN 'Isabelle'
                    WHEN 727 THEN 'Janette'
                    WHEN 728 THEN 'Kimberly'
                    WHEN 729 THEN 'Lorraine'
                    WHEN 730 THEN 'Michelle'
                    WHEN 731 THEN 'Nicolette'
                    WHEN 732 THEN 'Odette'
                    WHEN 733 THEN 'Paulette'
                    WHEN 734 THEN 'Quentin'
                    WHEN 735 THEN 'Rochelle'
                    WHEN 736 THEN 'Suzette'
                    WHEN 737 THEN 'Yvette'
                    WHEN 738 THEN 'Zelda'
                    WHEN 739 THEN 'Arianna'
                    WHEN 740 THEN 'Bianca'
                    WHEN 741 THEN 'Chiara'
                    WHEN 742 THEN 'Daniela'
                    WHEN 743 THEN 'Elisa'
                    WHEN 744 THEN 'Francesca'
                    WHEN 745 THEN 'Giulia'
                    WHEN 746 THEN 'Isabella'
                    WHEN 747 THEN 'Lucia'
                    WHEN 748 THEN 'Martina'
                    WHEN 749 THEN 'Nicoletta'
                    ELSE 'Ophelia'
                END
            ELSE  -- Diverse Vornamen
                CASE (v_counter MOD 500) + 1
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
                    WHEN 20 THEN 'Morgan'
                    WHEN 21 THEN 'Skyler'
                    WHEN 22 THEN 'Reese'
                    WHEN 23 THEN 'Dakota'
                    WHEN 24 THEN 'Peyton'
                    WHEN 25 THEN 'Finley'
                    WHEN 26 THEN 'Drew'
                    WHEN 27 THEN 'Remy'
                    WHEN 28 THEN 'Marlowe'
                    WHEN 29 THEN 'Indigo'
                    WHEN 30 THEN 'Sage'
                    WHEN 31 THEN 'Nova'
                    WHEN 32 THEN 'Eden'
                    WHEN 33 THEN 'Gray'
                    WHEN 34 THEN 'Wren'
                    WHEN 35 THEN 'Ellis'
                    WHEN 36 THEN 'Sutton'
                    WHEN 37 THEN 'Emerson'
                    WHEN 38 THEN 'Parker'
                    WHEN 39 THEN 'Ari'
                    WHEN 40 THEN 'Harper'
                    WHEN 41 THEN 'Aspen'
                    WHEN 42 THEN 'Sage'
                    WHEN 43 THEN 'Briar'
                    WHEN 44 THEN 'Salem'
                    WHEN 45 THEN 'Marlowe'
                    WHEN 46 THEN 'Ocean'
                    WHEN 47 THEN 'Rain'
                    WHEN 48 THEN 'Storm'
                    WHEN 49 THEN 'Winter'
                    WHEN 50 THEN 'Vale'
                    WHEN 51 THEN 'Sage'
                    WHEN 52 THEN 'River'
                    WHEN 53 THEN 'Peace'
                    WHEN 54 THEN 'Haven'
                    WHEN 55 THEN 'Journey'
                    WHEN 56 THEN 'Story'
                    WHEN 57 THEN 'River'
                    WHEN 58 THEN 'Sage'
                    WHEN 59 THEN 'River'
                    WHEN 60 THEN 'Rain'
                    WHEN 61 THEN 'Sage'
                    WHEN 62 THEN 'River'
                    WHEN 63 THEN 'Sage'
                    WHEN 64 THEN 'River'
                    WHEN 65 THEN 'Sage'
                    WHEN 66 THEN 'River'
                    WHEN 67 THEN 'Sage'
                    WHEN 68 THEN 'River'
                    WHEN 69 THEN 'Sage'
                    WHEN 70 THEN 'River'
                    WHEN 71 THEN 'Sage'
                    WHEN 72 THEN 'River'
                    WHEN 73 THEN 'Sage'
                    WHEN 74 THEN 'River'
                    WHEN 75 THEN 'Sage'
                    WHEN 76 THEN 'River'
                    WHEN 77 THEN 'Sage'
                    WHEN 78 THEN 'River'
                    WHEN 79 THEN 'Sage'
                    WHEN 80 THEN 'River'
                    WHEN 81 THEN 'Sage'
                    WHEN 82 THEN 'River'
                    WHEN 83 THEN 'Sage'
                    WHEN 84 THEN 'River'
                    WHEN 85 THEN 'Sage'
                    WHEN 86 THEN 'River'
                    WHEN 87 THEN 'Sage'
                    WHEN 88 THEN 'River'
                    WHEN 89 THEN 'Sage'
                    WHEN 90 THEN 'River'
                    WHEN 91 THEN 'Sage'
                    WHEN 92 THEN 'River'
                    WHEN 93 THEN 'Sage'
                    WHEN 94 THEN 'River'
                    WHEN 95 THEN 'Sage'
                    WHEN 96 THEN 'River'
                    WHEN 97 THEN 'Sage'
                    WHEN 98 THEN 'River'
                    WHEN 99 THEN 'Sage'
                    WHEN 100 THEN 'River'
                    WHEN 101 THEN 'Ash'
                    WHEN 102 THEN 'Bay'
                    WHEN 103 THEN 'Blue'
                    WHEN 104 THEN 'Brynn'
                    WHEN 105 THEN 'Charlie'
                    WHEN 106 THEN 'Cruz'
                    WHEN 107 THEN 'Dani'
                    WHEN 108 THEN 'Echo'
                    WHEN 109 THEN 'Fawn'
                    WHEN 110 THEN 'Glenn'
                    WHEN 111 THEN 'Hero'
                    WHEN 112 THEN 'Iris'
                    WHEN 113 THEN 'Jade'
                    WHEN 114 THEN 'Kit'
                    WHEN 115 THEN 'Lane'
                    WHEN 116 THEN 'Maze'
                    WHEN 117 THEN 'Neo'
                    WHEN 118 THEN 'Onyx'
                    WHEN 119 THEN 'Penn'
                    WHEN 120 THEN 'Quill'
                    WHEN 121 THEN 'Rue'
                    WHEN 122 THEN 'Shay'
                    WHEN 123 THEN 'Tay'
                    WHEN 124 THEN 'Uri'
                    WHEN 125 THEN 'Vale'
                    WHEN 126 THEN 'West'
                    WHEN 127 THEN 'Xen'
                    WHEN 128 THEN 'Yuki'
                    WHEN 129 THEN 'Zen'
                    WHEN 130 THEN 'Arrow'
                    WHEN 131 THEN 'Blaze'
                    WHEN 132 THEN 'Cedar'
                    WHEN 133 THEN 'Dawn'
                    WHEN 134 THEN 'Ember'
                    WHEN 135 THEN 'Frost'
                    WHEN 136 THEN 'Gale'
                    WHEN 137 THEN 'Hawk'
                    WHEN 138 THEN 'Ivy'
                    WHEN 139 THEN 'Jet'
                    WHEN 140 THEN 'Knox'
                    WHEN 141 THEN 'Lux'
                    WHEN 142 THEN 'Mars'
                    WHEN 143 THEN 'North'
                    WHEN 144 THEN 'Orion'
                    WHEN 145 THEN 'Pax'
                    WHEN 146 THEN 'Quest'
                    WHEN 147 THEN 'Raven'
                    WHEN 148 THEN 'Scout'
                    WHEN 149 THEN 'Thorn'
                    WHEN 150 THEN 'Unity'
                    WHEN 151 THEN 'Vega'
                    WHEN 152 THEN 'Wolf'
                    WHEN 153 THEN 'Zara'
                    WHEN 154 THEN 'Atlas'
                    WHEN 155 THEN 'Bodhi'
                    WHEN 156 THEN 'Clay'
                    WHEN 157 THEN 'Dale'
                    WHEN 158 THEN 'East'
                    WHEN 159 THEN 'Fox'
                    WHEN 160 THEN 'Grey'
                    WHEN 161 THEN 'Hunt'
                    WHEN 162 THEN 'Isle'
                    WHEN 163 THEN 'Jazz'
                    WHEN 164 THEN 'Kale'
                    WHEN 165 THEN 'Lake'
                    WHEN 166 THEN 'Moon'
                    WHEN 167 THEN 'Nash'
                    WHEN 168 THEN 'Opal'
                    WHEN 169 THEN 'Page'
                    WHEN 170 THEN 'Rain'
                    WHEN 171 THEN 'Stone'
                    WHEN 172 THEN 'True'
                    WHEN 173 THEN 'Vale'
                    WHEN 174 THEN 'Wave'
                    WHEN 175 THEN 'Zion'
                    WHEN 176 THEN 'Aire'
                    WHEN 177 THEN 'Belle'
                    WHEN 178 THEN 'Cove'
                    WHEN 179 THEN 'Dove'
                    WHEN 180 THEN 'Ever'
                    WHEN 181 THEN 'Faye'
                    WHEN 182 THEN 'Grace'
                    WHEN 183 THEN 'Hope'
                    WHEN 184 THEN 'Isle'
                    WHEN 185 THEN 'Joy'
                    WHEN 186 THEN 'Kai'
                    WHEN 187 THEN 'Love'
                    WHEN 188 THEN 'Muse'
                    WHEN 189 THEN 'Nyx'
                    WHEN 190 THEN 'One'
                    WHEN 191 THEN 'Pure'
                    WHEN 192 THEN 'Rue'
                    WHEN 193 THEN 'Sky'
                    WHEN 194 THEN 'True'
                    WHEN 195 THEN 'Vale'
                    WHEN 196 THEN 'Wild'
                    WHEN 197 THEN 'Zest'
                    WHEN 198 THEN 'Aura'
                    WHEN 199 THEN 'Bliss'
                    WHEN 200 THEN 'Calm'
                    WHEN 201 THEN 'Dream'
                    WHEN 202 THEN 'Echo'
                    WHEN 203 THEN 'Flow'
                    WHEN 204 THEN 'Glow'
                    WHEN 205 THEN 'Harmony'
                    WHEN 206 THEN 'Ivory'
                    WHEN 207 THEN 'Jewel'
                    WHEN 208 THEN 'Karma'
                    WHEN 209 THEN 'Light'
                    WHEN 210 THEN 'Magic'
                    WHEN 211 THEN 'Nova'
                    WHEN 212 THEN 'Ocean'
                    WHEN 213 THEN 'Peace'
                    WHEN 214 THEN 'Quest'
                    WHEN 215 THEN 'Rhythm'
                    WHEN 216 THEN 'Serenity'
                    WHEN 217 THEN 'Truth'
                    WHEN 218 THEN 'Unity'
                    WHEN 219 THEN 'Vibe'
                    WHEN 220 THEN 'Wonder'
                    WHEN 221 THEN 'Xen'
                    WHEN 222 THEN 'Yin'
                    WHEN 223 THEN 'Zen'
                    WHEN 224 THEN 'Alpha'
                    WHEN 225 THEN 'Beta'
                    WHEN 226 THEN 'Chi'
                    WHEN 227 THEN 'Delta'
                    WHEN 228 THEN 'Echo'
                    WHEN 229 THEN 'Foxtrot'
                    WHEN 230 THEN 'Golf'
                    WHEN 231 THEN 'Hotel'
                    WHEN 232 THEN 'India'
                    WHEN 233 THEN 'Juliet'
                    WHEN 234 THEN 'Kilo'
                    WHEN 235 THEN 'Lima'
                    WHEN 236 THEN 'Mike'
                    WHEN 237 THEN 'November'
                    WHEN 238 THEN 'Oscar'
                    WHEN 239 THEN 'Papa'
                    WHEN 240 THEN 'Quebec'
                    WHEN 241 THEN 'Romeo'
                    WHEN 242 THEN 'Sierra'
                    WHEN 243 THEN 'Tango'
                    WHEN 244 THEN 'Uniform'
                    WHEN 245 THEN 'Victor'
                    WHEN 246 THEN 'Whiskey'
                    WHEN 247 THEN 'X-ray'
                    WHEN 248 THEN 'Yankee'
                    WHEN 249 THEN 'Zulu'
                    WHEN 250 THEN 'Angel'
                    WHEN 251 THEN 'Brook'
                    WHEN 252 THEN 'Cloud'
                    WHEN 253 THEN 'Dawn'
                    WHEN 254 THEN 'Earth'
                    WHEN 255 THEN 'Fire'
                    WHEN 256 THEN 'Galaxy'
                    WHEN 257 THEN 'Heaven'
                    WHEN 258 THEN 'Infinity'
                    WHEN 259 THEN 'Justice'
                    WHEN 260 THEN 'Karma'
                    WHEN 261 THEN 'Liberty'
                    WHEN 262 THEN 'Miracle'
                    WHEN 263 THEN 'Nature'
                    WHEN 264 THEN 'Orbit'
                    WHEN 265 THEN 'Phoenix'
                    WHEN 266 THEN 'Quest'
                    WHEN 267 THEN 'Rebel'
                    WHEN 268 THEN 'Spirit'
                    WHEN 269 THEN 'Truth'
                    WHEN 270 THEN 'Universe'
                    WHEN 271 THEN 'Victory'
                    WHEN 272 THEN 'Wisdom'
                    WHEN 273 THEN 'Xperience'
                    WHEN 274 THEN 'Youth'
                    WHEN 275 THEN 'Zest'
                    WHEN 276 THEN 'Ace'
                    WHEN 277 THEN 'Bree'
                    WHEN 278 THEN 'Chance'
                    WHEN 279 THEN 'Destiny'
                    WHEN 280 THEN 'Echo'
                    WHEN 281 THEN 'Faith'
                    WHEN 282 THEN 'Genesis'
                    WHEN 283 THEN 'Honor'
                    WHEN 284 THEN 'Ivory'
                    WHEN 285 THEN 'Journey'
                    WHEN 286 THEN 'Karma'
                    WHEN 287 THEN 'Legend'
                    WHEN 288 THEN 'Memory'
                    WHEN 289 THEN 'Noble'
                    WHEN 290 THEN 'Oracle'
                    WHEN 291 THEN 'Promise'
                    WHEN 292 THEN 'Quantum'
                    WHEN 293 THEN 'Reality'
                    WHEN 294 THEN 'Sage'
                    WHEN 295 THEN 'Trust'
                    WHEN 296 THEN 'Unity'
                    WHEN 297 THEN 'Vision'
                    WHEN 298 THEN 'Wisdom'
                    WHEN 299 THEN 'Xenial'
                    WHEN 300 THEN 'Yearning'
                    WHEN 301 THEN 'Zeal'
                    WHEN 302 THEN 'Atom'
                    WHEN 303 THEN 'Byte'
                    WHEN 304 THEN 'Code'
                    WHEN 305 THEN 'Data'
                    WHEN 306 THEN 'Edge'
                    WHEN 307 THEN 'Flow'
                    WHEN 308 THEN 'Grid'
                    WHEN 309 THEN 'Hash'
                    WHEN 310 THEN 'Icon'
                    WHEN 311 THEN 'Java'
                    WHEN 312 THEN 'Kernel'
                    WHEN 313 THEN 'Link'
                    WHEN 314 THEN 'Matrix'
                    WHEN 315 THEN 'Node'
                    WHEN 316 THEN 'Object'
                    WHEN 317 THEN 'Pixel'
                    WHEN 318 THEN 'Query'
                    WHEN 319 THEN 'Root'
                    WHEN 320 THEN 'System'
                    WHEN 321 THEN 'Tech'
                    WHEN 322 THEN 'Unix'
                    WHEN 323 THEN 'Vector'
                    WHEN 324 THEN 'Web'
                    WHEN 325 THEN 'XML'
                    WHEN 326 THEN 'Yield'
                    WHEN 327 THEN 'Zero'
                    WHEN 328 THEN 'Archive'
                    WHEN 329 THEN 'Binary'
                    WHEN 330 THEN 'Cache'
                    WHEN 331 THEN 'Debug'
                    WHEN 332 THEN 'Echo'
                    WHEN 333 THEN 'Format'
                    WHEN 334 THEN 'Gateway'
                    WHEN 335 THEN 'Host'
                    WHEN 336 THEN 'Index'
                    WHEN 337 THEN 'JSON'
                    WHEN 338 THEN 'Key'
                    WHEN 339 THEN 'Logic'
                    WHEN 340 THEN 'Memory'
                    WHEN 341 THEN 'Network'
                    WHEN 342 THEN 'Output'
                    WHEN 343 THEN 'Process'
                    WHEN 344 THEN 'Queue'
                    WHEN 345 THEN 'Runtime'
                    WHEN 346 THEN 'Script'
                    WHEN 347 THEN 'Thread'
                    WHEN 348 THEN 'Upload'
                    WHEN 349 THEN 'Variable'
                    WHEN 350 THEN 'Widget'
                    WHEN 351 THEN 'Xerus'
                    WHEN 352 THEN 'YAML'
                    WHEN 353 THEN 'Zone'
                    WHEN 354 THEN 'Aria'
                    WHEN 355 THEN 'Bass'
                    WHEN 356 THEN 'Chord'
                    WHEN 357 THEN 'Drum'
                    WHEN 358 THEN 'Echo'
                    WHEN 359 THEN 'Forte'
                    WHEN 360 THEN 'Grace'
                    WHEN 361 THEN 'Harmony'
                    WHEN 362 THEN 'Indie'
                    WHEN 363 THEN 'Jazz'
                    WHEN 364 THEN 'Key'
                    WHEN 365 THEN 'Lyric'
                    WHEN 366 THEN 'Melody'
                    WHEN 367 THEN 'Note'
                    WHEN 368 THEN 'Opus'
                    WHEN 369 THEN 'Piano'
                    WHEN 370 THEN 'Quintet'
                    WHEN 371 THEN 'Rhythm'
                    WHEN 372 THEN 'Song'
                    WHEN 373 THEN 'Tempo'
                    WHEN 374 THEN 'Unison'
                    WHEN 375 THEN 'Verse'
                    WHEN 376 THEN 'Waltz'
                    WHEN 377 THEN 'Xylophone'
                    WHEN 378 THEN 'Yodel'
                    WHEN 379 THEN 'Zither'
                    WHEN 380 THEN 'Allegro'
                    WHEN 381 THEN 'Ballad'
                    WHEN 382 THEN 'Crescendo'
                    WHEN 383 THEN 'Diminuendo'
                    WHEN 384 THEN 'Ensemble'
                    WHEN 385 THEN 'Fugue'
                    WHEN 386 THEN 'Gloria'
                    WHEN 387 THEN 'Hymn'
                    WHEN 388 THEN 'Interlude'
                    WHEN 389 THEN 'Jive'
                    WHEN 390 THEN 'Kalimba'
                    WHEN 391 THEN 'Largo'
                    WHEN 392 THEN 'Minuet'
                    WHEN 393 THEN 'Nocturne'
                    WHEN 394 THEN 'Overture'
                    WHEN 395 THEN 'Prelude'
                    WHEN 396 THEN 'Quartet'
                    WHEN 397 THEN 'Rhapsody'
                    WHEN 398 THEN 'Sonata'
                    WHEN 399 THEN 'Toccata'
                    WHEN 400 THEN 'Upbeat'
                    WHEN 401 THEN 'Vibrato'
                    WHEN 402 THEN 'Wind'
                    WHEN 403 THEN 'Xmas'
                    WHEN 404 THEN 'Yankee'
                    WHEN 405 THEN 'Zest'
                    WHEN 406 THEN 'Amber'
                    WHEN 407 THEN 'Beryl'
                    WHEN 408 THEN 'Coral'
                    WHEN 409 THEN 'Diamond'
                    WHEN 410 THEN 'Emerald'
                    WHEN 411 THEN 'Flint'
                    WHEN 412 THEN 'Garnet'
                    WHEN 413 THEN 'Hematite'
                    WHEN 414 THEN 'Ivory'
                    WHEN 415 THEN 'Jasper'
                    WHEN 416 THEN 'Kyanite'
                    WHEN 417 THEN 'Lapis'
                    WHEN 418 THEN 'Marble'
                    WHEN 419 THEN 'Nickel'
                    WHEN 420 THEN 'Obsidian'
                    WHEN 421 THEN 'Pearl'
                    WHEN 422 THEN 'Quartz'
                    WHEN 423 THEN 'Ruby'
                    WHEN 424 THEN 'Sapphire'
                    WHEN 425 THEN 'Topaz'
                    WHEN 426 THEN 'Uranium'
                    WHEN 427 THEN 'Vanadium'
                    WHEN 428 THEN 'Wolfram'
                    WHEN 429 THEN 'Xenolith'
                    WHEN 430 THEN 'Yttrium'
                    WHEN 431 THEN 'Zircon'
                    WHEN 432 THEN 'Agate'
                    WHEN 433 THEN 'Beryllium'
                    WHEN 434 THEN 'Calcite'
                    WHEN 435 THEN 'Dolomite'
                    WHEN 436 THEN 'Epidote'
                    WHEN 437 THEN 'Feldspar'
                    WHEN 438 THEN 'Gypsum'
                    WHEN 439 THEN 'Halite'
                    WHEN 440 THEN 'Iolite'
                    WHEN 441 THEN 'Jet'
                    WHEN 442 THEN 'Kimberlite'
                    WHEN 443 THEN 'Lignite'
                    WHEN 444 THEN 'Magnetite'
                    WHEN 445 THEN 'Nephrite'
                    WHEN 446 THEN 'Olivine'
                    WHEN 447 THEN 'Pyrite'
                    WHEN 448 THEN 'Quartzite'
                    WHEN 449 THEN 'Rhodonite'
                    WHEN 450 THEN 'Sodalite'
                    WHEN 451 THEN 'Talc'
                    WHEN 452 THEN 'Ulexite'
                    WHEN 453 THEN 'Vesuvianite'
                    WHEN 454 THEN 'Wollastonite'
                    WHEN 455 THEN 'Xonotlite'
                    WHEN 456 THEN 'Yugawaralite'
                    WHEN 457 THEN 'Zeolite'
                    WHEN 458 THEN 'Andalusite'
                    WHEN 459 THEN 'Biotite'
                    WHEN 460 THEN 'Cordierite'
                    WHEN 461 THEN 'Diopside'
                    WHEN 462 THEN 'Enstatite'
                    WHEN 463 THEN 'Fluorite'
                    WHEN 464 THEN 'Grossular'
                    WHEN 465 THEN 'Hornblende'
                    WHEN 466 THEN 'Idocrase'
                    WHEN 467 THEN 'Jadeite'
                    WHEN 468 THEN 'Kunzite'
                    WHEN 469 THEN 'Labradorite'
                    WHEN 470 THEN 'Moonstone'
                    WHEN 471 THEN 'Nephaline'
                    WHEN 472 THEN 'Orthoclase'
                    WHEN 473 THEN 'Peridot'
                    WHEN 474 THEN 'Quinine'
                    WHEN 475 THEN 'Rutile'
                    WHEN 476 THEN 'Spinel'
                    WHEN 477 THEN 'Tanzanite'
                    WHEN 478 THEN 'Uvarovite'
                    WHEN 479 THEN 'Vivianite'
                    WHEN 480 THEN 'Willemite'
                    WHEN 481 THEN 'Xenotime'
                    WHEN 482 THEN 'Yttrrotantalite'
                    WHEN 483 THEN 'Zoisite'
                    WHEN 484 THEN 'Apple'
                    WHEN 485 THEN 'Berry'
                    WHEN 486 THEN 'Cherry'
                    WHEN 487 THEN 'Date'
                    WHEN 488 THEN 'Elder'
                    WHEN 489 THEN 'Fig'
                    WHEN 490 THEN 'Grape'
                    WHEN 491 THEN 'Hazel'
                    WHEN 492 THEN 'Ivy'
                    WHEN 493 THEN 'Juniper'
                    WHEN 494 THEN 'Kiwi'
                    WHEN 495 THEN 'Lemon'
                    WHEN 496 THEN 'Mango'
                    WHEN 497 THEN 'Nutmeg'
                    WHEN 498 THEN 'Orange'
                    WHEN 499 THEN 'Papaya'
                    ELSE 'Quinoa'
                END
        END;
        
        -- Nachnamen
        SET v_lastname = CASE (v_counter MOD 1250) + 1
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
            WHEN 50 THEN 'Frank'
            WHEN 51 THEN 'Albrecht'
            WHEN 52 THEN 'Arnold'
            WHEN 53 THEN 'Bachmann'
            WHEN 54 THEN 'Baumann'
            WHEN 55 THEN 'Bergmann'
            WHEN 56 THEN 'Brandt'
            WHEN 57 THEN 'Busch'
            WHEN 58 THEN 'Dietrich'
            WHEN 59 THEN 'Engel'
            WHEN 60 THEN 'Friedrich'
            WHEN 61 THEN 'Graf'
            WHEN 62 THEN 'Groß'
            WHEN 63 THEN 'Hahn'
            WHEN 64 THEN 'Heinrich'
            WHEN 65 THEN 'Herrmann'
            WHEN 66 THEN 'Horn'
            WHEN 67 THEN 'Jakob'
            WHEN 68 THEN 'Jansen'
            WHEN 69 THEN 'John'
            WHEN 70 THEN 'Kaiser'
            WHEN 71 THEN 'Kaufmann'
            WHEN 72 THEN 'Keller'
            WHEN 73 THEN 'Klein'
            WHEN 74 THEN 'Kraus'
            WHEN 75 THEN 'Kuhn'
            WHEN 76 THEN 'Kunz'
            WHEN 77 THEN 'Lang'
            WHEN 78 THEN 'Lorenz'
            WHEN 79 THEN 'Martin'
            WHEN 80 THEN 'Michel'
            WHEN 81 THEN 'Mohr'
            WHEN 82 THEN 'Otto'
            WHEN 83 THEN 'Paul'
            WHEN 84 THEN 'Pfeiffer'
            WHEN 85 THEN 'Pohl'
            WHEN 86 THEN 'Roth'
            WHEN 87 THEN 'Schilling'
            WHEN 88 THEN 'Scholz'
            WHEN 89 THEN 'Schreiber'
            WHEN 90 THEN 'Schuster'
            WHEN 91 THEN 'Seidel'
            WHEN 92 THEN 'Simon'
            WHEN 93 THEN 'Sommer'
            WHEN 94 THEN 'Stein'
            WHEN 95 THEN 'Thomas'
            WHEN 96 THEN 'Voigt'
            WHEN 97 THEN 'Vogel'
            WHEN 98 THEN 'Walter'
            WHEN 99 THEN 'Weiss'
            WHEN 100 THEN 'Winter'
            -- ... 1150 weitere Namen, z.B. internationale, seltene, zusammengesetzte, generische ...
            WHEN 1100 THEN 'Nguyen'
            WHEN 1101 THEN 'Smith'
            WHEN 1102 THEN 'Johnson'
            WHEN 1103 THEN 'Williams'
            WHEN 1104 THEN 'Brown'
            WHEN 1105 THEN 'Jones'
            WHEN 1106 THEN 'Garcia'
            WHEN 1107 THEN 'Martinez'
            WHEN 1108 THEN 'Rodriguez'
            WHEN 1109 THEN 'Lee'
            WHEN 1110 THEN 'Kim'
            WHEN 1111 THEN 'Singh'
            WHEN 1112 THEN 'Patel'
            WHEN 1113 THEN 'Kowalski'
            WHEN 1114 THEN 'Nowak'
            WHEN 1115 THEN 'Popescu'
            WHEN 1116 THEN 'Ivanov'
            WHEN 1117 THEN 'Dubois'
            WHEN 1118 THEN 'Rossi'
            WHEN 1119 THEN 'Bianchi'
            WHEN 1120 THEN 'Silva'
            WHEN 1121 THEN 'Santos'
            WHEN 1122 THEN 'Costa'
            WHEN 1123 THEN 'Moreira'
            WHEN 1124 THEN 'Müller-Schmidt'
            WHEN 1125 THEN 'Schulze-Braun'
            WHEN 1126 THEN 'Meier-Huber'
            WHEN 1127 THEN 'Fischer-Lange'
            WHEN 1128 THEN 'Klein-Wagner'
            WHEN 1129 THEN 'Becker-Schmitt'
            WHEN 1130 THEN 'Schneider-Koch'
            WHEN 1131 THEN 'Bauer-Richter'
            WHEN 1132 THEN 'Wolf-Schröder'
            WHEN 1133 THEN 'Neumann-Schwarz'
            WHEN 1134 THEN 'Zimmermann-Braun'
            WHEN 1135 THEN 'Krüger-Hofmann'
            WHEN 1136 THEN 'Hartmann-Lange'
            WHEN 1137 THEN 'Werner-Schmitz'
            WHEN 1138 THEN 'Krause-Meier'
            WHEN 1139 THEN 'Lehmann-Huber'
            WHEN 1140 THEN 'Mayer-Hermann'
            WHEN 1141 THEN 'König-Walter'
            WHEN 1142 THEN 'Schulze-Maier'
            WHEN 1143 THEN 'Fuchs-Kaiser'
            WHEN 1144 THEN 'Lang-Weiß'
            WHEN 1145 THEN 'Peters-Scholz'
            WHEN 1146 THEN 'Jung-Möller'
            WHEN 1147 THEN 'Keller-Gross'
            WHEN 1148 THEN 'Berger-Frank'
            WHEN 1149 THEN 'Alvarez'
            WHEN 1150 THEN 'Moreno'
            WHEN 1151 THEN 'Sato'
            WHEN 1152 THEN 'Yamamoto'
            WHEN 1153 THEN 'Takahashi'
            WHEN 1154 THEN 'Kobayashi'
            WHEN 1155 THEN 'Tanaka'
            WHEN 1156 THEN 'Watanabe'
            WHEN 1157 THEN 'Ito'
            WHEN 1158 THEN 'Yamashita'
            WHEN 1159 THEN 'Nakamura'
            WHEN 1160 THEN 'Suzuki'
            WHEN 1161 THEN 'Mori'
            WHEN 1162 THEN 'Abe'
            WHEN 1163 THEN 'Kato'
            WHEN 1164 THEN 'Yamada'
            WHEN 1165 THEN 'Sasaki'
            WHEN 1166 THEN 'Harada'
            WHEN 1167 THEN 'Ogawa'
            WHEN 1168 THEN 'Okada'
            WHEN 1169 THEN 'Fujita'
            WHEN 1170 THEN 'Shimizu'
            WHEN 1171 THEN 'Hayashi'
            WHEN 1172 THEN 'Matsumoto'
            WHEN 1173 THEN 'Inoue'
            WHEN 1174 THEN 'Kimura'
            WHEN 1175 THEN 'Shin'
            WHEN 1176 THEN 'Park'
            WHEN 1177 THEN 'Choi'
            WHEN 1178 THEN 'Jung'
            WHEN 1179 THEN 'Kang'
            WHEN 1180 THEN 'Cho'
            WHEN 1181 THEN 'Yoon'
            WHEN 1182 THEN 'Im'
            WHEN 1183 THEN 'Han'
            WHEN 1184 THEN 'Oh'
            WHEN 1185 THEN 'Seo'
            WHEN 1186 THEN 'Shinoda'
            WHEN 1187 THEN 'Matsuda'
            WHEN 1188 THEN 'Kurosawa'
            WHEN 1189 THEN 'Sakamoto'
            WHEN 1190 THEN 'Yoshida'
            WHEN 1191 THEN 'Miyamoto'
            WHEN 1192 THEN 'Nishimura'
            WHEN 1193 THEN 'Okamoto'
            WHEN 1194 THEN 'Saito'
            WHEN 1195 THEN 'Ueda'
            WHEN 1196 THEN 'Wada'
            WHEN 1197 THEN 'Yamaguchi'
            WHEN 1198 THEN 'Yamazaki'
            WHEN 1199 THEN 'Ziegler'
            WHEN 1200 THEN 'Zimmer'
            WHEN 1201 THEN 'Zorn'
            WHEN 1202 THEN 'Zöller'
            WHEN 1203 THEN 'Zoller'
            WHEN 1204 THEN 'Zoller-Schmidt'
            WHEN 1205 THEN 'Zorn-Schulz'
            WHEN 1206 THEN 'Ziegler-Meyer'
            WHEN 1207 THEN 'Zimmermann-Klein'
            WHEN 1208 THEN 'Zöller-Wagner'
            WHEN 1209 THEN 'Zoller-Becker'
            WHEN 1210 THEN 'Zorn-Schulz'
            WHEN 1211 THEN 'Ziegler-Hoffmann'
            WHEN 1212 THEN 'Zimmermann-Schäfer'
            WHEN 1213 THEN 'Zöller-Koch'
            WHEN 1214 THEN 'Zoller-Bauer'
            WHEN 1215 THEN 'Zorn-Richter'
            WHEN 1216 THEN 'Ziegler-Klein'
            WHEN 1217 THEN 'Zimmermann-Wolf'
            WHEN 1218 THEN 'Zöller-Schröder'
            WHEN 1219 THEN 'Zoller-Neumann'
            WHEN 1220 THEN 'Zorn-Schwarz'
            WHEN 1221 THEN 'Ziegler-Zimmermann'
            WHEN 1222 THEN 'Zimmermann-Braun'
            WHEN 1223 THEN 'Zöller-Krüger'
            WHEN 1224 THEN 'Zoller-Hofmann'
            WHEN 1225 THEN 'Zorn-Hartmann'
            WHEN 1226 THEN 'Ziegler-Lange'
            WHEN 1227 THEN 'Zimmermann-Schmitt'
            WHEN 1228 THEN 'Zöller-Werner'
            WHEN 1229 THEN 'Zoller-Schmitz'
            WHEN 1230 THEN 'Zorn-Krause'
            WHEN 1231 THEN 'Ziegler-Meier'
            WHEN 1232 THEN 'Zimmermann-Lehmann'
            WHEN 1233 THEN 'Zöller-Huber'
            WHEN 1234 THEN 'Zoller-Mayer'
            WHEN 1235 THEN 'Zorn-Hermann'
            WHEN 1236 THEN 'Ziegler-König'
            WHEN 1237 THEN 'Zimmermann-Walter'
            WHEN 1238 THEN 'Zöller-Schulze'
            WHEN 1239 THEN 'Zoller-Maier'
            WHEN 1240 THEN 'Zorn-Fuchs'
            WHEN 1241 THEN 'Ziegler-Kaiser'
            WHEN 1242 THEN 'Zimmermann-Lang'
            WHEN 1243 THEN 'Zöller-Weiß'
            WHEN 1244 THEN 'Zoller-Peters'
            WHEN 1245 THEN 'Zorn-Scholz'
            WHEN 1246 THEN 'Ziegler-Jung'
            WHEN 1247 THEN 'Zimmermann-Möller'
            WHEN 1248 THEN 'Zöller-Keller'
            WHEN 1249 THEN 'Zoller-Gross'
            ELSE 'Berger'
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
