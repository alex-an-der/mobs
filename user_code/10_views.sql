DROP VIEW IF EXISTS `y_v_userfields`;
CREATE VIEW `y_v_userfields` AS
select
  `f`.`ID` AS `fieldID`,
  `d`.`userID` AS `userID`,
  `f`.`uf_name` AS `uf_name`,
  `f`.`fieldname` AS `fieldname`,
  `d`.`fieldvalue` AS `fieldvalue`
from
  (
    `y_user_details` `d`
    left join `y_user_fields` `f` on((`d`.`fieldID` = `f`.`ID`))
  );

-- ----------------------------------------------------------------------------------------

DROP VIEW IF EXISTS `y_v_userdata`;
CREATE VIEW `y_v_userdata` AS
select
  distinct `t_uid`.`userID` AS `userID`,
  `y_user`.`mail` AS `mail`,
  `t_bsg`.`fieldvalue` AS `bsg`,
  `t_gebdatum`.`fieldvalue` AS `gebdatum`,
  `t_geschlecht`.`fieldvalue` AS `geschlecht`,
  `t_nname`.`fieldvalue` AS `nname`,
  `t_okformail`.`fieldvalue` AS `okformail`,
  `t_vname`.`fieldvalue` AS `vname`
from
  (
    (
      (
        (
          (
            (
              (
                `y_v_userfields` `t_uid`
                join `y_user` on(
                  (
                    `t_uid`.`userID` = `y_user`.`id`
                  )
                )
              )
              left join (
                select
                  `y_v_userfields`.`userID` AS `userID`,
                  `y_v_userfields`.`fieldvalue` AS `fieldvalue`
                from
                  `y_v_userfields`
                where
                  (
                    `y_v_userfields`.`fieldname` = 'bsg'
                  )
              ) `t_bsg` on(
                (
                  `t_uid`.`userID` = `t_bsg`.`userID`
                )
              )
            )
            left join (
              select
                `y_v_userfields`.`userID` AS `userID`,
                `y_v_userfields`.`fieldvalue` AS `fieldvalue`
              from
                `y_v_userfields`
              where
                (
                  `y_v_userfields`.`fieldname` = 'gebdatum'
                )
            ) `t_gebdatum` on(
              (
                `t_uid`.`userID` = `t_gebdatum`.`userID`
              )
            )
          )
          left join (
            select
              `y_v_userfields`.`userID` AS `userID`,
              `y_v_userfields`.`fieldvalue` AS `fieldvalue`
            from
              `y_v_userfields`
            where
              (
                `y_v_userfields`.`fieldname` = 'geschlecht'
              )
          ) `t_geschlecht` on(
            (
              `t_uid`.`userID` = `t_geschlecht`.`userID`
            )
          )
        )
        left join (
          select
            `y_v_userfields`.`userID` AS `userID`,
            `y_v_userfields`.`fieldvalue` AS `fieldvalue`
          from
            `y_v_userfields`
          where
            (
              `y_v_userfields`.`fieldname` = 'nname'
            )
        ) `t_nname` on(
          (
            `t_uid`.`userID` = `t_nname`.`userID`
          )
        )
      )
      left join (
        select
          `y_v_userfields`.`userID` AS `userID`,
          `y_v_userfields`.`fieldvalue` AS `fieldvalue`
        from
          `y_v_userfields`
        where
          (
            `y_v_userfields`.`fieldname` = 'okformail'
          )
      ) `t_okformail` on(
        (
          `t_uid`.`userID` = `t_okformail`.`userID`
        )
      )
    )
    left join (
      select
        `y_v_userfields`.`userID` AS `userID`,
        `y_v_userfields`.`fieldvalue` AS `fieldvalue`
      from
        `y_v_userfields`
      where
        (
          `y_v_userfields`.`fieldname` = 'vname'
        )
    ) `t_vname` on(
      (
        `t_uid`.`userID` = `t_vname`.`userID`
      )
    )
  )
where
  (`y_user`.`locked` = 0)

-- ----------------------------------------------------------------------------------------

DROP VIEW IF EXISTS b_v_meldeliste_dieses_jahr;
CREATE VIEW b_v_meldeliste_dieses_jahr AS

-- Beiträge mit Zuordnung = 1 (Regionalverband)
SELECT
    l.id AS id,
    l.Timestamp AS Erfasst_am,
    l.Beitragsjahr AS Beitragsjahr,
    CONCAT(m.Vorname, ' ', m.Nachname, ' (', m.id, ')') AS Mitglied,
    CONCAT(b.BSG, ' (', b.VKZ, ')') AS Zahlungspflichtige_BSG,
    z.Zweck AS Zuordnung,
    r.Kurzname AS Beschreibung,
    r.Basisbeitrag AS Betrag,
    b.id AS bsg_id,
    b.Verband AS rv_id
FROM
    b_meldeliste l
    JOIN b_mitglieder m       ON m.id = l.MNr
    JOIN b_bsg b              ON b.id = l.BSG
    JOIN b___beitragszuordnungen z ON z.id = l.Zuordnung
    JOIN b_regionalverband r  ON r.id = l.Zuordnung_ID
WHERE
    l.Zuordnung = 1
    AND l.Beitragsjahr = YEAR(CURDATE())

UNION

-- Beiträge mit Zuordnung = 2 (Sparte)
SELECT
    l.id AS id,
    l.Timestamp AS Erfasst_am,
    l.Beitragsjahr AS Beitragsjahr,
    CONCAT(m.Vorname, ' ', m.Nachname, ' (', m.id, ')') AS Mitglied,
    CONCAT(b.BSG, ' (', b.VKZ, ')') AS Zahlungspflichtige_BSG,
    z.Zweck AS Zuordnung,
    s.Sparte AS Beschreibung,
    s.Spartenbeitrag AS Betrag,
    b.id AS bsg_id,
    b.Verband AS rv_id
FROM
    b_meldeliste l
    JOIN b_mitglieder m       ON m.id = l.MNr
    JOIN b_bsg b              ON b.id = l.BSG
    JOIN b___beitragszuordnungen z ON z.id = l.Zuordnung
    JOIN b_sparte s           ON s.id = l.Zuordnung_ID
WHERE
    l.Zuordnung = 2
    AND l.Beitragsjahr = YEAR(CURDATE());

-- ----------------------------------------------------------------------------------------

DROP VIEW IF EXISTS b_v_meldeliste_letztes_jahr;
CREATE VIEW b_v_meldeliste_letztes_jahr AS

-- Beiträge mit Zuordnung = 1 (Regionalverband)
SELECT
    l.id AS id,
    l.Timestamp AS Erfasst_am,
    l.Beitragsjahr AS Beitragsjahr,
    CONCAT(m.Vorname, ' ', m.Nachname, ' (', m.id, ')') AS Mitglied,
    CONCAT(b.BSG, ' (', b.VKZ, ')') AS Zahlungspflichtige_BSG,
    z.Zweck AS Zuordnung,
    r.Kurzname AS Beschreibung,
    r.Basisbeitrag AS Betrag,
    b.id AS bsg_id,
    b.Verband AS rv_id
FROM
    b_meldeliste l
    JOIN b_mitglieder m       ON m.id = l.MNr
    JOIN b_bsg b              ON b.id = l.BSG
    JOIN b___beitragszuordnungen z ON z.id = l.Zuordnung
    JOIN b_regionalverband r  ON r.id = l.Zuordnung_ID
WHERE
    l.Zuordnung = 1
    AND l.Beitragsjahr = YEAR(CURDATE())-1

UNION

-- Beiträge mit Zuordnung = 2 (Sparte)
SELECT
    l.id AS id,
    l.Timestamp AS Erfasst_am,
    l.Beitragsjahr AS Beitragsjahr,
    CONCAT(m.Vorname, ' ', m.Nachname, ' (', m.id, ')') AS Mitglied,
    CONCAT(b.BSG, ' (', b.VKZ, ')') AS Zahlungspflichtige_BSG,
    z.Zweck AS Zuordnung,
    s.Sparte AS Beschreibung,
    s.Spartenbeitrag AS Betrag,
    b.id AS bsg_id,
    b.Verband AS rv_id
FROM
    b_meldeliste l
    JOIN b_mitglieder m       ON m.id = l.MNr
    JOIN b_bsg b              ON b.id = l.BSG
    JOIN b___beitragszuordnungen z ON z.id = l.Zuordnung
    JOIN b_sparte s           ON s.id = l.Zuordnung_ID
WHERE
    l.Zuordnung = 2
    AND l.Beitragsjahr = YEAR(CURDATE())-1;