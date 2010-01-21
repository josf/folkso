(require 'cl)

(setq fktest-base "http://www.fabula.org/tags/")
(setq fktest-resources 
      '((rembrandt . "http://www.fabula.org/actualites/article13644.php")
        (numero3 . "http://www.fabula.org/actualites/article22002.php")
        (yokel . "20634")))
      

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
        (communication . "communication")
        (gustav . "gustav-2009-001")))


(defun fk-build-get (base key &rest apairs)
  (let ((res-and-base (concat 
                       base (cdr (assoc key fktest-tags)))))
    (if (null apairs)
        res-and-base
      (concat res-and-base 
              (loop for pair in apairs concat 
                    (concat "&" (car pair) "=" (cdr pair)))))))




;; basic xml get
(url-retrieve-synchronously
 "http://www.fabula.org/tags/resource.php?folksores=http://www.fabula.org/actualites/article13644.php&folksodatatype=xml") 

(url-retrieve-synchronously
 (fk-build-resource-get
  fktest-base 'rembrandt '("folksodatatype" . "xml") '("folksoclouduri" . "1")))

(url-retrieve-synchronously
 (fk-build-resource-get 
  "http://localhost/" 'numero3 '("folksodatatype" . "xml") '("folksoclouduri" . "1")))

(switch-to-buffer (url-retrieve-synchronously
 (fk-build-tag-get
  "http://localhost/" 'number '("folksorelated" . "1"))))

(switch-to-buffer (url-retrieve-synchronously
                   (fk-build-get "http://localhost/user.php?folksouid="
                                 'gustav
                                 '((gustav . "gustav-2009-001"))
                                 '("folksogetmytags" . "1")
                                 '("folksodatatype" . "json"))))

(switch-to-buffer (url-retrieve-synchronously
                   "http://localhost/user.php?folksouid=gustav-2009-001&folksogetmytags=1&folksodatatype=json"))

(switch-to-buffer (url-retrieve-synchronously
                   (fk-build-resource-get
                    "http://localhost/" 
                    'yokel 
                    '("folksoclouduri" . "1")
                    '("folksodatatype" . "xml"))))
                    
(switch-to-buffer (url-retrieve-synchronously
                   (fk-build-tag-get
                    "http://localhost/"
                    'poesie
                    '("folksorelated" . "1"))))

(switch-to-buffer (url-retrieve-synchronously
                   (fk-build-tag-get
                    "http://localhost/"
                    'poesie
                    '("folksofancy" . "1")
                    '("folksodatatype" . "xml"))))

(switch-to-buffer (url-retrieve-synchronously
                   (fk-build-get "http://localhost/user.php?folksouser=" 
                                 'gustav
                                 '("folksomytags" . "1")
                                 '("folksosession" . "fb382640769de5c8ae22bdec22835c1c56f3be214a59da9b1beebc836764b559"))))
                                 
                                 

(switch-to-buffer (url-retrieve-synchronously

                    

(url-retrieve-synchronously "http://www.fabula.org/tags/resource.php?folksores=http://www.fabula.org/actualites/article23682.php&folksodatatype=html")


(url-retrieve-synchronously
 (fk-build-tag-get
  fktest-base 'communication '("folksodatatype" . "xml") '("folksofancy" . "1")))
