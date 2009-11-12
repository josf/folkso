

delimiter $$
drop procedure if exists create_user$$
create procedure create_user(
                  nick_arg varchar(70),
                  firstname varchar(255),
                  lastname varchar(255),
                  email varchar(255),
                  institution varchar(255),
                  pays varchar(50),
                  fonction varchar(50)
                 )      
begin

--declare variables
declare uid varchar(79) default '';
declare nick varchar(70);
declare counting int default 1;
declare loopcheck int default 0;
declare err_msg varchar(255) default '';
declare test_uid varchar(79) default '';

set nick = lcase(nick_arg);
set counting = 1;

if length(nick) < 5 then
   set err_msg =  'ERROR: nick is less than 5 characters long';
else

-- build userid from nick
  UID: loop
    set uid = make_userid(nick, counting);
    -- test for existing, otherwise increment final field: xxxx-2009-001
    select userid
    into test_uid
    from users
    where userid = uid;

    if test_uid then 
    -- else increment and loop again
        set counting = counting + 1;
        if counting > 999 then
           set err_msg = 'ERROR: incremented up to 999 by mistake';
           leave UID;
        end if;      
    else    
       leave UID;       -- no existing uid, we are done
    end if;          
 end loop UID;

 end if;  -- nick length check (we avoid the loop)


  if length(err_msg) > 1 then
     select err_msg;
  else
   insert into users 
        (userid, firstname, lastname, nick, email, institution, pays, fonction)
        values
        (uid, firstname, lastname, nick, email, institution, pays, fonction);
   select userid, firstname, lastname, nick, email, institution, pays, fonction
   from users 
   where userid = uid;
end if;
end$$
delimiter ;


delimiter $$
drop function if exists make_userid$$
create function make_userid( 
                              nick varchar(70),
                              counting int
                 )      
returns varchar(79) deterministic
begin

declare uid varchar(79) default '';

if counting > 999 then 
   return '';
end if;

set uid = concat(nick, '-', 
                 year(now()), '-', 
                 lpad(counting, 3, '0'));
return uid;

end$$
delimiter ;