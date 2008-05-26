select tag.tagdisplay, 
       tag.id, count(tag.id) as c1,  
from (select count(te.tag_id) as cnt
                             from tagevent te
                             join resource on te.resource_id = resource.id
                             where resource.uri_normal = 'fabula.org/revue/document1312.php') as icnt

       (select count(distinct cnt)
                     from (select count(te.tag_id) as cnt
                             from tagevent te
                             join resource on te.resource_id = resource.id
                             where resource.uri_normal = 'fabula.org/revue/document1312.php') as icnt
                      ) as c2
       from tagevent tt
       join tag on tt.tag_id = tag.id
       join resource on tt.resource_id = resource.id
       where (resource.uri_normal = 'fabula.org/revue/document1312.php')
       group by tag.id;



select tsq.tagdisplay,
       tsq.id,
       (select count(distinct itsq.icnt)
               from (select tag.tagdisplay,
                    tag.id,
                    count(tag.id) as icnt
                    from tag
                    join tagevent on tagevent.tag_id = tag.id
                    join resource on tagevent.resource_id = resource.id
                    where resource.uri_normal = 'fabula.org/revue/document1312.php'
                    group by tag.id) itsq
               where itsq.icnt >= tsq.cnt) as rank
       from (select tag.tagdisplay,
                    tag.id,
                    count(tag.id) as cnt
                    from tag
                    join tagevent on tagevent.tag_id = tag.id
                    join resource on tagevent.resource_id = resource.id
                    where resource.uri_normal = 'fabula.org/revue/document1312.php'
                    group by tag.id) tsq;
            
       