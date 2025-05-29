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