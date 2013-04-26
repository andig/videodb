# This SQL statement changes the owner of all unowned movies be sure to change
# NEWOWNER to the name of the user who should own the movies.

UPDATE videodata
   SET owner = 'NEWOWNER'
 WHERE owner IS NULL
    OR owner = '';
