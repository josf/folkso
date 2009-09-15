

create table accented_characters(
       accented char(1) not null primary key, 
       noaccent char(1) not null,
       accented_code tinyint unsigned not null, 
       index coded (accented_code, noaccent),
       index chared (accented, noaccent))
ENGINE=InnoDB;