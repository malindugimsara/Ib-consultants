<?php
/**
 * IB Consultants Admin Panel - Shared Icon Picker Component
 * Provides:
 * - Curated icon catalog (Manufacturing, Strategy, Finance, Trade, Leadership, Quality, Tech, Bexon)
 * - PHP render helper with Live Preview, Dropdown menu, Modal Gallery trigger, and FontAwesome link
 */

// Define standard curated icon categories and human-readable names
function getAdminIconCatalog() {
    return [
        'Manufacturing & Operations' => [
            'fa-solid fa-industry' => ['name' => 'Factory / Plant', 'tags' => 'factory plant manufacturing industry building'],
            'fa-solid fa-gears' => ['name' => 'Machinery & Operations', 'tags' => 'gears machinery mechanics production engineering'],
            'fa-solid fa-boxes-stacked' => ['name' => 'Production & Inventory', 'tags' => 'boxes production batches inventory warehouse stock'],
            'fa-solid fa-warehouse' => ['name' => 'Warehouse & Storage', 'tags' => 'warehouse storage logistics facility depot'],
            'fa-solid fa-hard-hat' => ['name' => 'Factory Safety & HSE', 'tags' => 'safety helmet hardhat protection hse worker'],
            'fa-solid fa-screwdriver-wrench' => ['name' => 'Engineering & Maintenance', 'tags' => 'tools maintenance engineering repair mechanics'],
            'fa-solid fa-truck-ramp-box' => ['name' => 'Material Handling & Logistics', 'tags' => 'forklift material handling logistics cargo'],
            'fa-solid fa-microchip' => ['name' => 'Automation & Electronics', 'tags' => 'chip microchip hardware automation technology'],
            'fa-solid fa-robot' => ['name' => 'Industry 4.0 & Robotics', 'tags' => 'robot automation smart manufacturing industry 4.0'],
        ],
        'Strategy, Growth & Consulting' => [
            'fa-solid fa-layer-group' => ['name' => 'Strategy & Frameworks', 'tags' => 'strategy layers frameworks structure planning'],
            'fa-solid fa-chart-line' => ['name' => 'Growth & Trends', 'tags' => 'chart growth line performance sales analytics'],
            'fa-solid fa-arrow-trend-up' => ['name' => 'Scaling & High Performance', 'tags' => 'trend up scaling performance progress acceleration'],
            'fa-solid fa-magnifying-glass-chart' => ['name' => 'Diagnostics & Health Check', 'tags' => 'audit diagnostics health check assessment research'],
            'fa-solid fa-diagram-project' => ['name' => 'Workflow SOPs & Process', 'tags' => 'workflow sops process architecture flow diagram'],
            'fa-solid fa-bullseye' => ['name' => 'Target & KPIs / OKRs', 'tags' => 'target bullseye goals kpi okr objectives'],
            'fa-solid fa-arrows-spin' => ['name' => 'Kaizen & Continuous Flow', 'tags' => 'kaizen continuous improvement cycle workflow loop'],
            'fa-solid fa-chess-knight' => ['name' => 'Tactics & Strategic Moves', 'tags' => 'chess tactics knight strategy competition advantage'],
            'fa-solid fa-compass' => ['name' => 'Corporate Vision & Direction', 'tags' => 'compass vision direction strategy purpose advisory'],
            'fa-solid fa-road' => ['name' => 'Implementation Roadmap', 'tags' => 'roadmap milestones execution timeline path'],
        ],
        'Finance, Costing & Margins' => [
            'fa-solid fa-coins' => ['name' => 'Cost Reduction & Margins', 'tags' => 'coins cost margin savings pricing money finance'],
            'fa-solid fa-sack-dollar' => ['name' => 'Profit Optimization & Revenue', 'tags' => 'profit revenue income earnings dollar cash'],
            'fa-solid fa-calculator' => ['name' => 'Cost Accounting & Unit Cost', 'tags' => 'calculator costing calculation accounting finance'],
            'fa-solid fa-scale-balanced' => ['name' => 'Governance & Balance Sheet', 'tags' => 'scale balance governance law financial stability'],
            'fa-solid fa-file-invoice-dollar' => ['name' => 'Commercial Invoicing & Terms', 'tags' => 'invoice billing commercial pricing accounting terms'],
            'fa-solid fa-wallet' => ['name' => 'Cash Flow & Working Capital', 'tags' => 'wallet cash money funds working capital'],
            'fa-solid fa-piggy-bank' => ['name' => 'Cost Savings & Reserves', 'tags' => 'piggy bank savings reserves retention finance'],
            'fa-solid fa-chart-pie' => ['name' => 'Budgeting & Cost Allocation', 'tags' => 'chart pie budget allocation expenditure shares'],
        ],
        'Global Trade, Export & Supply Chain' => [
            'fa-solid fa-globe' => ['name' => 'Global Export & B2B Markets', 'tags' => 'globe world international trade export global market'],
            'fa-solid fa-ship' => ['name' => 'Ocean Freight & Sea Cargo', 'tags' => 'ship sea ocean freight export vessel container maritime'],
            'fa-solid fa-plane-departure' => ['name' => 'Air Freight & Rapid Export', 'tags' => 'airplane flight cargo air freight fast shipment'],
            'fa-solid fa-truck-fast' => ['name' => 'Express Logistics & Distribution', 'tags' => 'truck delivery transport freight distribution logistics'],
            'fa-solid fa-handshake' => ['name' => 'Buyer Matchmaking & B2B Deals', 'tags' => 'handshake partnership deals agreements trade contracts'],
            'fa-solid fa-map-location-dot' => ['name' => 'Market Territory & Distribution', 'tags' => 'map location territory market expansion distribution'],
            'fa-solid fa-box-open' => ['name' => 'Packaging & Export Dispatch', 'tags' => 'box package unboxing delivery dispatch goods'],
            'fa-solid fa-passport' => ['name' => 'Trade Compliance & Customs', 'tags' => 'passport customs compliance borders tariffs trade'],
        ],
        'Leadership, HR & Organization' => [
            'fa-solid fa-user-tie' => ['name' => 'Executive Leadership & C-Suite', 'tags' => 'executive leadership manager ceo director tie professional'],
            'fa-solid fa-people-group' => ['name' => 'High-Performance Teams & HR', 'tags' => 'team people human resources staff workforce group'],
            'fa-solid fa-chalkboard-user' => ['name' => 'Workshops & Training', 'tags' => 'training teaching chalkboard workshop mentoring coaching'],
            'fa-solid fa-users-gear' => ['name' => 'Organizational Structuring', 'tags' => 'organization structure manpower management operations'],
            'fa-solid fa-graduation-cap' => ['name' => 'Academy & Professional Certs', 'tags' => 'graduation diploma academy education training diploma cert'],
            'fa-solid fa-briefcase' => ['name' => 'Corporate Management & Advisory', 'tags' => 'briefcase corporate business advisory management enterprise'],
        ],
        'Quality, ISO & Compliance' => [
            'fa-solid fa-award' => ['name' => 'Quality Excellence & Awards', 'tags' => 'award ribbon quality excellence trophy honors'],
            'fa-solid fa-certificate' => ['name' => 'ISO & Industry Certification', 'tags' => 'certificate iso standard accreditation license compliance'],
            'fa-solid fa-shield-halved' => ['name' => 'Risk Governance & Compliance', 'tags' => 'shield security compliance protection risk legal'],
            'fa-solid fa-clipboard-check' => ['name' => 'Quality Audits & Checklists', 'tags' => 'audit clipboard check verification inspection review'],
            'fa-solid fa-stamp' => ['name' => 'Official Endorsement & Seal', 'tags' => 'stamp seal approval verified official certified'],
            'fa-solid fa-star' => ['name' => 'Benchmark Rating & Prestige', 'tags' => 'star rating benchmark review prestige top'],
        ],
        'Innovation & Digital Tech' => [
            'fa-solid fa-rocket' => ['name' => 'Enterprise Acceleration & Scale', 'tags' => 'rocket startup scaleup launch acceleration speed'],
            'fa-solid fa-lightbulb' => ['name' => 'Innovation & Creative Ideation', 'tags' => 'lightbulb idea innovation creativity solution thinking'],
            'fa-solid fa-laptop-code' => ['name' => 'Digital Systems & Software/ERP', 'tags' => 'laptop digital code software systems erp it'],
            'fa-solid fa-cloud-arrow-up' => ['name' => 'Cloud Platforms & Connectivity', 'tags' => 'cloud upload systems digital infrastructure connectivity'],
            'fa-solid fa-brain' => ['name' => 'AI, Intelligence & Analytics', 'tags' => 'brain ai artificial intelligence smart thinking analytics'],
        ],
        'Bexon Custom Theme Icons' => [
            'tji-service-1' => ['name' => 'Bexon Chart & Analytics', 'tags' => 'bexon service chart analytics growth report'],
            'tji-service-2' => ['name' => 'Bexon Global Network', 'tags' => 'bexon network global world globe connection'],
            'tji-service-3' => ['name' => 'Bexon Industrial Gear', 'tags' => 'bexon gear machinery settings process service'],
            'tji-service-4' => ['name' => 'Bexon Quality Ribbon', 'tags' => 'bexon ribbon award star quality excellence'],
            'tji-strategy' => ['name' => 'Bexon Strategy Matrix', 'tags' => 'bexon strategy matrix plan business'],
            'tji-operations' => ['name' => 'Bexon Operational Flow', 'tags' => 'bexon operations workflow logistics management'],
            'tji-performance' => ['name' => 'Bexon Performance Meter', 'tags' => 'bexon performance meter speed kpi gauge'],
            'tji-process-1' => ['name' => 'Bexon Process Architecture', 'tags' => 'bexon process flow system design structure'],
            'tji-dart-board' => ['name' => 'Bexon Precision Target', 'tags' => 'bexon target dartboard precision aim goals'],
        ]
    ];
}

/**
 * Find human-readable label for any given class
 */
function getAdminIconLabel($iconClass, $catalog = null) {
    if (!$catalog) {
        $catalog = getAdminIconCatalog();
    }
    $clean = trim($iconClass);
    foreach ($catalog as $category => $icons) {
        if (isset($icons[$clean])) {
            return $icons[$clean]['name'];
        }
    }
    return !empty($clean) ? 'Custom Icon' : 'No Icon Selected';
}

/**
 * Render complete responsive Icon Picker UI
 * @param string $inputName - Name and ID of the form input (e.g. 'category_icon', 'sector_icon', 'icon_class')
 * @param string $currentValue - Current class string stored in DB
 * @param string $label - Field label
 * @param array $options - Additional settings like help text, default icon, etc.
 */
function renderAdminIconPicker($inputName, $currentValue = '', $label = 'Icon Class', $options = []) {
    $catalog = getAdminIconCatalog();
    $defaultIcon = $options['default'] ?? 'fa-solid fa-layer-group';
    $val = trim($currentValue);
    if (empty($val)) {
        $val = $defaultIcon;
    }

    $iconLabel = getAdminIconLabel($val, $catalog);
    $inputId = htmlspecialchars($inputName);
    $selectId = 'select_' . $inputId;
    $previewIconId = 'preview_icon_' . $inputId;
    $previewNameId = 'preview_name_' . $inputId;
    $previewClassId = 'preview_class_' . $inputId;

    $isCustomInCatalog = false;

    ob_start();
    ?>
    <div class="admin-icon-picker-box mb-3" data-icon-field="<?php echo $inputId; ?>">
        <label class="form-label fw-bold d-flex align-items-center justify-content-between">
            <span>
                <i class="fa-solid fa-icons me-1 text-gold"></i> <?php echo htmlspecialchars($label); ?>
            </span>
            <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px; font-weight: 500;">
                FontAwesome & Bexon
            </span>
        </label>

        <!-- Live Visual Preview Card -->
        <div class="admin-icon-preview-card mb-2">
            <div class="admin-icon-badge-frame" title="Live Visual Icon Preview">
                <i id="<?php echo $previewIconId; ?>" class="<?php echo htmlspecialchars($val); ?>"></i>
            </div>
            <div class="admin-icon-info-group">
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <span id="<?php echo $previewNameId; ?>" class="admin-icon-name-text">
                        <?php echo htmlspecialchars($iconLabel); ?>
                    </span>
                    <span id="<?php echo $previewClassId; ?>" class="admin-icon-class-pill font-monospace">
                        <?php echo htmlspecialchars($val); ?>
                    </span>
                </div>
                <div class="admin-icon-quick-actions">
                    <a href="https://fontawesome.com/search?o=r&m=free" target="_blank" rel="noopener noreferrer" 
                       class="admin-icon-link-external" title="Search all free icons on FontAwesome official website">
                        <i class="fa-brands fa-font-awesome me-1"></i> Browse FontAwesome Free 
                        <i class="fa-solid fa-arrow-up-right-from-square ms-1" style="font-size: 9px;"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Controls: Dropdown Selector + Browse Gallery Button -->
        <div class="input-group mb-2">
            <select id="<?php echo $selectId; ?>" class="form-select admin-icon-select"
                    onchange="handleAdminIconDropdownChange('<?php echo $inputId; ?>', this.value)">
                <option value="">-- Choose from Curated Icon List --</option>
                <?php foreach ($catalog as $categoryName => $icons): ?>
                    <optgroup label="<?php echo htmlspecialchars($categoryName); ?>">
                        <?php foreach ($icons as $class => $meta): ?>
                            <?php 
                                $selected = ($val === $class);
                                if ($selected) $isCustomInCatalog = true;
                            ?>
                            <option value="<?php echo htmlspecialchars($class); ?>" 
                                    data-name="<?php echo htmlspecialchars($meta['name']); ?>"
                                    <?php echo $selected ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($meta['name']); ?> (<?php echo htmlspecialchars($class); ?>)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
                <option value="__custom__" <?php echo (!$isCustomInCatalog && !empty($val)) ? 'selected' : ''; ?>>
                    ✏️ Enter Custom Icon Class...
                </option>
            </select>

            <button type="button" class="btn btn-admin-browse-gallery" 
                    onclick="openAdminIconGallery('<?php echo $inputId; ?>')"
                    title="Open visual icon browser with real-time search">
                <i class="fa-solid fa-table-cells me-1 text-gold"></i> Browse Gallery
            </button>
        </div>

        <!-- Form Input Field (Synchronized Live with Dropdown & Gallery) -->
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-white text-muted" title="CSS Class Name">
                <i class="fa-solid fa-code"></i>
            </span>
            <input type="text" id="<?php echo $inputId; ?>" name="<?php echo $inputId; ?>" 
                   class="form-control font-monospace admin-icon-raw-input"
                   placeholder="e.g. fa-solid fa-industry or tji-service-1"
                   value="<?php echo htmlspecialchars($val); ?>"
                   oninput="handleAdminIconInputChange('<?php echo $inputId; ?>', this.value)">
            <button type="button" class="btn btn-outline-secondary" 
                    onclick="handleAdminIconPreviewRefresh('<?php echo $inputId; ?>')" title="Refresh preview">
                <i class="fa-solid fa-rotate text-muted"></i>
            </button>
        </div>
        <small class="text-muted d-block mt-1" style="font-size: 11px;">
            <i class="fa-solid fa-circle-info me-1 text-primary"></i> 
            Select an icon from the dropdown above, click <strong>Browse Gallery</strong> for visual search, or paste any FontAwesome/Bexon class.
        </small>
    </div>
    <?php
    return ob_get_clean();
}
