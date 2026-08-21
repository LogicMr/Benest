USE benest;
UPDATE users
SET password_hash = '$2y$10$JWpsHLkvTInphSzQ8mPDQeTiKnXyJfR/spo78BJXnv6IcME4I0wSS', status = 'active'
WHERE email = 'admin@benest.local';