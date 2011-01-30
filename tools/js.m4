changecom(`@@@')
define(`LOG',
        ifdef(`DEBUG', `$1'))
define(`CLAG',
        ifdef(`DEBUG', ` */ $1  /* '))
undefine(`format')