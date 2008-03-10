
delimiter $$
drop function if exists remove_end$$
create function remove_end(input_string VARCHAR(250),
                           remove_target VARCHAR(250))
                RETURNS VARCHAR(250)
                DETERMINISTIC
BEGIN
        declare target_length INT;
        declare output_string VARCHAR(250);
        set target_length=length(remove_target);

        IF (target_length=0) THEN
           SET output_string=input_string;
        ELSEIF ( substring(input_string, 0 - target_length, target_length) = remove_target ) THEN
             SET output_string=substring(input_string, 1, length(input_string) - target_length);
        ELSE 
             SET output_string=input_string;
        END IF;
        RETURN(output_string);

END$$

delimiter ;


delimiter $$
drop function if exists query_sort$$
create function query_sort(input_string VARCHAR(255))
       RETURNS VARCHAR(255)
       deterministic

BEGIN
        DECLARE accum VARCHAR(250) DEFAULT '';
        DECLARE the_position INT DEFAULT 1;
        DECLARE no_more_rows INT DEFAULT 0;
        DECLARE inform VARCHAR(255) DEFAULT '';
        DECLARE parm VARCHAR(255) DEFAULT '';
        DECLARE local_accum VARCHAR(255) DEFAULT '';
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_rows=1; -- no_more_rows initialized at 0           
        
        IF (instr(input_string, '&') > 0) THEN
           
           CREATE TEMPORARY TABLE tmpUrlAccum(
                  param VARCHAR(255) primary key,
                  info  VARCHAR(255) default null);


           WHILE (locate('&', input_string, the_position) > 0) DO
                INSERT INTO tmpUrlAccum SET
                   param = substring(input_string, the_position, locate('=', input_string, the_position) -1),
                   info  = substring(input_string, locate('=', input_string, the_position) + 1,
                                                   locate('&', input_string, the_position) -1);

                   /* is there another '&'? */
                   IF ( locate('&', input_string, the_position) > 0) THEN
                     SET the_position = locate('&', input_string, the_position);
                   ELSE -- no more '&', so we grab the rest of the string
                     INSERT INTO tmpUrlAccum SET
                                param = substring(input_string, 
                                                  the_position + 1, 
                                                  locate('=', input_string, the_position) - 1),
                                info = substring(input_string,
                                                  locate('=', input_string, the_position) + 1);
                     SET the_position = length(input_string);
                   END IF;
           END WHILE;


           DECLARE our_cursor CURSOR FOR SELECT param, info
                                                 FROM tmpUrlAccum
                                                 ORDER BY param;

           OPEN our_cursor;
           REPEAT
                FETCH our_cursor INTO parm, inform;
                IF (length(accum) > 0) THEN
                   SET accum = concat(accum, '&', parm, '=', inform);
                ELSE
                   SET accum = concat(parm, '=', inform);
                END IF;
           UNTIL no_more_rows;
           END REPEAT;
           CLOSE our_cursor;
           SET no_more_rows = 0;
           DROP TABLE tmpUrlAccum;
           END IF;
           RETURN accum;
END$$      
delimiter ; 


                          
delimiter$$
drop function if exists url_whack$$
create function url_whack(input_url VARCHAR(250))
       RETURNS VARCHAR(250)
       DETERMINISTIC
begin
        DECLARE my_url VARCHAR(250) DEFAULT input_url ;
        DECLARE query_part VARCHAR(250) DEFAULT '';
        DECLARE query_start INT DEFAULT 0;

        IF (INSTR(input_url, '&')) THEN
           SET query_part=substring(input_url, 
                                    instr(input_url, '&') + 1);
           SET my_url=substring(input_url,
                                instr(input_url, '&') - 1);
        END IF;                                                                
        
        set my_url=lower(input_url);
        if ( substring(my_url, 1, 7) = 'http://') THEN
                set my_url=substring(my_url, 8);
        end if;

        set my_url=remove_end(my_url, 'index.php');
        set my_url=remove_end(my_url, 'index.html');
        set my_url=remove_end(my_url, 'index.htm');
        set my_url=remove_end(my_url, '/');         

        IF (substring(my_url, 1, 4) = 'www.') THEN
           SET my_url=substring(my_url, 5);
        END IF; 
        
        IF ( length(query_part) > 0) THEN
           SET my_url= concat( my_url, '&', query_sort(query_part));
        END IF;

        RETURN(my_url);        
end$$
delimiter ;





drop table if exists urltest;
create table urltest
       (id bigint(20) unsigned primary key auto_increment,
       url varchar(250) not null);

insert into urltest set url='http://example.com';
insert into urltest set url='example.com';
insert into urltest set url='http://example.com/';
insert into urltest set url='http://EXAMPLE.com/';
insert into urltest set url='http://www.example.com/index.htm';
insert into urltest set url='http://www.example.com/index.html';
insert into urltest set url='http://www.example.com/index.php';
insert into urltest set url='http://www.example.com/';
insert into urltest set url='http://www.example.com';
insert into urltest set url='http://www.example.com?user=bob&page=4';