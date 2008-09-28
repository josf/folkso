DROP TABLE IF EXISTS metatag;
CREATE TABLE metatag
       (id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
       tagnorm VARCHAR(120) NOT NULL UNIQUE,
       tagdisplay VARCHAR(150) NOT NULL UNIQUE,
       INDEX tagids (id),
       INDEX tagnorms (tagnorm))
    ENGINE=InnoDB;

INSERT INTO metatag SET tagnorm = 'normal',
                        tagdisplay = 'normal';
insert into metatag set tagnorm = 'auteur1',
                        tagdisplay = 'Auteur 1';
insert into metatag set tagnorm = 'auteur2',
                        tagdisplay = 'Auteur 2';
insert into metatag set tagnorm = 'auteur3',
                        tagdisplay = 'Auteur 3';
insert into metatag set tagnorm = 'auteur4',
                        tagdisplay = 'Auteur 4';
insert into metatag set tagnorm = 'auteur5',
                        tagdisplay = 'Auteur 5';
insert into metatag set tagnorm = 'contributeur1',
                        tagdisplay = 'Contributeur 1';
insert into metatag set tagnorm = 'contributeur2',
                        tagdisplay = 'Contributeur 2';
