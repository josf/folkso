-- TAG MANAGEMENT

-- Create new tag

-- Normalize tag

-- (local-set-key [(control c) (b)] 'sql-snip) 
--(defun sql-snip () (interactive) (snippet-insert "set final_tag = replace(input_tag, '$${1}', '$${2}');
--"))

delimiter $$
drop function if exists normalize_tag$$
create function normalize_tag(input_tag varchar(255))
       returns varchar(255)
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
        set final_tag = replace(final_tag, '//', '');
        set final_tag = replace(final_tag, '\\', '');
        set final_tag = replace(final_tag, '{', '');
        set final_tag = replace(final_tag, '}', '');
        set final_tag = replace(final_tag, '=', '');
        set final_tag = replace(final_tag, '$', '');
        set final_tag = replace(final_tag, '<', '');
        set final_tag = replace(final_tag, '>', '');
        set final_tag = replace(final_tag, '-', '');

        return(final_tag);
end$$
delimiter ;

--        set final_tag = replace(final_tag, 'é', 'e');
--         set final_tag = replace(final_tag, 'ê', 'e');
--         set final_tag = replace(final_tag, 'ë', 'e');
--         set final_tag = replace(final_tag, 'â', 'a');
--         set final_tag = replace(final_tag, 'ä', 'a');
--        set final_tag = replace(final_tag, 'î', 'i');
--        set final_tag = replace(final_tag, 'ï', 'i');
--        set final_tag = replace(final_tag, 'ô', 'o');
--      set final_tag = replace(final_tag, 'ö', 'o');
--         set final_tag = replace(final_tag, 'û', 'u');
--         set final_tag = replace(final_tag, 'ü', 'u');
--         set final_tag = replace(final_tag, 'ÿ', 'y');
--      set final_tag = replace(final_tag, 'ç', 'c');
--         set final_tag = replace(final_tag, 'à', 'a');
--          set final_tag = replace(final_tag, 'è', 'e');
--          set final_tag = replace(final_tag, 'ù', 'u');