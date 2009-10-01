set character_set_client = utf8;

drop table if exists replace_characters;
create table replace_characters
       (toreplace_code int unsigned not null primary key, 
       replacewith char(1) not null,
       index coded (toreplace_code, replacewith))
character set utf8
collate utf8_general_ci
ENGINE=InnoDB;


insert into replace_characters 
       (replacewith, toreplace_code)
       values
       ('a', ord('à')),
       ('a', ord('ä')),
       ('a', ord('â')),

       ('c', ord('ç')),

       ('e', ord('é')),
       ('e', ord('è')),
       ('e', ord('ë')),
       ('e', ord('ê')),

       ('i', ord('î')),
       ('i', ord('ï')),

       ('o', ord('ô')),
       ('o', ord('ö')),
       
       ('u', ord('û')),
       ('u', ord('ü')),
       ('u', ord('ù')),

       ('y', ord('ŷ')),
       ('y', ord('ÿ')),

       ('a', ord('æ')),

       ('-', ord(' ')),
       ('-', ord('_')),
       ('-', ord(',')),
       ('-', ord(';')),
       ('-', ord('.')),
       ('-', ord(':')),
       ('-', ord('~'));


create table memoize_tagnormal
       (in_tag  varchar(120) not null primary key,
       out_tag varchar(120) not null,
       index theboth (in_tag, out_tag))
character set utf8;

