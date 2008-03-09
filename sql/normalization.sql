
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
        declare accum VARCHAR(250) DEFAULT '';
        
        IF (instr(input_string, '&') > 0) THEN
                   

delimiter $$

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