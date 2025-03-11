DROP TRIGGER IF EXISTS before_delete_b_bsg;
DELIMITER //
CREATE TRIGGER before_delete_b_bsg
BEFORE DELETE ON `b_bsg`
FOR EACH ROW
BEGIN
    -- Declare variables for error handling
    DECLARE success BOOLEAN DEFAULT TRUE;
    DECLARE error_msg VARCHAR(255);
    
    -- Use exception handler
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
        -- Set error flag and message
        SET success = FALSE;
        SET error_msg = CONCAT('Error occurred while backing up BSG data for deletion. BSG ID: ', OLD.id);
        
        -- Log the error to the log table
        INSERT INTO `log` (`zeit`, `eintrag`) 
        VALUES (NOW(), error_msg);
        
        -- Signal to prevent the delete operation
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = error_msg;
    END;
    
    -- Try to insert into deleted BSG table
    INSERT INTO `b_bsg_deleted` (
        `id`, `Verband`, `BSG`, `Ansprechpartner`, 
        `RE_Name`, `RE_Name2`, `RE_Strasse_Nr`, 
        `RE_Strasse2`, `RE_PLZ_Ort`, `VKZ`, `delete_date`
    )
    VALUES (
        OLD.id, OLD.Verband, OLD.BSG, OLD.Ansprechpartner,
        OLD.RE_Name, OLD.RE_Name2, OLD.RE_Strasse_Nr,
        OLD.RE_Strasse2, OLD.RE_PLZ_Ort, OLD.VKZ, NOW()
    );
    
    -- Log successful backup before deletion
    INSERT INTO `log` (`zeit`, `eintrag`)
    VALUES (NOW(), CONCAT('BSG with ID: ', OLD.id, ' (', OLD.BSG, ') will be deleted. Data backed up.'));
    
END //
DELIMITER ;
