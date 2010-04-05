(require 'cl)

(setq fktest-base "http://www.fabula.org/tags/")
(setq fktest-resources 
      '((rembrandt . "http://www.fabula.org/actualites/article13644.php")
        (numero3 . "http://www.fabula.org/actualites/article22002.php")
        (yokel . "http://example.com/1")))
      

(defun fk-build-resource-get (base key &rest apairs)
  (let ((res-and-base (concat 
                       base "resource.php?folksores=" (cdr (assoc key fktest-resources)))))
    (if (null apairs)
        res-and-base
      (concat res-and-base 
              (loop for pair in apairs concat 
                    (concat "&" (car pair) "=" (cdr pair)))))))

(setq fktest-tags
      '((number . "8170")
        (poesie . "poésie")
        (tagone . "tagone")
        (dyn1 . "dyn1")
        (communication . "communication")
        (gustav . "gustav-2010-001")))


(defun fk-build-get (base key &rest apairs)
  (let ((res-and-base (concat 
                       base (cdr (assoc key fktest-tags)))))
    (if (null apairs)
        res-and-base
      (concat res-and-base 
              (loop for pair in apairs concat 
                    (concat "&" (car pair) "=" (cdr pair)))))))


(defun fk-run-req (url)
  (switch-to-buffer (url-retrieve-synchronously url)))



;; basic xml get
(fk-run-req 
  "http://www.fabula.org/tags/resource.php?folksores=http://www.fabula.org/actualites/article13644.php&folksodatatype=xml&folksoclouduri=1")

(fk-run-req "http://localhost/resource.php?folksoclouduri=1&folksores=http://example.com/1")

(fk-run-req "http://localhost/user.php?_=1265366823256&folksotag=dyn1&folksouid=gustav-2010-001&folksodatatype=json")

(url-retrieve-synchronously
 (fk-build-resource-get
  fktest-base 'rembrandt '("folksodatatype" . "xml") '("folksoclouduri" . "1")))

(fk-run-req 
 (fk-build-resource-get 
  "http://localhost/" 'numero3 '("folksodatatype" . "xml") '("folksoclouduri" . "1")))

(switch-to-buffer (url-retrieve-synchronously
 (fk-build-tag-get
  "http://localhost/" 'number '("folksorelated" . "1"))))

(switch-to-buffer (url-retrieve-synchronously
                   (fk-build-get "http://localhost/user.php?folksouid="
                                 'gustav
                                 '("gustav" . "gustav-2010-001")
                                 '("folksomytags" . "1")
                                 '("folksodatatype" . "json"))))

(switch-to-buffer (url-retrieve-synchronously
                   (fk-build-get "http://localhost/tag.php?folksouid="
                                 'gustav
                                 '("folksotag" . "tagone")
                                 '("folksodatatype" . "xml"))))

(switch-to-buffer (url-retrieve-synchronously
                   (fk-build-get "http://localhost/user.php?folksouid="
                                 'gustav
                                 '("folksotag" . "tagone")
                                 '("folksosess" . "bcfebee8a0f77b1f371d8fffd20102ea48a413a903f5c05bb965836ddc5b5177")
                                 '("folksodatatype" . "json"))))

(switch-to-buffer (url-retrieve-synchronously
                   "http://localhost/user.php?folksouid=gustav-2010-001&folksomytags=1&folksodatatype=json"))

(fk-run-req "http://www.fabula.org/tags/user.php?folksouid=jfahey-2009-001&folksomytags=1&folksodatatype=json")


(fk-run-req
 "http://www.fabula.org/tag/poesie/cloud/datatype/xml")

(fk-run-req 
 "http://www.fabula.org/tags/resource.php?folksores=http://www.fabula.org/actualites/article21902.php&folksoclouduri=1&folksodatatype=xml")
(switch-to-buffer (url-retrieve-synchronously
                   (fk-build-get
                    "http://localhost/resource.php" 
                    'yokel 
                    '("folksoclouduri" . "1")
                    '("folksodatatype" . "xml"))))
                    
(fk-run-req (fk-build-get
             "http://localhost/tag.php?folksotag="
             'poesie
             '("folksorelated" . "1")))

(fk-run-req (fk-build-get
             "http://www.fabula.org/tags/tag.php?folksotag="
             'poesie
             '("folksorelated" . "1")
             '("folksodatatype" . "xml")))

(fk-run-req (fk-build-get
             "http://localhost/tag.php?folksotag="
             'dyn1
             '("folksofancy" . "1")
             '("folksodatatype" . "xml")))

(fk-run-req (fk-build-get
             "http://www.fabula.org/tags/tag.php?folksotag="
             'poesie
             '("folksofancy" . "1")
             '("folksooffset" . "50")))
             '("folksofeed" . "atom")))

(fk-run-req "http://www.fabula.org/tags/resource.php?folksores=65700&folksodatatype=xml&folksoean13=1")
             


(fk-run-req (fk-build-get
             "http://localhost/tag.php?folksotag="
             'tagone
             '("folksofancy" . "1")
             '("folksofeed" . "atom")))

(fk-run-req "http://localhost/folksovisit.php?resource=http://bobsworld.com")

(fk-run-req "http://localhost/tag/tagone/related/datatype/html")
             

(fk-run-req (fk-build-get
             "http://localhost/tag.php?folksotag="
             'tagone
             '("folksofancy" . "1")))



(switch-to-buffer (url-retrieve-synchronously
                   (fk-build-get "http://localhost/user.php?folksouser=" 
                                 'gustav
                                 '("folksomytags" . "1")
                                 '("folksosession" . "fb382640769de5c8ae22bdec22835c1c56f3be214a59da9b1beebc836764b559"))))
                                 
                                 




                    

(url-retrieve-synchronously "http://www.fabula.org/tags/resource.php?folksores=http://www.fabula.org/actualites/article23682.php&folksodatatype=html")


(url-retrieve-synchronously
 (fk-build-tag-get
  fktest-base 'communication '("folksodatatype" . "xml") '("folksofancy" . "1")))
