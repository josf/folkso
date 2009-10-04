(require 'cl)

(setq fktest-base "http://www.fabula.org/tags/")
(setq fktest-resources 
      '((rembrandt . "http://www.fabula.org/actualites/article13644.php")
        (numero3 . "http://www.fabula.org/actualites/article22002.php")))
(defun fk-build-resource-get (base key &rest apairs)
  (let ((res-and-base (concat 
                       base "resource.php?folksores=" (cdr (assoc key fktest-resources)))))
        (if (null apairs)
            res-and-base
          (concat res-and-base 
                  (loop for pair in apairs concat 
                        (concat "&" (car pair) "=" (cdr pair)))))))

(setq fktest-tags
      '((number . "8170")))
        


(defun fk-build-tag-get (base key &rest apairs)
  (let ((res-and-base (concat 
                       base "tag.php?folksotag=" (cdr (assoc key fktest-tags)))))
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

(url-retrieve-synchronously
 (fk-build-tag-get
  "http://localhost/" 'number '("folksorelated" . "1")))

(url-retrieve-synchronously "http://www.fabula.org/tags/resource.php?folksores=http://www.fabula.org/actualites/article23682.php&folksodatatype=html")

