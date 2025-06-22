<?php
$meldedatum = date('Y') . '-02-15';
$heute = date('Y-m-d');
if ($heute < $meldedatum) {
    return;
}
require_once(__DIR__."/../config/db_connect.php");

// Stamm-BSG (Basisbeitrag)
$cnt1 = $db->query("INSERT IGNORE INTO b_meldeliste
                (Mitglied, BSG, Zuordnung, Zuordnung_ID, Betrag, Beitragsjahr, Beitragsstelle)
            SELECT
                CONCAT(m.id, ': ', m.Vorname, ', ', m.Nachname, ', geb. ', DATE_FORMAT(m.Geburtsdatum, '%d.%m.%y')) AS Mitglied,
                CONCAT(
                    COALESCE(b.VKZ, '---'), ': ',
                    COALESCE(b.BSG, '---'), ' (',
                    COALESCE(r.Kurzname, '---'), ')'
                ) AS BSG,
                1                        AS Zuordnung,
                b.Verband                AS Zuordnung_ID,
                r.Basisbeitrag           AS Betrag,
                YEAR(CURDATE())          AS Beitragsjahr,
                b.Verband                AS Beitragsstelle
            FROM b_mitglieder            AS m
            JOIN b_bsg                   AS b ON b.id = m.BSG
            JOIN b_regionalverband       AS r ON r.id = b.Verband
            WHERE m.BSG IS NOT NULL;
            ",array(),0);

// Sparten
$cnt2 = $db->query("INSERT IGNORE INTO b_meldeliste
                (Mitglied, BSG, Zuordnung, Zuordnung_ID, Betrag, Beitragsjahr, Beitragsstelle)
            SELECT 
                CONCAT(m.id, ': ', m.Vorname, ', ', m.Nachname, ', geb. ', DATE_FORMAT(m.Geburtsdatum, '%d.%m.%y')) AS Mitglied,
                CONCAT(
                    COALESCE(b.VKZ, '---'), ': ',
                    COALESCE(b.BSG, '---'), ' (',
                    COALESCE(r.Kurzname, '---'), ')'
                ) AS BSG,
                2                        AS Zuordnung,
                mis.Sparte               AS Zuordnung_ID,
                s.Spartenbeitrag         AS Betrag,
                YEAR(CURDATE())          AS Beitragsjahr,
                s.Verband                AS Beitragsstelle
            FROM b_mitglieder_in_sparten AS mis
            JOIN b_mitglieder            AS m ON m.id = mis.Mitglied
            JOIN b_bsg                   AS b ON b.id = mis.BSG
            JOIN b_regionalverband       AS r ON r.id = b.Verband
            JOIN b_sparte                AS s ON s.id = mis.Sparte;
            ",array(),0);




// ALT: Beitragsstelle (Empfänger) = Verband der BSG. Das muss aber Verband der Sparte sein, weil dort muss das Geld ja hin!
/*
$cnt2 = $db->query("INSERT IGNORE INTO b_meldeliste
                (Mitglied, BSG, Zuordnung, Zuordnung_ID, Betrag, Beitragsjahr, Beitragsstelle)
            SELECT 
                CONCAT(m.id, ': ', m.Vorname, ', ', m.Nachname, ', geb. ', DATE_FORMAT(m.Geburtsdatum, '%d.%m.%y')) AS Mitglied,
                CONCAT(
                    COALESCE(b.VKZ, '---'), ': ',
                    COALESCE(b.BSG, '---'), ' (',
                    COALESCE(r.Kurzname, '---'), ')'
                ) AS BSG,
                2                        AS Zuordnung,
                mis.Sparte               AS Zuordnung_ID,
                s.Spartenbeitrag         AS Betrag,
                YEAR(CURDATE())          AS Beitragsjahr,
                b.Verband                AS Beitragsstelle
            FROM b_mitglieder_in_sparten AS mis
            JOIN b_mitglieder            AS m ON m.id = mis.Mitglied
            JOIN b_bsg                   AS b ON b.id = mis.BSG
            JOIN b_regionalverband       AS r ON r.id = b.Verband
            JOIN b_sparte                AS s ON s.id = mis.Sparte;
            ",array(),0);
    */
?>
