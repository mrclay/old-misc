/**
 * MySQL to create a stored function to fetch the id of a user, 
 * optionally creating one if missing.
 */
DELIMITER $$

DROP FUNCTION IF EXISTS `my_db`.`f_GetUserId` $$

/**
 * Usage: SELECT f_GetUserId('foo');
 */
CREATE 
    FUNCTION `my_db`.`f_GetUserId`(in_username VARCHAR(30))
    RETURNS INT
    DETERMINISTIC
    BEGIN
        DECLARE ret INT;
        SELECT id INTO ret FROM users WHERE username = in_username LIMIT 1;
        IF ret IS NULL THEN
            INSERT INTO users (username) VALUES (in_username);
            SET ret = LAST_INSERT_ID();
            /* setup user here */
        END IF;
        RETURN ret;
    END $$

DELIMITER ;