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
INSERT INTO metatag SET tagnorm = 'contributor',
                        tagdisplay = 'contributor';
INSERT INTO metatag SET tagnorm = 'creator',
                        tagdisplay = 'creator';
INSERT INTO metatag SET tagnorm = 'coverage',
                        tagdisplay = 'coverage';
INSERT INTO metatag SET tagnorm = 'date',
                        tagdisplay = 'date';
INSERT INTO metatag SET tagnorm = 'description',
                        tagdisplay = 'description';
INSERT INTO metatag SET tagnorm = 'format',
                        tagdisplay = 'format';
INSERT INTO metatag SET tagnorm = 'identifier',
                        tagdisplay = 'identifier';
INSERT INTO metatag SET tagnorm = 'language',
                        tagdisplay = 'language';
INSERT INTO metatag SET tagnorm = 'publisher',
                        tagdisplay = 'publisher';
INSERT INTO metatag SET tagnorm = 'relation',
                        tagdisplay = 'relation';
INSERT INTO metatag SET tagnorm = 'rights',
                        tagdisplay = 'rights';
INSERT INTO metatag SET tagnorm = 'source',
                        tagdisplay = 'source';
INSERT INTO metatag SET tagnorm = 'subject',
                        tagdisplay = 'subject';
INSERT INTO metatag SET tagnorm = 'title',
                        tagdisplay = 'title';
INSERT INTO metatag SET tagnorm = 'type',
                        tagdisplay = 'type';
