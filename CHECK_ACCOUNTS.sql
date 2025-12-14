-- Проверка всех ServiceAccounts
SELECT 
    id,
    service_id,
    profile_id,
    is_active,
    JSON_EXTRACT(credentials, '$.cookies') as has_cookies,
    JSON_LENGTH(credentials, '$.cookies') as cookies_count,
    created_at
FROM service_accounts
WHERE service_id = 1
ORDER BY id DESC;

