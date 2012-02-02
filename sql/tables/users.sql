
create table users
       (userid varchar(255) primary key,
       userno  integer unsigned auto_increment not null, -- not to be used but mysql wants it
       created datetime not null,
       last_visit datetime not null,
       index unumb (userno), 
       index uurl (urlbase)
       )
ENGINE=InnoDB;
