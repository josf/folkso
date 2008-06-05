drop table if exists tag;
create table tag
       (id int unsigned primary key auto_increment,
       tagnorm varchar(120) not null unique,
       tagdisplay varchar(150) not null unique,
       index tagids (id),
       index tagnorms (tagnorm))
    ENGINE=InnoDB;


-- (local-set-key [(control c) (b)] 'sql-snip)
-- (defun sql-snip () 
--     (interactive)(snippet-insert "insert into tag set tagnorm = '$${norm}',  tagdisplay = '$${raw}';
-- "))

-- alter table tagevent add index tag_res (tag_id, resource_id);
-- alter table tagevent add index res_tag (resource_id, tag_id);

insert into tag set tagnorm = 'gerardgenette',  tagdisplay = 'Gérard Genette';
insert into tag set tagnorm = 'jacquesderrida',  tagdisplay = 'Jacques Derrida';
insert into tag set tagnorm = 'richardmstallman',  tagdisplay = 'Richard M. Stallman';
insert into tag set tagnorm = 'merleau-ponty',  tagdisplay = 'Maurice Merleau-Ponty';
insert into tag set tagnorm = 'husserl',  tagdisplay = 'Edmund Husserl';



