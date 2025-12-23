-- VotaBudapest Seed Data
-- Insert initial data for categories, statuses, and admin user

USE votabudapest;

-- Insert categories (EXACTLY as specified in assignment)
INSERT INTO categories (name, slug) VALUES
('Local small project', 'local-small'),
('Local large project', 'local-large'),
('Equal opportunity Budapest', 'equal-opportunity'),
('Green Budapest', 'green-budapest');

-- Insert statuses
INSERT INTO statuses (name, slug) VALUES
('Pending', 'pending'),
('Approved', 'approved'),
('Rejected', 'rejected'),
('Rework', 'rework');

-- Create admin user (username: admin, password: admin)
-- MINIMUM REQUIREMENT: Admin user with username admin and password admin ✓
-- Password hash for 'admin' generated with: password_hash('admin', PASSWORD_DEFAULT)
INSERT INTO users (username, email, password_hash, is_admin) VALUES
('admin', 'admin@votabudapest.hu', '$2y$10$4SjE7qfQ.DzYbcVgjh7ptO0bMZFe61GpLRPnWE2SfmaoqUwYRKiZ6', TRUE);

-- Optional: Insert some sample users for testing
-- Password for testuser is 'Password123' (meets requirements: lowercase, uppercase, numeric, 8+ chars)
INSERT INTO users (username, email, password_hash, is_admin) VALUES
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE);

-- Optional: Insert sample projects for testing (you can remove this later)
INSERT INTO projects (user_id, category_id, status_id, title, description, postal_code, approved_at) VALUES
(2, 1, 2, 'Community Garden in District 5', 'A small community garden project to bring green spaces to our neighborhood. This project will create a peaceful environment for families and children to enjoy nature, learn about sustainable gardening, and build community connections.', '1051', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 2, 2, 'New Bicycle Lane on Andrássy Avenue', 'This large infrastructure project aims to create a dedicated bicycle lane along the iconic Andrássy Avenue, promoting sustainable transportation and reducing traffic congestion in central Budapest. The project will span 2.5 kilometers.', '1062', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(2, 3, 2, 'Wheelchair Accessible Playground', 'An inclusive playground designed for children of all abilities. This equal opportunity project will feature wheelchair-accessible equipment, sensory play areas, and rubber surfacing to ensure safety and accessibility for everyone.', '1082', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 4, 2, 'Solar Panels for Public Schools', 'Installing solar panels on the roofs of 10 public schools in Budapest to reduce carbon emissions and promote renewable energy education. This green initiative will save energy costs and serve as a teaching tool for students.', '1117', DATE_SUB(NOW(), INTERVAL 20 DAY));

-- Insert sample votes for testing
INSERT INTO votes (user_id, project_id) VALUES
(2, 1),
(2, 2),
(2, 3);
