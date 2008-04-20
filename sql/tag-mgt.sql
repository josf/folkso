-- TAG MANAGEMENT

-- Create new tag

-- Normalize tag

-- (local-set-key [(control c) (b)] 'sql-snip) 
--(defun sql-snip () (interactive) (snippet-insert "set final_tag = replace(final_tag, '$${1}', '$${2}');
"))

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

delimiter $$
drop procedure if exists new_tag$$
create function new_tag(input_tag varchar(255))
begin
        declare existing_id int unsigned default 0;
        declare normed varchar(255) default '';

        select id 
               into existing_id 
               from tag 
               where tagnorm = normed;

        if (existing_id > 0) then
           return(existing_id); -- probably wrong
        else
           insert into tag
                  set tagnorm = normed,
                      tagdisplay = input_tag;
           select last_insert_id();
        end if;


end$$
delimiter ;