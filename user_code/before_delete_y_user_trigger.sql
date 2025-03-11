DROP TRIGGER IF EXISTS before_delete_y_user;
DELIMITER //
CREATE TRIGGER before_delete_y_user
BEFORE DELETE ON `y_user`
FOR EACH ROW
BEGIN
    -- Declare variables for error handling
    DECLARE success BOOLEAN DEFAULT TRUE;
    DECLARE error_msg VARCHAR(255);
    DECLARE member_exists INT DEFAULT 0;
    
    -- Use exception handler
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
        -- Set error flag and message
        SET success = FALSE;
        SET error_msg = CONCAT('Error occurred while backing up user data for deletion. User ID: ', OLD.id);
        
        -- Log the error to the existing log table
        INSERT INTO `log` (`zeit`, `eintrag`) 
        VALUES (NOW(), error_msg);
        
        -- Signal to prevent the delete operation
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = error_msg;
    END;
    
    -- Try to insert into deleted users table
    INSERT INTO `y_deleted_users` (`y_id`, `mail`, `delete_date`)
    VALUES (OLD.id, OLD.mail, NOW());
    
    -- Check if there's a corresponding record in b_mitglieder
    -- The y_id in b_mitglieder corresponds to the id in y_user
    SELECT COUNT(*) INTO member_exists FROM `b_mitglieder` WHERE `y_id` = OLD.id;
    
    -- If member record exists, backup to b_mitglieder_deleted
    IF member_exists > 0 THEN
        INSERT INTO `b_mitglieder_deleted` (
            `id`, `y_id`, `BSG`, `Vorname`, `Nachname`, `Mail`, 
            `Geschlecht`, `Geburtsdatum`, `Mailbenachrichtigung`, `delete_date`
        )
        SELECT 
            `id`, `y_id`, `BSG`, `Vorname`, `Nachname`, `Mail`, 
            `Geschlecht`, `Geburtsdatum`, `Mailbenachrichtigung`, NOW()
        FROM 
            `b_mitglieder` 
        WHERE 
            `y_id` = OLD.id;
            
        -- Log member data backup
        INSERT INTO `log` (`zeit`, `eintrag`)
        VALUES (NOW(), CONCAT('Member data for user ID: ', OLD.id, ' has been backed up to b_mitglieder_deleted.'));
    END IF;
    
    -- Log successful backup before deletion
    INSERT INTO `log` (`zeit`, `eintrag`)
    VALUES (NOW(), CONCAT('User with ID: ', OLD.id, ' and email: ', OLD.mail, ' will be deleted. Data backed up.'));
    
END //
DELIMITER ;
