-- Seed a staff/admin account. Log in normally through /subscribe with this
-- mobile number (OTP flow) — the pre-existing row keeps role='admin'.
-- Change the mobile number before any real deployment.

INSERT INTO users (id, mobile_number, operator, name, role, daily_goal_count, created_at)
VALUES (UUID(), '01700000000', 'airtel', 'Admin', 'admin', 5, NOW())
ON DUPLICATE KEY UPDATE role = 'admin';
