<?php
$pageTitle = 'Home';
require_once 'includes/header.php';
$services = getServices();
$reviews = $pdo->query("SELECT * FROM reviews WHERE status='active' ORDER BY id DESC LIMIT 6")->fetchAll();
$gallery = $pdo->query("SELECT * FROM gallery WHERE status='active' ORDER BY id DESC LIMIT 6")->fetchAll();
$allPrices = $pdo->query("SELECT s.name service_name, p.label, p.price, p.pricing_type FROM prices p JOIN services s ON s.id=p.service_id WHERE p.status='active' ORDER BY s.id, p.id")->fetchAll();
$today = date('Y-m-d');
?>
<section class="hero">
    <div class="container">
        <div>
            <span class="badge">Premium sports booking in Bangladesh</span>
            <h1>Book Your Slot at TS Sports Arena</h1>
            <p>Reserve your football turf, badminton court, or swimming pool session with live availability, transparent pricing, digital invoice, and manual bKash/Nagad payment verification.</p>
            <div class="hero-actions">
                <a class="btn btn-glow" href="booking.php">Book Slot Now</a>
                <a class="btn btn-outline" href="#pricing">View Pricing</a>
            </div>
        </div>
        <div class="hero-card">
            <img src="assets/images/logo.jpg" alt="TS Sports Arena logo">
            <div class="hero-stats">
                <div class="stat"><strong>3</strong><span>Sports</span></div>
                <div class="stat"><strong>Live</strong><span>Slots</span></div>
                <div class="stat"><strong>24/7</strong><span>Request</span></div>
            </div>
        </div>
    </div>
</section>

<section id="services">
    <div class="container">
        <div class="section-title"><h2>Choose your arena</h2><p>Clean, organized, and ready for teams, friends, families, training sessions, and friendly matches.</p></div>
        <div class="grid-3">
            <?php foreach ($services as $service): ?>
                <div class="card">
                    <div class="icon-pill"><?= $service['slug'] === 'football' ? '⚽' : ($service['slug'] === 'badminton' ? '🏸' : '🏊') ?></div>
                    <h3><?= e($service['name']) ?></h3>
                    <p><?= e($service['description']) ?></p>
                    <a class="btn btn-outline" href="booking.php?service=<?= e($service['slug']) ?>">Book <?= e($service['name']) ?></a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="section-title"><h2>Live slot preview</h2><p>Today’s 1-hour sample availability. Final availability is checked again during booking submission.</p></div>
        <div class="form-card">
            <div class="slot-grid">
                <?php foreach (getTimeSlots('06:00','23:00',60) as $slot):
                    $end = addMinutesToTime($slot, 60);
                    $status = slotStatus(1, $today, $slot . ':00', $end);
                    $class = $status === 'Available' ? 'available' : ($status === 'Pending payment' ? 'pending' : ($status === 'Closed' ? 'closed' : 'booked'));
                ?>
                    <div class="slot <?= $class ?>"><strong><?= e($slot) ?></strong><br><small><?= e($status) ?></small></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section id="pricing">
    <div class="container">
        <div class="section-title"><h2>Simple pricing</h2><p>No hidden charge. Your invoice shows the selected sport, duration/person count, payment method, and booking status.</p></div>
        <div class="price-table">
            <table>
                <thead><tr><th>Service</th><th>Package</th><th>Price</th></tr></thead>
                <tbody>
                    <?php foreach ($allPrices as $price): ?>
                        <tr><td><?= e($price['service_name']) ?></td><td><?= e($price['label']) ?></td><td><?= money($price['price']) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="section-title"><h2>Why choose us</h2><p>A premium sports booking flow built for speed, transparency, and smooth customer experience.</p></div>
        <div class="grid-3">
            <div class="card"><div class="icon-pill">✅</div><h3>Live availability</h3><p>Check available, pending, booked, and closed slots before submitting a booking request.</p></div>
            <div class="card"><div class="icon-pill">💳</div><h3>bKash/Nagad support</h3><p>Manual payment verification keeps the system simple and ready for future gateway integration.</p></div>
            <div class="card"><div class="icon-pill">🧾</div><h3>Instant invoice</h3><p>Customers receive a branded invoice immediately after successful booking request submission.</p></div>
        </div>
    </div>
</section>

<section id="gallery">
    <div class="container">
        <div class="section-title"><h2>Gallery</h2><p>Manager can update these images from the dashboard.</p></div>
        <div class="gallery-grid">
            <?php foreach ($gallery as $item): ?>
                <img src="<?= e($item['image_path']) ?>" alt="<?= e($item['title']) ?>">
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="section-title"><h2>Customer reviews</h2><p>Show social proof and real customer experience.</p></div>
        <div class="reviews">
            <?php foreach ($reviews as $review): ?>
                <div class="card"><div class="stars"><?= str_repeat('★', (int)$review['rating']) ?></div><p>“<?= e($review['comment']) ?>”</p><strong><?= e($review['customer_name']) ?></strong></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="contact">
    <div class="container contact-grid">
        <div class="card">
            <div class="section-title"><h2>Contact & location</h2></div>
            <p><strong>Phone:</strong> <?= e(CONTACT_PHONE) ?></p>
            <p><strong>Email:</strong> <?= e(CONTACT_EMAIL) ?></p>
            <p><strong>Address:</strong> <?= e(ADDRESS) ?></p>
            <a class="btn btn-glow" href="https://wa.me/<?= preg_replace('/\D+/', '', CONTACT_PHONE) ?>">Contact on WhatsApp</a>
        </div>
        <div class="map-box">Embed Google Map iframe here<br>TS Sports Arena Location</div>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
