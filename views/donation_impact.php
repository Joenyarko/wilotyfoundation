<?php
// views/donation_impact.php
// Reusable component for Donation button and Impact section

$impact_settings_path = __DIR__ . '/../config/impact_settings.json';
$impact_mode = 'general';
$impact_title = 'How your donation is used';
$impact_content = "Funds are being prepared and allocated for upcoming urgent needs.\n\nThis allows us to respond quickly to:\n\n• Education emergencies\n• Medical support cases\n• Community outreach programs\n• Relief situations\n\nStatus: General Impact Fund (Active)";

if (file_exists($impact_settings_path)) {
    $settings = json_decode(file_get_contents($impact_settings_path), true);
    if ($settings) {
        $impact_mode = $settings['mode'] ?? 'general';
        
        if (!empty($settings['general_fund_title'])) {
            $impact_title = $settings['general_fund_title'];
        }
        if (!empty($settings['general_fund_text'])) {
            $impact_content = $settings['general_fund_text'];
        }
        
        if ($impact_mode === 'active' && !empty($settings['active_project_text'])) {
            $impact_title = 'How your donation is being used';
            $impact_title_prefix = !empty($settings['active_project_title']) ? $settings['active_project_title'] . "\n\n" : "";
            $impact_content = $impact_title_prefix . $settings['active_project_text'] . "\n\nStatus: Active Project";
        }
    }
}
?>

<div class="donation-impact-container">
    <div class="donation-actions">
        <a href="#" class="btn-donate-outline">DONATE</a>
        <button type="button" class="btn-impact-toggle" onclick="toggleImpactSection(this)">
            <span class="btn-text">See Impact</span>
            <svg class="icon-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>
    </div>
    <div class="impact-content" style="display: none;">
        <h4><?= htmlspecialchars($impact_title) ?></h4>
        <div class="impact-text">
            <?= nl2br(htmlspecialchars($impact_content)) ?>
        </div>
    </div>
</div>

<script>
// Prevent multiple re-declarations if included multiple times
if (typeof toggleImpactSection !== 'function') {
    function toggleImpactSection(btn) {
        var container = btn.closest('.donation-impact-container');
        var content = container.querySelector('.impact-content');
        var textSpan = btn.querySelector('.btn-text');
        
        if (content.style.display === 'none' || content.style.display === '') {
            content.style.display = 'block';
            textSpan.textContent = 'Hide Impact';
            btn.classList.add('is-open');
        } else {
            content.style.display = 'none';
            textSpan.textContent = 'See Impact';
            btn.classList.remove('is-open');
        }
    }
}
</script>
