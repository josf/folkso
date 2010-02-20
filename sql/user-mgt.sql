

delimiter $$
drop procedure if exists create_user$$
create procedure create_user(
                  userid_base_arg varchar(70),
                  oid_url_val text,
                  fb_id bigint,
                  firstname varchar(255),
                  lastname varchar(255),
                  email varchar(255),
                  institution varchar(255),
                  pays varchar(50),
                  fonction varchar(50)
                 )      
begin

-- declare variables

declare fb_test bigint default 0;
declare oid_test varchar(255) default '';
declare uid varchar(79) default '';
declare url varchar(100) default '';
declare userid_base varchar(70);
declare urlbase_var varchar(100) default '';
declare counting int default 1;
declare basecounting int default 0;
declare loopcheck int default 0;
declare err_msg varchar(255) default '';
declare test_uid varchar(79) default '';
declare test_url varchar(100) default '';

-- dots are allowed in the urlbase but not in the userid
set urlbase_var = lcase(userid_base_arg);
set userid_base = replace(urlbase_var, '.', '');
set url = urlbase_var;

set counting = 1;
set basecounting = 0;

if length(userid_base) < 5 then
   set err_msg =  'ERROR: useridbase is less than 5 characters long';
elseif length(oid_url_val) = 0 and fb_id = 0 then
   set err_msg = 'ERROR: no login id data (fb and oid are empty)';
elseif length(oid_url_val) > 0 and fb_id > 0 then
   set err_msg = 'ERROR: we have both fb and oid. This will not work';

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

-- build the user url
-- decided to store this separately so that it can be customized later without 
-- touching the structure of the userid
 URL: loop

  select urlbase into test_url
   from users
   where urlbase = url
   limit 1;

  if length(test_url) > 0 then
     set url = make_urlbase(urlbase_var, basecounting);
     set basecounting = basecounting + 1;
     set test_url = '';
     if basecounting > 999 then 
       set err_msg = 'ERROR: incremented up to 999 with basecounting';
       leave URL;
     end if;
  else
    leave URL;    -- no existing url, we are done. variable is ready to be 
  end if;
 end loop URL;

 end if;  -- end of data checks (we avoid the loop if we already have errors)


   -- check for existing oid_url or fb_id
   if length(oid_url_val) > 0 then
       select oid_url into oid_test from oid_urls
       where oid_url = oid_url_val;
       
       if length(oid_test) > 0 then
          set err_msg = 'ERROR: oid_url already exists, cannot create user';
       end if;
  elseif fb_id > 0 then
       select fb_uid into fb_test from fb_ids
       where fb_uid = fb_id;

       if fb_test > 0 then
          set err_msg = 'ERROR: fb_id already exists, cannot create user';
      end if;
  end if;

  if length(err_msg) > 1 then
     select err_msg as error_message;
  else
   start transaction;
   insert into users 
        (userid, urlbase) values (uid, url);


   if length(concat(firstname, lastname, email, institution, pays, fonction)) > 0 then
      insert into user_data
      (userid, firstname, lastname, email, institution, pays, fonction)
      values
      (uid, firstname, lastname, email, institution, pays, fonction);
   end if;

   if length(oid_url_val) > 1 then
   insert into oid_urls
          (userid, oid_url)
          values
          (uid, oid_url_val);
   else
   insert into fb_ids
          (userid, fb_uid)
          values
          (uid, fb_id);
   end if;
   commit;

   select userid, firstname, lastname, email, institution, pays, fonction
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