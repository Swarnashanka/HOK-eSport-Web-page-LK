-- HOK Esports LK - Complete Database Schema
-- Created for Honor of Kings Sri Lanka eSports Hub

CREATE DATABASE IF NOT EXISTS hok_esports_lk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hok_esports_lk;

-- Admin Users Table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1
);

-- Player Users Table
CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ign VARCHAR(50) NOT NULL,
    full_name VARCHAR(100),
    avatar VARCHAR(255),
    bio TEXT,
    role VARCHAR(50),
    hero_specialties VARCHAR(255),
    team_id INT NULL,
    country VARCHAR(50) DEFAULT 'Sri Lanka',
    total_kills INT DEFAULT 0,
    total_deaths INT DEFAULT 0,
    total_assists INT DEFAULT 0,
    total_matches INT DEFAULT 0,
    total_wins INT DEFAULT 0,
    mvp_count INT DEFAULT 0,
    rank_points INT DEFAULT 0,
    rank_tier VARCHAR(50) DEFAULT 'Bronze',
    is_active TINYINT(1) DEFAULT 1,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Teams Table
CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    logo VARCHAR(255),
    banner VARCHAR(255),
    description TEXT,
    founded_year YEAR,
    captain_id INT NULL,
    wins INT DEFAULT 0,
    losses INT DEFAULT 0,
    tournaments_played INT DEFAULT 0,
    tournaments_won INT DEFAULT 0,
    achievements TEXT,
    social_discord VARCHAR(255),
    social_facebook VARCHAR(255),
    social_youtube VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tournaments Table
CREATE TABLE tournaments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    banner VARCHAR(255),
    format ENUM('single_elimination', 'double_elimination', 'round_robin', 'group_stage') DEFAULT 'single_elimination',
    status ENUM('upcoming', 'registration_open', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    prize_pool_total DECIMAL(12,2) DEFAULT 0.00,
    prize_pool_currency VARCHAR(10) DEFAULT 'LKR',
    prize_distribution TEXT,
    registration_start DATETIME,
    registration_end DATETIME,
    tournament_start DATETIME,
    tournament_end DATETIME,
    max_teams INT DEFAULT 16,
    registered_teams INT DEFAULT 0,
    entry_fee DECIMAL(10,2) DEFAULT 0.00,
    rules TEXT,
    contact_email VARCHAR(100),
    stream_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tournament Registrations
CREATE TABLE tournament_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    team_id INT NULL,
    player_id INT NULL,
    contact_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20),
    team_name VARCHAR(100),
    player_ign VARCHAR(50),
    status ENUM('pending', 'approved', 'rejected', 'withdrawn') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'waived') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
);

-- News & Updates Table
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255),
    category ENUM('match_results', 'patch_updates', 'community', 'international', 'tournament', 'team_news', 'general') DEFAULT 'general',
    author_id INT NOT NULL,
    views INT DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES admin_users(id)
);

-- Gallery Table
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_type ENUM('image', 'video_embed') DEFAULT 'image',
    embed_url VARCHAR(500),
    category ENUM('tournament', 'team', 'player', 'event', 'general') DEFAULT 'general',
    tournament_id INT NULL,
    team_id INT NULL,
    views INT DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE SET NULL,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL
);

-- Match Results Table
CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NULL,
    team1_id INT NULL,
    team2_id INT NULL,
    team1_name VARCHAR(100),
    team2_name VARCHAR(100),
    team1_score INT DEFAULT 0,
    team2_score INT DEFAULT 0,
    winner_team_id INT NULL,
    stage VARCHAR(100),
    match_date DATETIME,
    stream_url VARCHAR(255),
    result_notes TEXT,
    status ENUM('scheduled', 'live', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE SET NULL
);

-- Player Match Stats
CREATE TABLE player_match_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    player_id INT NOT NULL,
    hero_played VARCHAR(100),
    kills INT DEFAULT 0,
    deaths INT DEFAULT 0,
    assists INT DEFAULT 0,
    damage_dealt INT DEFAULT 0,
    is_mvp TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
);

-- Merchandise Table
CREATE TABLE merchandise (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'LKR',
    image VARCHAR(255),
    category VARCHAR(50),
    stock INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    buy_link VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sponsors Table
CREATE TABLE sponsors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    logo VARCHAR(255),
    website VARCHAR(255),
    tier ENUM('title', 'gold', 'silver', 'bronze', 'community') DEFAULT 'bronze',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact Messages Table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('general', 'team_registration', 'sponsorship', 'media', 'other') DEFAULT 'general',
    status ENUM('unread', 'read', 'replied', 'archived') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site Settings Table
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add foreign keys
ALTER TABLE players ADD CONSTRAINT fk_player_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL;
ALTER TABLE teams ADD CONSTRAINT fk_team_captain FOREIGN KEY (captain_id) REFERENCES players(id) ON DELETE SET NULL;

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Default Admin User (password: Admin@123)
INSERT INTO admin_users (username, email, password, full_name, role) VALUES
('admin', 'admin@hokesportslk.com', '$2b$12$8guqpZHlsciDEQOhLxsXXuM69rTXKBMQhJPhpte0VZsCOcstkePOW', 'HOK Admin', 'super_admin');

-- Sample Teams
INSERT INTO teams (name, slug, description, founded_year, wins, losses, achievements) VALUES
('Lanka Lions', 'lanka-lions', 'Sri Lanka''s premier Honor of Kings team, founded by top-ranked players.', 2023, 24, 6, 'HOK SL Season 1 Champions, Regional Qualifier Finalists'),
('Colombo Raiders', 'colombo-raiders', 'Representing Colombo with aggressive playstyle and veteran players.', 2023, 18, 11, 'HOK SL Season 1 Runner-up, Best Team Award 2024'),
('Kandy Kings', 'kandy-kings', 'The highland warriors bringing mountain-strong gameplay.', 2024, 12, 9, 'HOK SL Season 2 Semi-finalists'),
('Galle Storm', 'galle-storm', 'Southern Sri Lanka''s finest, known for strategic gameplay.', 2024, 9, 12, 'HOK SL Season 2 Quarter-finalists');

-- Sample Players
INSERT INTO players (username, email, password, ign, full_name, role, hero_specialties, team_id, total_kills, total_deaths, total_assists, total_matches, total_wins, mvp_count, rank_points, rank_tier) VALUES
('shadowblade99', 'shadow@example.com', '$2b$12$bSXE6P3DXHRvLog7hi.q2ePkKeYeuPgsIlRSOn1QCg3LA32ZX/Lwa', 'ShadowBlade', 'Kasun Perera', 'Jungler', 'Li Bai, Sun Ce, Monkey King', 1, 1240, 380, 890, 89, 67, 24, 4850, 'Grandmaster'),
('dragonknight', 'dragon@example.com', '$2b$12$bSXE6P3DXHRvLog7hi.q2ePkKeYeuPgsIlRSOn1QCg3LA32ZX/Lwa', 'DragonKnight', 'Nuwan Silva', 'EXP Laner', 'Lü Bu, Cao Cao, Dian Wei', 1, 980, 420, 760, 85, 63, 18, 4620, 'Grandmaster'),
('starlord_lk', 'star@example.com', '$2b$12$bSXE6P3DXHRvLog7hi.q2ePkKeYeuPgsIlRSOn1QCg3LA32ZX/Lwa', 'StarLord', 'Dinesh Fernando', 'Mid Laner', 'Zhuge Liang, Wu Zetian, Yang Yuhuan', 1, 1560, 290, 1120, 92, 71, 31, 5200, 'Challenger'),
('ironwall_lk', 'iron@example.com', '$2b$12$bSXE6P3DXHRvLog7hi.q2ePkKeYeuPgsIlRSOn1QCg3LA32ZX/Lwa', 'IronWall', 'Ravi Jayasinghe', 'Gold Laner', 'Hou Yi, Yu Ji, Huang Zhong', 1, 1890, 310, 670, 90, 68, 27, 5050, 'Challenger'),
('guardian_ceylon', 'guardian@example.com', '$2b$12$bSXE6P3DXHRvLog7hi.q2ePkKeYeuPgsIlRSOn1QCg3LA32ZX/Lwa', 'GuardianCeylon', 'Priya Wickrama', 'Support/Roam', 'Zhuang Zhou, Da Qiao, Gao Jianli', 1, 340, 280, 2140, 88, 66, 15, 4780, 'Grandmaster');

-- Sample Tournaments
INSERT INTO tournaments (name, slug, description, format, status, prize_pool_total, prize_pool_currency, prize_distribution, registration_start, registration_end, tournament_start, tournament_end, max_teams, entry_fee, rules) VALUES
('HOK Sri Lanka Championship Season 3', 'hok-sl-championship-s3', 'The biggest Honor of Kings tournament in Sri Lanka. Compete for glory, prize money and a chance to represent Sri Lanka internationally.', 'double_elimination', 'registration_open', 250000.00, 'LKR', '1st: LKR 100,000 | 2nd: LKR 60,000 | 3rd-4th: LKR 30,000 each | 5th-8th: LKR 7,500 each', '2026-04-15 00:00:00', '2026-05-10 23:59:59', '2026-05-18 10:00:00', '2026-06-01 20:00:00', 16, 2500.00, 'Standard HOK tournament rules apply. All players must be Sri Lankan nationals or residents. Teams must have 5 active players and up to 2 substitutes. Check-in is 30 minutes before match time.'),
('HOK Summer Invitational 2026', 'hok-summer-invitational-2026', 'Elite invitational tournament for top 8 teams in Sri Lanka.', 'single_elimination', 'upcoming', 100000.00, 'LKR', '1st: LKR 50,000 | 2nd: LKR 25,000 | 3rd-4th: LKR 12,500 each', '2026-06-01 00:00:00', '2026-06-20 23:59:59', '2026-06-28 14:00:00', '2026-07-06 20:00:00', 8, 0.00, 'Invitation only. Top 8 teams from Season 3 qualify automatically.'),
('HOK Beginner Cup', 'hok-beginner-cup', 'Welcome tournament for new players and teams. Build your competitive career here.', 'round_robin', 'completed', 50000.00, 'LKR', '1st: LKR 25,000 | 2nd: LKR 15,000 | 3rd: LKR 10,000', '2026-02-01 00:00:00', '2026-02-28 23:59:59', '2026-03-08 10:00:00', '2026-03-22 20:00:00', 8, 1000.00, 'Open to players below Diamond rank. Beginner-friendly format.');

-- Sample News
INSERT INTO news (title, slug, excerpt, content, category, author_id, is_featured, views) VALUES
('HOK Sri Lanka Championship Season 3 — Registration Now Open!', 'hok-sl-championship-s3-registration-open', 'Registrations are now live for the biggest tournament in Sri Lanka. LKR 250,000 in prizes up for grabs!', '<p>We are thrilled to announce that registrations are now officially open for the <strong>HOK Sri Lanka Championship Season 3</strong>! This is your chance to compete for LKR 250,000 in prize money and earn the title of Sri Lanka''s best Honor of Kings team.</p><h3>What to Expect</h3><p>Season 3 promises to be bigger and better than ever, featuring 16 teams battling it out in a double elimination format over 3 weekends. All matches will be streamed live.</p><h3>How to Register</h3><p>Head over to our Tournaments page and click "Register Now". Fill in your team details and pay the LKR 2,500 entry fee. Registration closes May 10th.</p>', 'tournament', 1, 1, 1240),
('Lanka Lions Defend Their Title — Season 2 Champions!', 'lanka-lions-season-2-champions', 'Lanka Lions clinch the HOK Sri Lanka Championship Season 2 title in a thrilling best-of-5 grand final.', '<p>In a breathtaking grand final that lasted over 4 hours, <strong>Lanka Lions</strong> defended their championship title against Colombo Raiders, winning 3-2 in a heart-pounding series that had spectators on the edge of their seats.</p><p>StarLord delivered an iconic Zhuge Liang performance in game 5, earning MVP honors and cementing his status as the best mid-laner in Sri Lanka.</p><p>Lanka Lions take home LKR 100,000 and retain the championship trophy.</p>', 'match_results', 1, 1, 3450),
('HOK Global Update — New Hero Zhao Yun Arrives', 'hok-global-update-zhao-yun', 'The legendary warrior Zhao Yun joins the Honor of Kings roster. Here''s everything you need to know.', '<p>Tianguo''s newest hero, <strong>Zhao Yun</strong>, has arrived in Honor of Kings! This high-mobility assassin/warrior hybrid is already shaking up the meta in international servers.</p><h3>Abilities Overview</h3><p>Zhao Yun excels in solo carries and skirmish scenarios. His passive grants bonus movement speed after eliminating enemies, making him a terror in the jungle and EXP lane.</p><p>Sri Lankan players can expect him to arrive in the local server with the next patch update.</p>', 'patch_updates', 1, 0, 890);

-- Sample Gallery
INSERT INTO gallery (title, description, file_type, embed_url, category, is_featured) VALUES
('HOK SL Season 2 Grand Final Highlights', 'Full match highlights from the epic Season 2 Grand Final', 'video_embed', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'tournament', 1),
('Season 2 Opening Ceremony', 'The spectacular opening ceremony of HOK Sri Lanka Season 2', 'video_embed', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'event', 1);

-- Site Settings
INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES
('site_name', 'HOK Esports LK', 'text'),
('site_tagline', 'Forging Sri Lanka''s Kings of Honor', 'text'),
('site_email', 'info@hokesportslk.com', 'email'),
('site_phone', '+94 77 123 4567', 'text'),
('discord_invite', 'https://discord.gg/hokesportslk', 'url'),
('whatsapp_link', 'https://wa.me/94771234567', 'url'),
('facebook_url', 'https://facebook.com/hokesportslk', 'url'),
('youtube_url', 'https://youtube.com/@hokesportslk', 'url'),
('tiktok_url', 'https://tiktok.com/@hokesportslk', 'url'),
('twitter_url', 'https://twitter.com/hokesportslk', 'url'),
('meta_description', 'Official Sri Lankan Honor of Kings eSports Hub — Tournaments, Teams, Players, Rankings and Community', 'text'),
('maintenance_mode', '0', 'boolean'),
('registration_enabled', '1', 'boolean');

-- Sample Merchandise
INSERT INTO merchandise (name, description, price, currency, category, stock, buy_link) VALUES
('HOK Esports LK Jersey', 'Official team jersey with premium sublimation print. Gold and dark red design.', 3500.00, 'LKR', 'Apparel', 50, '#'),
('HOK Mousepad XL', 'Extra large gaming mousepad (90x40cm) with HOK Esports LK branding.', 1800.00, 'LKR', 'Accessories', 30, '#'),
('HOK Cap', 'Premium snapback cap with embroidered lion crest.', 1500.00, 'LKR', 'Apparel', 40, '#'),
('HOK Sticker Pack', 'Pack of 10 premium vinyl stickers — logos, heroes, and team designs.', 500.00, 'LKR', 'Accessories', 100, '#');

-- Sample Sponsors
INSERT INTO sponsors (name, website, tier, sort_order) VALUES
('Lionair Tech', 'https://example.com', 'gold', 1),
('Ceylon Gaming Hub', 'https://example.com', 'silver', 2),
('Lanka PC World', 'https://example.com', 'bronze', 3);

-- Sample Matches
INSERT INTO matches (tournament_id, team1_name, team2_name, team1_score, team2_score, winner_team_id, stage, match_date, status) VALUES
(3, 'Lanka Lions', 'Colombo Raiders', 3, 2, 1, 'Grand Final', '2026-03-22 18:00:00', 'completed'),
(3, 'Lanka Lions', 'Kandy Kings', 2, 0, 1, 'Semi Final', '2026-03-15 16:00:00', 'completed'),
(3, 'Colombo Raiders', 'Galle Storm', 2, 1, 2, 'Semi Final', '2026-03-15 19:00:00', 'completed');
