## Jedes Recht für alle
DROP TRIGGER IF EXISTS set_all_privileges_on_insert_y_user;
DELIMITER $$
CREATE TRIGGER set_all_privileges_on_insert_y_user
BEFORE INSERT ON y_user
FOR EACH ROW
BEGIN
    SET NEW.roles = 75;
END
$$
DELIMITER ;

-- ------------------------------------------------------------