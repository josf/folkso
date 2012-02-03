

delimiter $$
drop procedure if exists create_user$$
create procedure create_user(
                  identifier_arg varchar(255),
                  service_id_arg char(4),
                  firstname varchar(255),
                  lastname varchar(255),
                  email varchar(255),
                  institution varchar(255),
                  pays varchar(50),
                  fonction varchar(50)
                 )      
begin

-- declare variables

declare uid varchar(255) default '';
declare url varchar(255) default '';
declare identifier_var varchar(255) default '';
declare userid_base varchar(255) default '';
declare counting int default 1;
declare basecounting int default 0;
declare loopcheck int default 0;
declare err_msg varchar(255) default '';
-- declare test_identifier varchar(255) default '';
declare test_uid varchar(255) default '';
declare test_identifier int default 0;

set identifier_var = identifier_arg;
set userid_base = replace(identifier_var, '.', '');
set userid_base = replace(userid_base, '/', '');


set counting = 1;
set basecounting = 0;

if length(userid_base) < 5 then
   set err_msg =  'ERROR: useridbase is less than 5 characters long';
else

-- build userid from userid_base
  UID: loop
    set uid = make_userid(userid_base, counting);

    -- test for existing
    select userid
    into test_uid
    from users
    where userid = uid
    limit 1;

    if length(test_uid) > 0 then 
    -- oops, already taken so we increment and loop again

        set counting = counting + 1; 
        set test_uid = '';
        if counting > 999 then
           set err_msg = 'ERROR: incremented up to 999 by mistake';
           leave UID;
        end if;      
    else    
       leave UID;       -- no existing uid, we are done. 
                        -- uid should be correctly defined
    end if;          
 end loop UID;

 end if;  -- end of data checks (we avoid the loop if we already have errors)

 -- check for existing identifier
 
  select count(*) into test_identifier
    from user_services
    where identifier = identifier_arg;


   if test_identifier > 0 then
     set err_msg = 'ERROR: identifier already attributed to a user';
   end if;
   

  if length(err_msg) > 1 then
     select err_msg as error_message;
  else
   start transaction;
   insert into users 
        (userid) values (uid);


   if length(concat(firstname, lastname, email, institution, pays, fonction)) > 0 then
      insert into user_data
      (userid, firstname, lastname, email, institution, pays, fonction, firstname_norm, lastname_norm)
      values
      (uid, firstname, lastname, email, institution, pays, fonction, normalize_tag(firstname), normalize_tag(lastname));
   end if;

   insert into user_services
   (userid, service_id, identifier)
   values
   (uid, service_id_arg, identifier_arg);


   commit;

   select userid
   from users 
   where userid = uid;
end if;
end$$
delimiter ;
grant execute on procedure folksonomie.create_user to 'folkso'@'localhost';
grant execute on procedure folksonomie.create_user to 'folkso-rw'@'localhost';


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


delimiter $$
drop function if exists make_urlbase$$
create function make_urlbase(
       inbase varchar(70),
       basecounting int
       )
returns varchar(100) deterministic
begin

declare outurl varchar(100) default '';

if basecounting > 999 then 
   return '';
end if;

if basecounting = 0 then
   return inbase;
else
   return concat(inbase, basecounting);
end if;
end$$
delimiter ;

delimiter $$
drop procedure if exists delete_user_with_tags$$
create procedure delete_user_with_tags(
       userid_arg varchar(255)
       )

begin
        delete from user_subscription where userid = userid_arg;
        delete from user_data where userid = userid_arg;
        delete from fb_ids where userid = userid_arg;
        delete from oid_urls where userid = userid_arg;
        delete from sessions where userid = userid_arg;
        delete from users_rights where userid = userid_arg;
        delete from tagevent where userid = userid_arg;
        delete from users where userid = userid_arg;


end$$
delimiter ;

-- Both userdata triggers enforce the relationship between firstname/lastname
-- and firstname_norm/lastname_norm
delimiter $$
drop trigger if exists userdata_insert_check$$
create trigger userdata_insert_check
       before insert
on user_data
for each row      
begin

        declare existing_count integer;
        declare current_max_ord integer;
        declare new_norm_firstname varchar(255);
        declare new_norm_lastname varchar(255);

        set new_norm_firstname = normalize_tag(NEW.firstname);
        set new_norm_lastname = normalize_tag(NEW.lastname);    
        set NEW.firstname_norm = new_norm_firstname;
        set NEW.lastname_norm = new_norm_lastname;


        select count(*)
               into existing_count
               from user_data
        where firstname_norm=new_norm_firstname
              and 
              lastname_norm=new_norm_lastname;

        if (existing_count > 0) then

           select max(ordinal)
                  into current_max_ord
                  from (select * from user_data 
                       where firstname_norm=new_norm_firstname
                             and 
                             lastname_norm=new_norm_lastname) as huh;
                  
           set NEW.ordinal = current_max_ord + 1;
        end if;

end$$
delimiter ;
 
delimiter $$
drop trigger if exists userdata_update_check$$
create trigger userdata_update_check
       before update
on user_data
for each row
begin
        declare existing_count integer;
        declare current_max_ord integer;
        declare new_norm_firstname varchar(255);
        declare new_norm_lastname varchar(255);

        set new_norm_firstname = normalize_tag(NEW.firstname);
        set new_norm_lastname = normalize_tag(NEW.lastname);
        set NEW.firstname_norm = new_norm_firstname;
        set NEW.lastname_norm = new_norm_lastname;

        if ((new_norm_firstname <> OLD.firstname_norm) ||
            (new_norm_lastname <> OLD.lastname_norm)) then

            select count(*)
                   into existing_count
                   from user_data
            where firstname_norm = new_norm_firstname
                  and
                  lastname_norm = new_norm_lastname;

            if (existing_count > 0) then
               select max(ordinal)
                  into current_max_ord
                  from user_data
               where firstname_norm=new_norm_firstname
                 and 
                 lastname_norm=new_norm_lastname;
            set NEW.ordinal = current_max_ord + 1;

            else -- this is necessary because we might be changing from a name w/ 
                 -- dupes to a name without
            set NEW.ordinal = 0;
            end if;
       end if; 

end$$
delimiter ;
