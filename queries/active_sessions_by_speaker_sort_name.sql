SELECT 
  u.sort_last_name AS user_sort_last_name,
  s.id AS session_id,
  s.name AS session_name,
  s.session_start,
  s.session_end,
  s.session_type,
  s.session_subtype,
  s.description,
  u.name AS user_name,
  u.email AS user_email,
  u.url AS user_url,
  u.avatar AS user_avatar
FROM 
  active_session s
LEFT JOIN
  role r ON r.sessionid = s.id
LEFT JOIN
  user u ON u.id = r.userid
WHERE
  r.usertype = 'speaker'
ORDER BY u.sort_last_name, s.session_start;