<?php
require_once(__DIR__."/../config/db_connect.php");

$cnt1 = $db->query("INSERT IGNORE INTO b_meldeliste
                (MNr, BSG, Zuordnung, Zuordnung_ID, Betrag, Beitragsjahr)
            SELECT
                m.id                     AS MNr,
                m.BSG                    AS BSG,
                1                        AS Zuordnung,
                b.Verband                AS Zuordnung_ID,
                r.Basisbeitrag           AS Betrag,
                YEAR(CURDATE())          AS Beitragsjahr
            FROM b_mitglieder            AS m
            JOIN b_bsg                   AS b ON b.id = m.BSG
            JOIN b_regionalverband       AS r ON r.id = b.Verband
            WHERE m.BSG IS NOT NULL;
            ",array(),0);

$cnt2 = $db->query("INSERT IGNORE INTO b_meldeliste
                (MNr, BSG, Zuordnung, Zuordnung_ID, Betrag, Beitragsjahr)
            SELECT 
                mis.Mitglied             AS MNr,
                mis.BSG                  AS BSG,
                2                        AS Zuordnung,
                mis.Sparte               AS Zuordnung_ID,
                s.Spartenbeitrag         AS Betrag,
                YEAR(CURDATE())          AS Beitragsjahr
            FROM b_mitglieder_in_sparten AS mis
            JOIN b_sparte                AS s ON s.id = mis.Sparte;
            ",array(),0);
?>



