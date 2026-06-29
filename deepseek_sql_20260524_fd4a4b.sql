-- =============================================
-- IDM KNOWLEDGE HUB - COMPLETE DATABASE
-- i-Discourse Mehfil (IDM) Knowledge Hub
-- Database Name: u767322683_Idiscourse
-- Created: May 2026
-- =============================================

-- =============================================
-- CREATE DATABASE (if not exists)
-- =============================================
CREATE DATABASE IF NOT EXISTS `u767322683_Idiscourse`;
USE `u767322683_Idiscourse`;

-- =============================================
-- Drop tables in correct order (disable foreign key checks)
-- =============================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `post_views`;
DROP TABLE IF EXISTS `post_categories`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `categories`;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- TABLE: users (Scholars, Admins, Readers)
-- =============================================
CREATE TABLE `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('admin', 'scholar', 'reader') DEFAULT 'reader',
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `institution` VARCHAR(150) DEFAULT NULL,
    `specialization` VARCHAR(100) DEFAULT NULL,
    `malaysia_state` VARCHAR(50) DEFAULT NULL,
    `google_scholar` VARCHAR(255) DEFAULT NULL,
    `researchgate` VARCHAR(255) DEFAULT NULL,
    `display_order` INT(11) DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: categories
-- =============================================
CREATE TABLE `categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name_en` VARCHAR(50) NOT NULL,
    `name_bm` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: posts (Articles, Publications)
-- =============================================
CREATE TABLE `posts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `title_bm` VARCHAR(255) DEFAULT NULL,
    `content` LONGTEXT NOT NULL,
    `content_bm` LONGTEXT DEFAULT NULL,
    `excerpt` TEXT DEFAULT NULL,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `post_type` ENUM('article', 'fatwa', 'tafsir', 'hadith_study', 'fiqh', 'usuluddin', 'islamic_finance', 'halal_research', 'book_chapter', 'journal', 'lecture', 'findings') DEFAULT 'article',
    `status` ENUM('draft', 'published', 'under_review') DEFAULT 'draft',
    `author_id` INT(11) DEFAULT NULL,
    `views` INT(11) DEFAULT 0,
    `unique_views` INT(11) DEFAULT 0,
    `references_text` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FULLTEXT KEY `search_idx` (`title`, `content`, `excerpt`),
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: post_views (Track unique views)
-- =============================================
CREATE TABLE `post_views` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `post_id` INT(11) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `viewed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_agent` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_post_ip` (`post_id`, `ip_address`),
    KEY `idx_viewed_at` (`viewed_at`),
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: post_categories (Junction table)
-- =============================================
CREATE TABLE `post_categories` (
    `post_id` INT(11) NOT NULL,
    `category_id` INT(11) NOT NULL,
    PRIMARY KEY (`post_id`, `category_id`),
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INSERT CATEGORIES
-- =============================================
INSERT INTO `categories` (`id`, `name_en`, `name_bm`, `slug`, `description`, `icon`) VALUES
(1, 'Tafsir Studies', 'Kajian Tafsir', 'tafsir-studies', 'Quranic exegesis and interpretation', '📖'),
(2, 'Hadith Sciences', 'Ulum Hadith', 'hadith-sciences', 'Study of prophetic traditions', '📜'),
(3, 'Fiqh', 'Fiqh', 'fiqh', 'Islamic jurisprudence and legal rulings', '⚖️'),
(4, 'Islamic Finance', 'Kewangan Islam', 'islamic-finance', 'Shariah-compliant economics', '💰'),
(5, 'General Information', 'Maklumat Am', 'general-information', 'General knowledge and information across various topics', 'ℹ️');

-- =============================================
-- INSERT ADMIN USER (Imtiyaz - Super Admin)
-- Password: password
-- Email: imtiyaz@idiscourse.my
-- =============================================
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `institution`, `bio`, `display_order`) VALUES
(1, 'imtiyaz', 'imtiyaz@idiscourse.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Imtiyaz', 'admin', 'i-Discourse Mehfil (IDM)', 'Super Administrator and Founder of i-Discourse Mehfil Knowledge Hub. Responsible for platform management and oversight.', 0);

-- =============================================
-- INSERT SCHOLARS
-- =============================================

-- Scholar 1: Prof. Dr. Mohd Mumtaz Ali
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `profile_image`, `bio`, `institution`, `specialization`, `malaysia_state`, `google_scholar`, `researchgate`, `display_order`) VALUES
(2, 'mumtaz_ali', 'mumtazali@iium.edu.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Dr. Mohd Mumtaz Ali', 'scholar', 'uploads/mumtaz-ali.jpg', 'Professor Dr. Muhammad Mumtaz Ali was born in Hyderabad, India in 1955 and started his career as an Assistant Professor in International Islamic University Malaysia (IIUM) in 1987. Since then, he has been serving in the same University. Currently, he is in the Department of Usul al-Din and Comparative Religion, Kulliyyah of Islamic Revealed Knowledge and Human Sciences.

He has produced several textbooks from Islamic perspectives. His books on Islamization of Knowledge are considered as bestseller books in the campus. In 2014, he was awarded the National Book Award for his book, "Issues in Islamization of Human Knowledge: Civilization Building Discourse of Contemporary Muslim Thinkers".

He is the author of several books including: Islam and the Western Philosophy of Knowledge, The Philosophy of Science: Western and Islamic Perspectives, Islamic Critical Thinking, and The History and Philosophy of Islamization of Knowledge.', 'International Islamic University Malaysia (IIUM) - AbdulHamid AbuSulayman Kulliyyah of Islamic Revealed Knowledge and Human Sciences', 'Usul al-Din and Comparative Religion | Islamization of Knowledge', 'Selangor (Gombak)', 'https://scholar.google.com/', 'https://www.researchgate.net/profile/Mohd-Mumtaz-Ali-2', 1);

-- Scholar 2: Associate Professor Dr. Arshad Islam
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `profile_image`, `bio`, `institution`, `specialization`, `malaysia_state`, `display_order`) VALUES
(3, 'arshad_islam', 'arshad.islam@iium.edu.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Associate Professor Dr. Arshad Islam', 'scholar', 'uploads/arshad-islam.jpg', 'Associate Professor Dr. Arshad Islam is a prominent academic at the International Islamic University Malaysia (IIUM). He has been a faculty member in the Department of History and Civilization within the AHAS Kulliyyah of Islamic Revealed Knowledge and Human Sciences since 1991.

He holds degrees from Gorakhpur University, Aligarh Muslim University (India), and IIUM. His research specialization includes Southeast Asian history, Islamic history and civilization, History of Science, and the Malay Sultanates.

He is the author of numerous publications, including "Islam in Sindh" and papers on Pan-Islamic cooperation, Japanese occupation in Sabah, and Islamic scientific contributions during the Middle Abbasid Period.', 'International Islamic University Malaysia (IIUM) - AHAS Kulliyyah of Islamic Revealed Knowledge and Human Sciences', 'Southeast Asian History | Islamic History & Civilization | History of Science | Malay Sultanates', 'Selangor (Gombak)', 2);

-- Scholar 3: Khalid Noor Mohammed
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `profile_image`, `bio`, `institution`, `specialization`, `malaysia_state`, `display_order`) VALUES
(4, 'khalid_noor', 'khalid.noor@iium.edu.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Khalid Noor Mohammed', 'scholar', 'uploads/khalid.jpg', 'Khalid Noor Mohammed is an academic and researcher based in Cyberjaya, Selangor, Malaysia, associated with the International Islamic University Malaysia. He holds a postgraduate degree and is an active researcher focusing on historical studies of Indian Muslims, contemporary project management, and technology trends.

His research interests include:
• Historical studies of Indian Muslims - examining the rich heritage, contributions, and challenges faced by the Muslim community in India
• Contemporary project management - applying modern methodologies to academic and institutional projects
• Technology trends - analyzing emerging technologies and their impact on education and society

Khalid combines his academic background with practical research experience, contributing to the scholarly discourse on Muslim history and contemporary management practices.', 'International Islamic University Malaysia (IIUM), Cyberjaya Campus', 'Historical Studies of Indian Muslims | Project Management | Technology Trends', 'Selangor (Cyberjaya)', 3);

-- Scholar 4: Mahboob Ilahi
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `profile_image`, `bio`, `institution`, `specialization`, `malaysia_state`, `display_order`) VALUES
(5, 'mahboob_ilahi', 'mahboob.ilahi@idiscourse.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mahboob Ilahi', 'scholar', 'uploads/mahboobali.jpg', 'Mahboob Ilahi is a PMP-certified Project Engineering professional with over 25 years of extensive experience in the engineering sector. He is based in Kuala Lumpur, Malaysia.

His expertise includes offshore structural engineering, risk-based integrity management for FLNG hull structures, and project management. He has worked on significant projects such as FPSO Anita Garibaldi MV33, structural life extension studies for EnQuest, and concept feasibility for the YME Redevelopment project.

He holds a Project Management Professional (PMP) certification from the Project Management Institute. He is a lifetime member of the Institute of Engineers (India) and a member of PMI.

Beyond his professional work, Mahboob has been involved in volunteer efforts, including fundraising for disaster relief in Uttarakhand (2013) and serving on parent-teacher committees at the International Islamic School Malaysia.', 'Larsen & Toubro / Independent Professional', 'Project Engineering Management | Offshore Structural Engineering | Risk Management', 'Wilayah Persekutuan Kuala Lumpur', 4);

-- =============================================
-- INSERT SAMPLE PUBLICATIONS
-- =============================================

-- Article 1: Khalid Noor Mohammed - Why Standing in Arafat is Not Mandated in Umrah
INSERT INTO `posts` (`id`, `title`, `content`, `excerpt`, `post_type`, `status`, `author_id`, `views`, `references_text`, `created_at`) VALUES
(1, 'Why Standing in Arafat is Not Mandated in Umrah', 
'<div style="background: #f0f7f0; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-right: 4px solid #2d5a3b;">
<p style="margin: 0; font-style: italic;">"Hajj consists of two pillars: standing at Arafah, and Tawaf al-Ziyarat, followed by Sa''aee. Umrah is the lesser Hajj."</p>
</div>

<h2>Introduction</h2>
<p>Umrah, often referred to as the "lesser pilgrimage" or "minor Hajj," holds great spiritual significance in Islam. However, unlike Hajj, it does not require the pilgrim to perform the standing (wuqoof) at Arafat. This distinction has led many to wonder about the underlying wisdom behind this difference in legislation.</p>

<p>Shah Wali Allah al-Dihlawi (may Allah have mercy on him), one of the most distinguished Islamic scholars of the Indian subcontinent, provides profound insights into this question. His analysis, preserved in his monumental work <em>Hujjatullah al-Baligha</em>, offers a deep understanding of the divine wisdom in Islamic jurisprudence.</p>

<h2>The Wisdom According to Shah Wali Allah</h2>
<p>Explaining the reason why the Standing in Arafah is not legislated for those performing the Umrah, Shah Wali Allah mentions the following profound reasons:</p>

<h3>1. The Absence of a Specified Time</h3>
<p>Umrah is not performed at a specific time, unlike the Hajj, which is performed on specific days in the year. If Umrah pilgrims were to be asked to visit Arafah, many would be left standing alone. The very essence of wuqoof at Arafat lies in the collective gathering of millions of pilgrims on a single, divinely ordained day—the 9th of Dhul Hijjah.</p>

<h3>2. The Distinction Between Hajj and Umrah Would Blur</h3>
<p>"And if a date be specified, then that will make it Hajj." This is a critical observation. If a specific date were assigned for standing at Arafat during Umrah, it would effectively transform Umrah into Hajj.</p>

<h3>3. The Burden of Multiple Gatherings</h3>
<p>"And if two gatherings were to be mandated per year, the difficulty is not hidden from anyone." This reflects Islam''s core principle of removing hardship.</p>

<h2>The Primary Purpose of Umrah</h2>
<p>The purpose of Umrah is specifically to glorify the House of Allah and to thank Allah for His blessings. Umrah focuses on glorifying the Ka''bah through Tawaf and emphasizes gratitude for Allah''s countless blessings.</p>

<hr />
<p><strong>Source:</strong> Abridged translation from <em>Rahmatullah al-Wasiya</em>, Vol. 4, by Mufti Saeed Palanpuri, being a commentary on Shah Wali Allah''s <em>Hujjatullah al-Baligha</em>.</p>',
'An insightful analysis on the profound wisdom behind why standing at Arafat is not mandated in Umrah, based on the teachings of Shah Wali Allah al-Dihlawi.',
'article', 'published', 4, 245, 'Shah Wali Allah al-Dihlawi, Hujjatullah al-Baligha', NOW() - INTERVAL 5 DAY);

-- Article 2: Khalid Noor Mohammed - Relevance of Mohammed Ali Jauhar
INSERT INTO `posts` (`id`, `title`, `content`, `excerpt`, `post_type`, `status`, `author_id`, `views`, `references_text`, `created_at`) VALUES
(2, 'Relevance of Mohammed Ali Jauhar (d. 1931) to Our Times',
'<h2>Introduction</h2>
<p>Mohamed Ali''s main grievance was that Gandhi, whom he had only just described as "the most Christ-like man of our times", gave a free hand to the "Lala-Malaviya gang" to pursue the goal of a Hindu Rashtra. The Congress, according to him, was not a national but a Hindu party, unprepared to condemn Hindu fanatics, and unprepared to work towards the creation of a secular society.</p>

<p>Gandhi, with whom he worked for ten years through thick and thin, was keen to retain his popularity with the "Hindus" and, for this reason, reluctant to resolve the Hindu-Muslim deadlock.</p>

<h2>The Pseudo-Nationalists Critique</h2>
<p>At the same time, the "pseudo-nationalists", he wrote in the Comrade on 17 July 1924, talk and write as nationalists and run down communals; but only in the use of counters and catchwords of nationalism are they nationalists for their hearts are narrow and they can conceive of no future for India except it be one of Hindu dominance and the existence of the Musalmans as a minority living on the sufferance of the Hindu majority.</p>

<p>It is my sad conviction that not one of these pseudo-nationalists would have talked so glibly of nationalism, majority rule and mixed electorates, if his own community had not been in the safe position of an overwhelming majority.</p>

<h2>The Cow Question as a Test Case</h2>
<p>He went on to write that the Cow question provides the best topic for the exposure of their pseudo-nationalism, for in the name of nationalism they make demands on their fellow-countrymen so absurd that none has ever heard of them in any other country or nation in the world.</p>

<hr />
<p><em>Excerpted from Mushirul Hasan''s "Introduction" to Maulana Mohamed Ali. My Life: A Fragment. [Delhi, 1999]</em></p>',
'Mohamed Ali''s critique of pseudo-nationalism and the Hindu-Muslim deadlock in colonial India remains profoundly relevant today. This article examines his warnings about majoritarianism and the importance of secular governance.',
'article', 'published', 4, 189, 'Mushirul Hasan, Introduction to Maulana Mohamed Ali. My Life: A Fragment. Delhi, 1999', NOW() - INTERVAL 4 DAY);

-- Article 3: Prof. Dr. Mohd Mumtaz Ali - Islamization of Knowledge
INSERT INTO `posts` (`id`, `title`, `content`, `excerpt`, `post_type`, `status`, `author_id`, `views`, `created_at`) VALUES
(3, 'Islamization of Knowledge: A Civilizational Discourse',
'<h2>Introduction</h2>
<p>The Islamization of knowledge represents a significant intellectual movement that seeks to integrate Islamic principles with modern disciplines. This discourse emerged as a response to the secularization of knowledge and the marginalization of religious values in education.</p>

<h2>The Need for Islamization</h2>
<p>The contemporary Muslim world faces a crisis of knowledge characterized by the bifurcation between revealed knowledge and acquired sciences. This separation has led to a loss of Islamic identity in educational institutions and a weakening of moral foundations.</p>

<h2>Key Thinkers and Contributions</h2>
<p>Pioneering scholars such as Ismail al-Faruqi, Syed Muhammad Naquib al-Attas, and AbdulHamid AbuSulayman have laid the foundations for this intellectual movement. Their works emphasize the importance of tawhid as the organizing principle for all knowledge.</p>

<h2>Methodological Approaches</h2>
<p>The Islamization process requires a critical examination of existing disciplines, identifying elements that conflict with Islamic principles, and developing alternative frameworks that integrate revealed truths with rational inquiry.</p>

<h2>Conclusion</h2>
<p>The Islamization of knowledge is not merely an academic exercise but a civilizational imperative that can restore the intellectual vitality of the Muslim ummah and contribute positively to global civilization.</p>',
'An overview of the Islamization of knowledge movement, its key thinkers, and its importance for contemporary Muslim civilization.',
'journal', 'published', 2, 156, NOW() - INTERVAL 7 DAY);

-- Article 4: Dr. Arshad Islam - Islamic Scientific Contributions
INSERT INTO `posts` (`id`, `title`, `content`, `excerpt`, `post_type`, `status`, `author_id`, `views`, `created_at`) VALUES
(4, 'Islamic Scientific Contributions During the Middle Abbasid Period',
'<h2>Introduction</h2>
<p>The Middle Abbasid period (9th-11th centuries) witnessed unprecedented advancements in Islamic scientific traditions. This paper examines key contributions in astronomy, medicine, mathematics, and optics.</p>

<h2>Key Scholars and Their Contributions</h2>
<ul>
<li><strong>Al-Khwarizmi</strong> - Father of algebra, introduced the concept of algorithms</li>
<li><strong>Ibn al-Haytham</strong> - Pioneered scientific method and optics</li>
<li><strong>Al-Razi</strong> - Distinguished physician and medical scholar</li>
<li><strong>Al-Biruni</strong> - Master of astronomy and geography</li>
</ul>

<h2>Impact on European Renaissance</h2>
<p>Islamic scientific works were translated into Latin and became foundational texts in European universities, influencing scholars such as Roger Bacon and Copernicus.</p>

<h2>Conclusion</h2>
<p>The scientific legacy of Islamic civilization represents a golden era that preserved, developed, and transmitted knowledge across cultures, laying the groundwork for the modern scientific revolution.</p>',
'An exploration of scientific achievements during the Golden Age of Islam, highlighting key scholars and their contributions to world civilization.',
'journal', 'published', 3, 134, NOW() - INTERVAL 6 DAY);

-- Article 5: Prof. Dr. Mohd Mumtaz Ali - Islamic Critical Thinking
INSERT INTO `posts` (`id`, `title`, `content`, `excerpt`, `post_type`, `status`, `author_id`, `views`, `created_at`) VALUES
(5, 'Islamic Critical Thinking: Foundations and Principles',
'<h2>Introduction</h2>
<p>Critical thinking is deeply embedded in Islamic tradition. The Quran repeatedly calls upon believers to reflect, reason, and use their intellect to understand the signs of Allah in the universe and within themselves.</p>

<h2>Quranic Basis for Critical Thinking</h2>
<p>Verses such as "Do they not reflect upon the Quran?" (4:82) and "Indeed, in that are signs for a people who give thought" (13:3) establish reflection as a religious obligation.</p>

<h2>Principles of Islamic Critical Thinking</h2>
<ul>
<li>Tawhidic worldview as the foundation</li>
<li>Integration of revelation and reason</li>
<li>Verification of information (tabayyun)</li>
<li>Dialectical reasoning (jadal)</li>
<li>Consensus (ijma) as a validating mechanism</li>
</ul>

<h2>Application in Contemporary Education</h2>
<p>Islamic critical thinking can address contemporary challenges such as extremism, materialism, and moral relativism by providing a framework for balanced judgment and ethical reasoning.</p>',
'An exploration of critical thinking within Islamic tradition, its Quranic foundations, and its application in contemporary education and society.',
'article', 'published', 2, 98, NOW() - INTERVAL 3 DAY);

-- =============================================
-- LINK POSTS TO CATEGORIES
-- =============================================
INSERT INTO `post_categories` (`post_id`, `category_id`) VALUES 
(1, 1), (1, 3),
(2, 5),
(3, 3), (3, 5),
(4, 1), (4, 2),
(5, 3), (5, 5);

-- =============================================
-- INSERT SAMPLE POST VIEWS (for testing)
-- =============================================
INSERT INTO `post_views` (`post_id`, `ip_address`, `user_agent`, `viewed_at`) VALUES
(1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', NOW() - INTERVAL 2 DAY),
(1, '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)', NOW() - INTERVAL 1 DAY),
(2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', NOW() - INTERVAL 3 DAY),
(3, '192.168.1.50', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NOW() - INTERVAL 1 DAY),
(4, '10.0.0.1', 'Mozilla/5.0 (Linux; Android 11; SM-G991B)', NOW() - INTERVAL 2 DAY);

-- =============================================
-- VERIFY ALL DATA
-- =============================================
SELECT '===========================================' AS '';
SELECT 'IDM KNOWLEDGE HUB DATABASE SETUP COMPLETE' AS '';
SELECT '===========================================' AS '';

SELECT '=== USERS TABLE ===' AS '';
SELECT id, full_name, email, role, display_order FROM users ORDER BY display_order;

SELECT '=== POSTS TABLE ===' AS '';
SELECT id, title, post_type, status, author_id, views FROM posts;

SELECT '=== CATEGORIES TABLE ===' AS '';
SELECT id, name_en, name_bm, icon FROM categories;

SELECT '=== POST_CATEGORIES LINKS ===' AS '';
SELECT pc.post_id, p.title, c.name_en as category 
FROM post_categories pc 
JOIN posts p ON pc.post_id = p.id 
JOIN categories c ON pc.category_id = c.id;

SELECT '===========================================' AS '';
SELECT '✅ DATABASE IS READY FOR IDM KNOWLEDGE HUB!' AS message;
SELECT '===========================================' AS '';

-- =============================================
-- LOGIN CREDENTIALS SUMMARY
-- =============================================
SELECT '===========================================' AS '';
SELECT '🔑 LOGIN CREDENTIALS' AS '';
SELECT '===========================================' AS '';
SELECT 'Admin:' AS '';
SELECT 'Email: imtiyaz@idiscourse.my | Password: password' AS '';
SELECT '-------------------------------------------' AS '';
SELECT 'Scholars:' AS '';
SELECT '1. mumtazali@iium.edu.my | Password: password' AS '';
SELECT '2. arshad.islam@iium.edu.my | Password: password' AS '';
SELECT '3. khalid.noor@iium.edu.my | Password: password' AS '';
SELECT '4. mahboob.ilahi@idiscourse.my | Password: password' AS '';
SELECT '===========================================' AS '';