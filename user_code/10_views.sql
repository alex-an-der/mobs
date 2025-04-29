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

  