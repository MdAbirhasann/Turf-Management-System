<?php
$pageTitle = 'Slot Booking';
require_once 'includes/header.php';
$services = getServices();
$selectedSlug = $_GET['service'] ?? '';
$today = date('Y-m-d');
?>
<section class="page-hero">
    <div class="container">
        <span class="badge">Live slot booking</span>
        <h1>Book your slot</h1>
        <p>Choose service, date, slot, payment method, and submit your request. Manager approval is required after payment verification.</p>
    </div>
</section>
<section class="container form-shell">
    <form class="form-card" id="bookingForm" action="submit_booking.php" method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="field full">
                <label>Service</label>
                <select name="service_id" id="service_id" required>
                    <?php foreach ($services as $service):
                        $prices = getPrices($service['id']);
                        $personPrice = 0;
                        foreach ($prices as $p) if ($p['pricing_type'] === 'per_person') $personPrice = $p['price'];
                    ?>
                        <option value="<?= (int)$service['id'] ?>" data-slug="<?= e($service['slug']) ?>" data-person-price="<?= e($personPrice) ?>" <?= $selectedSlug === $service['slug'] ? 'selected' : '' ?>><?= e($service['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Date</label>
                <input type="date" name="booking_date" id="booking_date" min="<?= $today ?>" value="<?= $today ?>" required>
            </div>
            <div class="field">
                <label>Start Time</label>
                <select name="start_time" id="start_time" required>
                    <?php foreach (getTimeSlots('06:00','23:00',30) as $slot): ?>
                        <option value="<?= e($slot) ?>"><?= e(date('h:i A', strtotime($slot))) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" id="durationField">
                <label>Duration</label>
                <select name="duration_minutes" id="duration_minutes">
                    <?php
                    $football = $services[0]['id'] ?? 1;
                    foreach (getPrices($football) as $p): if ($p['pricing_type'] === 'duration'): ?>
                        <option value="<?= (int)$p['duration_minutes'] ?>" data-price="<?= e($p['price']) ?>"><?= e($p['label']) ?> - <?= money($p['price']) ?></option>
                    <?php endif; endforeach; ?>
                </select>
                <small>For Football and Badminton only.</small>
            </div>
            <div class="field" id="peopleField" style="display:none">
                <label>Number of People</label>
                <input type="number" min="1" max="100" name="people_count" id="people_count" value="1">
            </div>
            <div class="field">
                <label>Name</label>
                <input type="text" name="customer_name" required maxlength="150" placeholder="Your full name">
            </div>
            <div class="field">
                <label>Phone</label>
                <input type="tel" name="phone" required maxlength="40" placeholder="01XXXXXXXXX">
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" maxlength="150" placeholder="Optional email">
            </div>
            <div class="field">
                <label>Payment Method</label>
                <select name="payment_method" required>
                    <option value="bKash">bKash</option>
                    <option value="Nagad">Nagad</option>
                </select>
            </div>
            <div class="field full">
                <label>Transaction ID</label>
                <input type="text" name="transaction_id" required maxlength="100" placeholder="Enter bKash/Nagad transaction ID">
            </div>
            <div class="field full">
                <label>Payment Screenshot</label>
                <input type="file" name="payment_screenshot" accept="image/*,.pdf">
            </div>
            <div class="field full">
                <label>Note</label>
                <textarea name="note" placeholder="Optional message"></textarea>
            </div>
            <div class="field full">
                <label class="checkbox"><input type="checkbox" name="terms" required> I agree to the terms and conditions and understand that booking remains pending until manager confirmation.</label>
            </div>
            <div class="field full"><button class="btn btn-glow" type="submit">Submit Booking Request</button></div>
        </div>
    </form>
    <aside class="form-card summary-box">
        <h2>Booking summary</h2>
        <p>Pay manually and submit the transaction details.</p>
        <div class="alert"><strong>bKash Merchant:</strong> <?= e(BKASH_NUMBER) ?><br><strong>Nagad Merchant:</strong> <?= e(NAGAD_NUMBER) ?></div>
        <p>Total Amount</p>
        <div class="amount" id="totalAmount">0 BDT</div>
        <hr style="border-color:var(--line);border-width:0 0 1px;margin:22px 0">
        <h3>Live availability</h3>
        <div id="liveSlots"><div class="alert">Select a date and service to view slots.</div></div>
    </aside>
</section>
<script>
const priceMap = <?= json_encode(array_reduce($services, function($carry, $s){ $carry[$s['id']] = getPrices($s['id']); return $carry; }, [])) ?>;
const serviceSelectEl = document.getElementById('service_id');
const durationSelectEl = document.getElementById('duration_minutes');
function rebuildDurationOptions(){
    const serviceId = serviceSelectEl.value;
    const prices = priceMap[serviceId] || [];
    durationSelectEl.innerHTML = '';
    prices.filter(p => p.pricing_type === 'duration').forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.duration_minutes;
        opt.dataset.price = p.price;
        opt.textContent = `${p.label} - ${Number(p.price).toLocaleString()} BDT`;
        durationSelectEl.appendChild(opt);
    });
}
serviceSelectEl.addEventListener('change', rebuildDurationOptions);
rebuildDurationOptions();
</script>
<?php require_once 'includes/footer.php'; ?>
