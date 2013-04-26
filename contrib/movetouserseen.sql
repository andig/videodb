# This is for migrating from the old 'seen' status to the new userbased
# one. Replace USERNAME with your username

INSERT INTO userseen
SELECT 'USERNAME', id
FROM `videodata`
WHERE seen =1