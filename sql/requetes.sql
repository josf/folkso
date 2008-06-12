
-- rank is the popularity relative to _this_ resource : ie. the lowest
-- score is the most commonly attributed tag for this resource.
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
            

-- rank here is relative to the overall rank of the tag in the entire
-- DB.  if this is slow (which it probably is) we might think about
-- having a temporary popularity table that could be updated daily or
-- something.
select tsq.tagdisplay,
       tsq.id,
       (select count(distinct itsq.pop)
        from (select 
                   (select count(te.tag_id)
                       from tagevent te
                       where te.tag_id = tag.id) as pop
                   from tag
                   join tagevent on tagevent.tag_id = tag.id
                   join resource on tagevent.resource_id = resource.id
                   where resource.uri_normal = 'fabula.org/revue/document1312.php') itsq
        where itsq.pop >= tsq.pop) as rank
       from (select tag.tagdisplay,
                    tag.id,
                    (select count(te.tag_id)
                       from tagevent te
                       where te.tag_id = tag.id) as pop
                       from tag
                       join tagevent on tagevent.tag_id = tag.id
                       join resource on tagevent.resource_id = resource.id
                       where resource.uri_normal = 'fabula.org/revue/document1312.php') tsq;



select tag.tagdisplay,
       tag.tagnorm,
       tag.id,
       (select count(tag_id)
               from tagevent tage
               join resource on tage.resource_id = resource.id
               where (resource.uri_normal = 'fabula.org/revue/document1312.php') and 
                      (tage.tag_id = tag.id))
                 as localcount,
       tpop.popularity as pop
       from tag
       join tagevent te on te.tag_id = tag.id
       join resource res on res.id = te.resource_id
       join tag_popularity tpop on tpop.tag_id = tag.id
       where res.uri_normal = 'fabula.org/revue/document1312.php'
       group by tag.id;


 
select r.title, 
       r.id,
       group_concat(tt.td2 separator ' - ' )
       from resource r
       join tagevent te on r.id = te.resource_id
       join tag t on te.tag_id = t.id
       cross join (select tagdisplay as td2
                          from tag t2
                          join tagevent te2 on te2.tag_id = t2.id
                          join resource r2 on r2.id = te2.resource_id
                          where r2.id = r.id) as tt
       where t.id =50
       group by r.id;
