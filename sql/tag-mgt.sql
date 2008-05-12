-- TAG MANAGEMENT

-- Create new tag

-- Normalize tag

-- (local-set-key [(control c) (b)] 'sql-snip) 
--(defun sql-snip () (interactive) (snippet-insert "set final_tag = replace(final_tag, '$${1}', '$${2}');
-- "))

delimiter $$
drop function if exists normalize_tag$$
create function normalize_tag(input_tag varchar(255))
       returns varchar(120)
       deterministic
begin
        DECLARE final_tag VARCHAR(255) DEFAULT '';

        SET final_tag = lower(input_tag);
        set final_tag = replace(final_tag, ' ', '');
        set final_tag = replace(final_tag, '.', '');
        set final_tag = replace(final_tag, ':', '');
        set final_tag = replace(final_tag, ';', '');
        set final_tag = replace(final_tag, ',', '');
        set final_tag = replace(final_tag, '!', '');
        set final_tag = replace(final_tag, '?', '');
        set final_tag = replace(final_tag, '/', '');
        set final_tag = replace(final_tag, '\\', '');
        set final_tag = replace(final_tag, '{', '');
        set final_tag = replace(final_tag, '}', '');
        set final_tag = replace(final_tag, '=', '');
        set final_tag = replace(final_tag, '$', '');
        set final_tag = replace(final_tag, '<', '');
        set final_tag = replace(final_tag, '>', '');
        set final_tag = replace(final_tag, '-', '');
        set final_tag = replace(final_tag, '"', '');
        set final_tag = replace(final_tag, '''', '');

        -- shorten
        if (length(final_tag) > 120) then
            set final_tag = substr(final_tag, 1, 120);
        end if;

        return(final_tag);
end$$
delimiter ;


-- new_tag()
DELIMITER $$
DROP PROCEDURE if exists new_tag$$
CREATE PROCEDURE new_tag(input_tag varchar(255))
BEGIN
        DECLARE existing_id INTEGER DEFAULT 0;
        DECLARE normed VARCHAR(255) DEFAULT '';
        SET normed = normalize_tag(input_tag); 

        select id 
               into existing_id 
               from tag 
               where tagnorm = normed;

        if (existing_id = 0) then 
           insert into tag
                  set tagnorm = normed,
                      tagdisplay = input_tag;
           set existing_id = last_insert_id();
        end if;

        SELECT id FROM tag WHERE id = existing_id;


END$$
DELIMITER ;


-- tag page We recommend calling url_visit _before_ calling this
-- procedure, to make sure that the resource already
-- exists. Especially true if this were to be used for external URIs.
DELIMITER $$
DROP PROCEDURE IF EXISTS tag_resource$$
CREATE PROCEDURE tag_resource(resource_uri      varchar(255),
                              tag_id            integer)
BEGIN

        declare existing_tag_id int unsigned;
        declare existing_uri varchar(255);      
        declare out_status varchar(255);
--        declare exit handler for 1048
--                set out_status='Tag does not exist';


        select id
               into existing_tag_id
               from tag
               where id = tag_id;

       select id
              into existing_uri
              from resource
              where uri_normal = url_whack(resource_uri);

       insert into tagevent
              set tag_id = existing_tag_id,
              resource_id = existing_uri,
              user_id = 9999;
end$$
DELIMITER ;
           

select 1;