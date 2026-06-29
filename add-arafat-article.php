<?php
require_once 'config/database.php';

// Check if Khalid exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'khalid.noor@iium.edu.my'");
$stmt->execute();
$khalid = $stmt->fetch();

if (!$khalid) {
    die("Error: Khalid Noor Mohammed not found in database. Please add him first.");
}

$author_id = $khalid['id'];

// Check if article already exists
$stmt = $pdo->prepare("SELECT id FROM posts WHERE title = 'Why Standing in Arafat is Not Mandated in Umrah'");
$stmt->execute();
$existing = $stmt->fetch();

if ($existing) {
    echo "Article already exists!<br>";
    echo "<a href='view-post.php?id=" . $existing['id'] . "'>View existing article</a>";
    exit;
}

$title = "Why Standing in Arafat is Not Mandated in Umrah";
$content = '<div style="background: #f0f7f0; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-right: 4px solid #2d5a3b;">
    <p style="margin: 0; font-style: italic;">"Hajj consists of two pillars: standing at Arafah, and Tawaf al-Ziyarat, followed by Sa\'aee. Umrah is the lesser Hajj."</p>
</div>

<h2>Introduction</h2>

<p>Umrah, often referred to as the "lesser pilgrimage" or "minor Hajj," holds great spiritual significance in Islam. However, unlike Hajj, it does not require the pilgrim to perform the standing (wuqoof) at Arafat. This distinction has led many to wonder about the underlying wisdom behind this difference in legislation.</p>

<p>Shah Wali Allah al-Dihlawi (may Allah have mercy on him), one of the most distinguished Islamic scholars of the Indian subcontinent, provides profound insights into this question. His analysis, preserved in his monumental work <em>Hujjatullah al-Baligha</em>, offers a deep understanding of the divine wisdom in Islamic jurisprudence.</p>

<h2>The Core Distinction Between Hajj and Umrah</h2>

<p>To understand why standing at Arafat is not mandated in Umrah, we must first recognize the fundamental differences between the two pilgrimages:</p>

<ul>
    <li><strong>Hajj</strong> is performed at a specific time of the year (the month of Dhul Hijjah, particularly the 9th-13th days)</li>
    <li><strong>Umrah</strong> can be performed at any time throughout the year</li>
    <li><strong>Hajj</strong> requires multiple rituals including standing at Arafat, spending nights at Muzdalifah and Mina</li>
    <li><strong>Umrah</strong> consists primarily of Tawaf (circumambulation) and Sa\'i (walking between Safa and Marwah)</li>
</ul>

<h2>The Wisdom According to Shah Wali Allah</h2>

<p>Explaining the reason why the Standing in Arafah is not legislated for those performing the Umrah, Shah Wali Allah (may Allah have mercy on him) mentions the following profound reasons:</p>

<h3>1. The Absence of a Specified Time</h3>

<p>Umrah is not performed at a specific time, unlike the Hajj, which is performed on specific days in the year. If Umrah pilgrims were to be asked to visit Arafah (and since the days for the wuqoof are not specified other than on the 9th of Dhul Hijjah), many would be left standing alone. The natural question arises: what is the benefit of such isolated standing?</p>

<p>The very essence of wuqoof at Arafat lies in the collective gathering of millions of pilgrims on a single, divinely ordained day—the 9th of Dhul Hijjah. This unified standing symbolizes the unity of the Muslim ummah and its equal standing before Allah. Removing it from this specific context would diminish its spiritual impact and purpose.</p>

<h3>2. The Distinction Between Hajj and Umrah Would Blur</h3>

<p>Shah Wali Allah continues: "And if a date be specified, then that will make it Hajj." This is a critical observation. If a specific date were assigned for standing at Arafat during Umrah, it would effectively transform Umrah into Hajj, or create a second Hajj, which would contradict the divine wisdom of having only one obligatory annual pilgrimage.</p>

<h3>3. The Burden of Multiple Gatherings</h3>

<p>"And if two gatherings were to be mandated per year, the difficulty (that two gatherings would cause to people) is not hidden from anyone." This reflects Islam\'s core principle of removing hardship (رفع الحرج). The logistical, financial, and physical challenges of organizing two major pilgrimages requiring wuqoof would impose undue burden on the Muslim community worldwide.</p>

<h2>The Primary Purpose of Umrah</h2>

<p>Shah Wali Allah concludes by articulating the essential purpose of Umrah: "The purpose of Umrah is specifically to glorify the House of Allah and to thank Allah for His blessings."</p>

<p>This beautiful articulation reminds us that:</p>
<ul>
    <li>Umrah focuses on <strong>glorifying the Ka\'bah</strong>—the House of Allah—through Tawaf, which is its central ritual</li>
    <li>Umrah emphasizes <strong>gratitude</strong> (shukr) for Allah\'s countless blessings</li>
    <li>Umrah serves as an act of devotion accessible throughout the year, allowing believers to maintain a continuous connection with the Sacred House</li>
</ul>

<h2>Practical Implications</h2>

<p>Understanding this wisdom has important practical benefits for Muslims:</p>

<ul>
    <li><strong>Accessibility</strong>: Muslims can perform Umrah at any time of the year, spreading the spiritual benefits and preventing overcrowding during a single period</li>
    <li><strong>Affordability</strong>: The absence of wuqoof requirements makes Umrah more affordable and logistically simpler than Hajj</li>
    <li><strong>Continuous Devotion</strong>: Believers can maintain a year-round connection with the Ka\'bah through repeated Umrah performances</li>
    <li><strong>Preparation for Hajj</strong>: For many, Umrah serves as a spiritual preparation and rehearsal before undertaking the greater pilgrimage of Hajj</li>
</ul>

<h2>Conclusion</h2>

<p>The divine wisdom in not mandating the standing at Arafat for Umrah reflects Islam\'s beautiful balance between spiritual aspiration and practical reality. Shah Wali Allah\'s analysis demonstrates how Islamic legislation considers time, community capacity, and the preservation of each ritual\'s unique spiritual essence.</p>

<p>While Hajj brings the ummah together in a magnificent annual gathering at Arafat, Umrah offers a continuous opportunity throughout the year to glorify Allah\'s House and express gratitude for His blessings. Both pilgrimages serve their distinct purposes, each perfect in its design and profound in its spiritual impact.</p>

<hr />

<p><strong>Source:</strong> Abridged translation from <em>Rahmatullah al-Wasiya</em>, Vol. 4, by Mufti Saeed Palanpuri, being a commentary on Shah Wali Allah\'s <em>Hujjatullah al-Baligha</em>.</p>';

$excerpt = "An insightful analysis by Khalid Noor Mohammed on the profound wisdom behind why standing at Arafat is not mandated in Umrah, based on the teachings of Shah Wali Allah al-Dihlawi.";

$stmt = $pdo->prepare("INSERT INTO posts (title, content, excerpt, post_type, status, author_id, references_text) VALUES (?, ?, ?, 'article', 'published', ?, ?)");

if ($stmt->execute([$title, $content, $excerpt, $author_id, 'Shah Wali Allah al-Dihlawi, Hujjatullah al-Baligha; Abridged translation from Rahmatullah al-Wasiya, Vol. 4, by Mufti Saeed Palanpuri'])) {
    $post_id = $pdo->lastInsertId();
    echo "✅ Article added successfully!<br>";
    echo "<a href='view-post.php?id=" . $post_id . "'>View the article</a><br>";
    echo "<a href='index.php'>Return to homepage</a>";
} else {
    echo "❌ Failed to add article.";
}
?>