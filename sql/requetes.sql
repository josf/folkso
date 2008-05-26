
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

       