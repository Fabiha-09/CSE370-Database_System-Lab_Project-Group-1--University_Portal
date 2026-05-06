<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();

/* ═══════════════════════════════════════════════════════════════════════════
 * SECTION 1 — CANONICAL PREREQUISITE DEFINITION
 * Source: 'pre req condition.jpg'
 *
 * HP = Hard Prerequisite  — ALL must be passed; course is BLOCKED otherwise.
 * SP = Soft Prerequisite  — Course shown with "Recommended background" note
 *                           even if the full HP chain is not yet complete.
 * ═══════════════════════════════════════════════════════════════════════════ */
$PREREQ_DEFINITION = [

    /* ── 1. Program Core (White Boxes) ──────────────────────────────────────
     *  Chain:  CSE110 → CSE111 → CSE220 → CSE221
     *  Branch: CSE221 → { CSE321, CSE331, CSE340, CSE370, CSE422 }
     *  Gate:   CSE321 + CSE331 + CSE340  (ALL 3)  →  CSE420
     *  Branch: CSE370 → { CSE470, CSE471 }
     * ─────────────────────────────────────────────────────────────────────── */
    'CSE111' => ['HP' => ['CSE110'],                      'SP' => []],
    'CSE220' => ['HP' => ['CSE111', 'CSE230'],            'SP' => []],   // CSE230 alongside CSE111
    'CSE221' => ['HP' => ['CSE220'],                      'SP' => []],
    'CSE321' => ['HP' => ['CSE221'],                      'SP' => []],
    'CSE331' => ['HP' => ['CSE221'],                      'SP' => []],
    'CSE340' => ['HP' => ['CSE221'],                      'SP' => []],
    'CSE370' => ['HP' => ['CSE221'],                      'SP' => []],
    'CSE422' => ['HP' => ['CSE221'],                      'SP' => []],
    'CSE420' => ['HP' => ['CSE321', 'CSE331', 'CSE340'],  'SP' => []],   // ALL 3 required
    'CSE470' => ['HP' => ['CSE370'],                      'SP' => []],
    'CSE471' => ['HP' => ['CSE370'],                      'SP' => []],

    /* ── 2. Math & School Core (Blue Boxes) ─────────────────────────────────
     *  Chain:  MAT110 → MAT120 → MAT216
     *  Branch: MAT216 → { CSE330, MAT215, CSE423 }
     *  Note:   CSE230 is a standalone HP for CSE220 (alongside CSE111)
     *          CSE230's own prerequisite is CSE110.
     * ─────────────────────────────────────────────────────────────────────── */
    'CSE230' => ['HP' => ['CSE110'],                      'SP' => []],
    'MAT120' => ['HP' => ['MAT110'],                      'SP' => []],
    'MAT216' => ['HP' => ['MAT120'],                      'SP' => []],
    'CSE330' => ['HP' => ['MAT216'],                      'SP' => []],
    'MAT215' => ['HP' => ['MAT216'],                      'SP' => []],
    'CSE423' => ['HP' => ['MAT216'],                      'SP' => []],

    /* ── 3. English & GenEd (Yellow Boxes) ──────────────────────────────────
     *  Chain:  ENG091 → ENG101 → ENG102
     *  Chain:  PHY111 → PHY112
     *  Gate:   ENG102 + PHY112  (HP)  →  CSE250  (fully eligible)
     *  Soft:   PHY112           (SP)  →  CSE250  (recommended background note)
     * ─────────────────────────────────────────────────────────────────────── */
    'ENG101' => ['HP' => ['ENG091'],                      'SP' => []],
    'ENG102' => ['HP' => ['ENG101'],                      'SP' => []],
    'PHY112' => ['HP' => ['PHY111'],                      'SP' => []],
    'CSE250' => ['HP' => ['ENG102', 'PHY112'],            'SP' => ['PHY112']],
];

/* ═══════════════════════════════════════════════════════════════════════════
 * SECTION 2 — COURSE CHAIN METADATA  (for display grouping & colour coding)
 * ═══════════════════════════════════════════════════════════════════════════ */
$CHAINS = [
    'white' => [
        'label'   => 'Program Core',
        'emoji'   => '⚙️',
        'color'   => '#1a3a6b',
        'bg'      => '#eff6ff',
        'border'  => '#93c5fd',
        'tag_bg'  => '#dbeafe',
        'tag_col' => '#1e40af',
        'courses' => ['CSE110','CSE111','CSE220','CSE221','CSE321','CSE331',
                      'CSE340','CSE370','CSE422','CSE420','CSE470','CSE471'],
    ],
    'blue'  => [
        'label'   => 'Math & Science Core',
        'emoji'   => '📐',
        'color'   => '#0369a1',
        'bg'      => '#f0f9ff',
        'border'  => '#7dd3fc',
        'tag_bg'  => '#e0f2fe',
        'tag_col' => '#0369a1',
        'courses' => ['MAT110','MAT120','MAT216','MAT215','CSE230','CSE330','CSE423'],
    ],
    'yellow' => [
        'label'   => 'English & GenEd',
        'emoji'   => '📖',
        'color'   => '#b45309',
        'bg'      => '#fffbeb',
        'border'  => '#fcd34d',
        'tag_bg'  => '#fef3c7',
        'tag_col' => '#92400e',
        'courses' => ['ENG091','ENG101','ENG102','PHY111','PHY112','CSE250'],
    ],
];

function getChain(string $cc, array $CHAINS): array {
    foreach ($CHAINS as $meta) {
        if (in_array($cc, $meta['courses'])) return $meta;
    }
    return ['label'=>'General','emoji'=>'📚','color'=>'#6366f1',
            'bg'=>'#f5f3ff','border'=>'#c4b5fd','tag_bg'=>'#ede9fe','tag_col'=>'#5b21b6'];
}

/* ═══════════════════════════════════════════════════════════════════════════
 * SECTION 3 — LOAD LIVE PREREQS FROM DATABASE
 *
 * The canonical PHP array ($PREREQ_DEFINITION) is the authoritative source
 * for every course it defines.  The DB provides fallback HP data for any
 * other course not covered by the spec (e.g. CSE320, CSE421, CSE460 …).
 * ═══════════════════════════════════════════════════════════════════════════ */
$db_hp = [];   // course_code => [hp1, hp2, …]

$prereq_q = $conn->query("SELECT course_code, prereq_course_code FROM Prerequisite");
if ($prereq_q) {
    while ($r = $prereq_q->fetch_assoc()) {
        $db_hp[$r['course_code']][] = $r['prereq_course_code'];
    }
    $prereq_q->free();   // ← always free mysqli results
}

// Soft Prerequisites — check Soft_Prerequisite table first, fall back to PHP map
$db_sp = [];   // course_code => [['course'=>…,'note'=>…], …]
$sp_tbl      = $conn->query("SHOW TABLES LIKE 'Soft_Prerequisite'");
$sp_tbl_exists = ($sp_tbl && $sp_tbl->num_rows > 0);
if ($sp_tbl) $sp_tbl->free();

if ($sp_tbl_exists) {
    $sp_q = $conn->query("SELECT course_code, sp_course_code, note FROM Soft_Prerequisite");
    if ($sp_q) {
        while ($r = $sp_q->fetch_assoc()) {
            $db_sp[$r['course_code']][] = [
                'course' => $r['sp_course_code'],
                'note'   => $r['note'] ?? 'Recommended background: ' . $r['sp_course_code'],
            ];
        }
        $sp_q->free();
    }
}

/* ── Build working maps ─────────────────────────────────────────────────── */
// HP: start from DB, then override with canonical map for all defined courses
$hp_map = $db_hp;
foreach ($PREREQ_DEFINITION as $cc => $def) {
    $hp_map[$cc] = $def['HP'];   // canonical PHP map always wins for spec courses
}

// SP: canonical PHP map first; DB overrides for any matching course
$sp_map = [];
foreach ($PREREQ_DEFINITION as $cc => $def) {
    if (!empty($def['SP'])) {
        foreach ($def['SP'] as $sp_c) {
            $sp_map[$cc][] = ['course' => $sp_c,
                              'note'   => 'Recommended background: ' . $sp_c];
        }
    }
}
foreach ($db_sp as $cc => $items) {
    $sp_map[$cc] = $items;   // DB overrides PHP for same course
}

/* ═══════════════════════════════════════════════════════════════════════════
 * SECTION 4 — STUDENT DATA  (grades, enrollments)
 * ═══════════════════════════════════════════════════════════════════════════ */
$gq = $conn->prepare("SELECT Course_code, Grade_Point FROM Grade WHERE Student_id=?");
$gq->bind_param('s', $sid);
$gq->execute();
$grade_res = $gq->get_result();

$best_grades = [];
while ($r = $grade_res->fetch_assoc()) {
    $cc     = $r['Course_code'];
    $passed = ($r['Grade_Point'] !== 'F');
    if ($passed)                        $best_grades[$cc] = true;
    elseif (!isset($best_grades[$cc]))  $best_grades[$cc] = false;
}
$grade_res->free();
$gq->close();   // ← must close before next prepare() on same connection

$done           = [];
$failed_courses = [];
foreach ($best_grades as $cc => $p) {
    if ($p) $done[] = $cc;
    else    $failed_courses[] = $cc;
}

// Remove from retake list any course already re-enrolled
// Prepare ONCE outside the loop — rebind per iteration, free each result
$failed_to_retake = [];
if (!empty($failed_courses)) {
    $eq = $conn->prepare("SELECT 1 FROM Enrolled_In WHERE Student_id=? AND course_code=?");
    foreach ($failed_courses as $fc) {
        $eq->bind_param('ss', $sid, $fc);
        $eq->execute();
        $eq_res = $eq->get_result();
        if ($eq_res->num_rows === 0) $failed_to_retake[] = $fc;
        $eq_res->free();
    }
    $eq->close();
}

// Currently enrolled
$enq = $conn->prepare("SELECT course_code FROM Enrolled_In WHERE Student_id=?");
$enq->bind_param('s', $sid);
$enq->execute();
$enq_res = $enq->get_result();
$currently_enrolled = [];
while ($r = $enq_res->fetch_assoc()) $currently_enrolled[] = $r['course_code'];
$enq_res->free();
$enq->close();

/* ═══════════════════════════════════════════════════════════════════════════
 * SECTION 5 — ELIGIBILITY ENGINE
 *
 * Rule HP  : IF student has NOT passed ALL [HP courses] → BLOCK (don't show)
 * Rule SP  : IF student HAS passed [SP course] but HP not fully met
 *            → show course with "Recommended background: [SP course]" note
 * Rule NoP : Course has NO prerequisites → always eligible
 * ═══════════════════════════════════════════════════════════════════════════ */
$eligible     = [];   // All HPs satisfied → "Enroll Now"
$soft_matches = [];   // SP passed, some HPs missing → "Recommended background" note

$all_q = $conn->query("SELECT course_code FROM Course ORDER BY course_code");
if ($all_q) {
    while ($r = $all_q->fetch_assoc()) { // loop — $all_q->free() is called after the block
        $cc = $r['course_code'];

        if (in_array($cc, $done) || in_array($cc, $currently_enrolled)) continue;

        $hp_courses = $hp_map[$cc] ?? [];
        $sp_list    = $sp_map[$cc] ?? [];

        // Evaluate HP satisfaction
        $missing_hp = [];
        foreach ($hp_courses as $hp) {
            if (!in_array($hp, $done)) $missing_hp[] = $hp;
        }
        $hp_met = empty($missing_hp);

        if ($hp_met) {
            // ✅ Fully eligible — also collect any applicable SP bonus notes
            $sp_notes = [];
            foreach ($sp_list as $sp_item) {
                if (in_array($sp_item['course'], $done)) $sp_notes[] = $sp_item['note'];
            }
            $eligible[] = [
                'code'    => $cc,
                'hp_list' => $hp_courses,
                'sp_note' => $sp_notes,
                'chain'   => getChain($cc, $CHAINS),
            ];
        } else {
            // Check SP match: if any SP course is passed → soft recommendation
            $sp_matched = [];
            foreach ($sp_list as $sp_item) {
                if (in_array($sp_item['course'], $done)) $sp_matched[] = $sp_item['note'];
            }
            if (!empty($sp_matched)) {
                $soft_matches[] = [
                    'code'       => $cc,
                    'sp_notes'   => $sp_matched,
                    'missing_hp' => $missing_hp,
                    'chain'      => getChain($cc, $CHAINS),
                ];
            }
        }
    }
    $all_q->free();
}

$eligible_codes = array_column($eligible, 'code');

/* ── Map node helpers ───────────────────────────────────────────────────── */
function mapNodeStatus(string $cc, array $done, array $enrolled, array $eligible_codes): string {
    if (in_array($cc, $done))           return 'done';
    if (in_array($cc, $enrolled))       return 'enrolled';
    if (in_array($cc, $eligible_codes)) return 'eligible';
    return 'locked';
}

function renderNode(string $cc, array $done, array $enrolled, array $elig): string {
    $s = mapNodeStatus($cc, $done, $enrolled, $elig);
    $icons = ['done'=>'✓','enrolled'=>'▶','eligible'=>'⚡','locked'=>'🔒'];
    return '<span class="mnode mnode-'.$s.'" title="'.$cc.'">'.$icons[$s].' '.htmlspecialchars($cc).'</span>';
}

function arr(): string { return '<span class="marrow">→</span>'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Smart Recommendations – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* ── Prerequisite Map Nodes ──────────────────────────────────────── */
.mnode {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 11px; border-radius: 20px;
    font-size: 12px; font-weight: 700; white-space: nowrap;
    border: 1.5px solid; cursor: default;
    transition: transform .15s, box-shadow .15s;
    line-height: 1.2;
}
.mnode:hover { transform: scale(1.07); box-shadow: 0 2px 8px rgba(0,0,0,.12); }
.mnode-done     { background:#d1fae5; color:#065f46; border-color:#6ee7b7; }
.mnode-enrolled { background:#dbeafe; color:#1e40af; border-color:#93c5fd; }
.mnode-eligible { background:#fef3c7; color:#92400e; border-color:#fcd34d; }
.mnode-locked   { background:#f3f4f6; color:#6b7280; border-color:#d1d5db; }

.marrow { color:#9ca3af; font-size:16px; margin:0 3px; line-height:1; }

/* ── Chain Blocks ────────────────────────────────────────────────── */
.prereq-chain-block {
    border-left: 4px solid;
    border-radius: 0 10px 10px 0;
    padding: 16px 20px;
    margin-bottom: 14px;
}
.chain-title { font-weight: 700; font-size: 13px; margin-bottom: 10px; letter-spacing:.3px; }
.chain-flow  { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; }
.chain-sub   { margin-top: 8px; }
.chain-note  { font-size: 11px; margin-top: 6px; opacity: .75; font-style: italic; }
.map-group-label {
    font-size: 11px; font-weight: 700; color: #6b7280;
    margin: 0 4px; white-space: nowrap;
}
.map-brace   { font-size: 13px; font-weight: 700; }

/* ── Map Legend ──────────────────────────────────────────────────── */
.map-legend {
    display: flex; flex-wrap: wrap; gap: 12px;
    margin-top: 16px; padding-top: 14px;
    border-top: 1px solid var(--border);
}
.map-legend .leg { display: flex; align-items: center; gap: 7px; font-size: 12px; color: var(--text-muted); }

/* ── Stats Row ───────────────────────────────────────────────────── */
.rec-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 24px; }
.rec-stat  {
    background: #fff; border: 1px solid var(--border);
    border-radius: 12px; padding: 18px 20px; text-align: center;
    box-shadow: var(--shadow);
}
.rec-stat .rnum { font-size: 30px; font-weight: 700; line-height: 1; }
.rec-stat .rlbl { font-size: 12px; color: var(--text-muted); margin-top: 5px; }

/* ── Course Row Cards ────────────────────────────────────────────── */
.course-row {
    border: 1px solid var(--border); border-radius: 10px;
    padding: 14px 18px; margin-bottom: 10px;
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 14px;
    background: #fff; transition: box-shadow .2s;
}
.course-row:hover { box-shadow: var(--shadow); }

.chain-tag {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 9px; border-radius: 12px;
    font-size: 11px; font-weight: 700;
}

/* HP / SP chip styles */
.hp-chips { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 7px; }
.hp-chip {
    padding: 2px 9px; border-radius: 12px;
    font-size: 11px; font-weight: 600;
    background: #e8f5ee; color: #065f46; border: 1px solid #6ee7b7;
}
.missing-chip {
    padding: 2px 9px; border-radius: 12px;
    font-size: 11px; font-weight: 600;
    background: #fde8e6; color: var(--danger); border: 1px solid #f5b8b3;
}
.sp-note-box {
    background: #fffbeb; border: 1px solid #fcd34d;
    border-radius: 8px; padding: 8px 12px;
    font-size: 12px; color: #92400e;
    margin-top: 8px; display: flex; align-items: flex-start; gap: 6px;
}

/* ── Section Divider label ───────────────────────────────────────── */
.section-label {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1px; color: var(--text-muted);
    margin: 8px 0 12px; padding-left: 2px;
}

@media(max-width:768px) {
    .rec-stats { grid-template-columns: repeat(2,1fr); }
    .chain-flow { gap: 5px; }
    .course-row { flex-direction: column; }
}
</style>
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">

  <div class="topbar">
    <div>
      <h1>🤖 Smart Course Recommendations</h1>
      <p>Full prerequisite dependency map — personalised to your academic history</p>
    </div>
    <div class="topbar-right">
      <span class="badge badge-primary">📋 <?= count($done) ?> course<?= count($done)!==1?'s':'' ?> completed</span>
    </div>
  </div>

  <div class="page-content">

    <!-- ═══════════════════════════════════════════════════════════════
         CARD 1 — PREREQUISITE DEPENDENCY MAP  (visual chains)
    ═══════════════════════════════════════════════════════════════ -->
    <div class="card mb-6">
      <div class="card-header">
        <div>
          <h2>🗺️ Prerequisite Dependency Map</h2>
          <p>Hover over any course node to see its code — your live progress is colour-coded</p>
        </div>
      </div>
      <div class="card-body">

        <!-- ── Chain 1: Program Core (White Boxes) ── -->
        <div class="prereq-chain-block" style="border-left-color:#1a3a6b; background:#eff6ff;">
          <div class="chain-title" style="color:#1a3a6b;">⚙️ Program Core Chain (White Boxes)</div>

          <!-- Main trunk -->
          <div class="chain-flow">
            <?= renderNode('CSE110',$done,$currently_enrolled,$eligible_codes) ?>
            <?= arr() ?>
            <?= renderNode('CSE111',$done,$currently_enrolled,$eligible_codes) ?>
            <?= arr() ?>
            <?= renderNode('CSE220',$done,$currently_enrolled,$eligible_codes) ?>
            <span class="map-group-label">(also needs CSE230 ↓)</span>
            <?= arr() ?>
            <?= renderNode('CSE221',$done,$currently_enrolled,$eligible_codes) ?>
            <?= arr() ?>
            <span class="map-brace" style="color:#1a3a6b;">{</span>
            <?= renderNode('CSE321',$done,$currently_enrolled,$eligible_codes) ?>
            <?= renderNode('CSE331',$done,$currently_enrolled,$eligible_codes) ?>
            <?= renderNode('CSE340',$done,$currently_enrolled,$eligible_codes) ?>
            <?= renderNode('CSE370',$done,$currently_enrolled,$eligible_codes) ?>
            <?= renderNode('CSE422',$done,$currently_enrolled,$eligible_codes) ?>
            <span class="map-brace" style="color:#1a3a6b;">}</span>
          </div>

          <!-- Second line: gate + branches -->
          <div class="chain-flow chain-sub">
            <span class="map-group-label">CSE321 + CSE331 + CSE340 (ALL 3):</span>
            <?= arr() ?>
            <?= renderNode('CSE420',$done,$currently_enrolled,$eligible_codes) ?>
            &nbsp;&nbsp;
            <span class="map-group-label">CSE370:</span>
            <?= arr() ?>
            <?= renderNode('CSE470',$done,$currently_enrolled,$eligible_codes) ?>
            <?= renderNode('CSE471',$done,$currently_enrolled,$eligible_codes) ?>
          </div>
          <div class="chain-note" style="color:#1a3a6b;">
            ⚠️ CSE420 is only unlocked when CSE321, CSE331 AND CSE340 are ALL passed.
          </div>
        </div>

        <!-- ── Chain 2: Math & Science Core (Blue Boxes) ── -->
        <div class="prereq-chain-block" style="border-left-color:#0369a1; background:#f0f9ff;">
          <div class="chain-title" style="color:#0369a1;">📐 Math & Science Core Chain (Blue Boxes)</div>

          <!-- CSE230 standalone row -->
          <div class="chain-flow" style="margin-bottom:8px;">
            <?= renderNode('CSE110',$done,$currently_enrolled,$eligible_codes) ?>
            <?= arr() ?>
            <?= renderNode('CSE230',$done,$currently_enrolled,$eligible_codes) ?>
            <span class="map-group-label">— standalone HP for CSE220 alongside CSE111</span>
          </div>

          <!-- MAT chain -->
          <div class="chain-flow">
            <?= renderNode('MAT110',$done,$currently_enrolled,$eligible_codes) ?>
            <?= arr() ?>
            <?= renderNode('MAT120',$done,$currently_enrolled,$eligible_codes) ?>
            <?= arr() ?>
            <?= renderNode('MAT216',$done,$currently_enrolled,$eligible_codes) ?>
            <?= arr() ?>
            <span class="map-brace" style="color:#0369a1;">{</span>
            <?= renderNode('CSE330',$done,$currently_enrolled,$eligible_codes) ?>
            <?= renderNode('MAT215',$done,$currently_enrolled,$eligible_codes) ?>
            <?= renderNode('CSE423',$done,$currently_enrolled,$eligible_codes) ?>
            <span class="map-brace" style="color:#0369a1;">}</span>
          </div>
        </div>

        <!-- ── Chain 3: English & GenEd (Yellow Boxes) ── -->
        <div class="prereq-chain-block" style="border-left-color:#d97706; background:#fffbeb;">
          <div class="chain-title" style="color:#b45309;">📖 English & GenEd Chain (Yellow Boxes)</div>

          <!-- ENG chain -->
          <div class="chain-flow" style="margin-bottom:8px;">
            <?= renderNode('ENG091',$done,$currently_enrolled,$eligible_codes) ?>
            <?= arr() ?>
            <?= renderNode('ENG101',$done,$currently_enrolled,$eligible_codes) ?>
            <?= arr() ?>
            <?= renderNode('ENG102',$done,$currently_enrolled,$eligible_codes) ?>
            <span class="map-group-label">(HP)</span>
            <?= arr() ?>
            <?= renderNode('CSE250',$done,$currently_enrolled,$eligible_codes) ?>
          </div>

          <!-- PHY chain -->
          <div class="chain-flow">
            <?= renderNode('PHY111',$done,$currently_enrolled,$eligible_codes) ?>
            <?= arr() ?>
            <?= renderNode('PHY112',$done,$currently_enrolled,$eligible_codes) ?>
            <span class="map-group-label">(HP + SP)</span>
            <?= arr() ?>
            <?= renderNode('CSE250',$done,$currently_enrolled,$eligible_codes) ?>
          </div>

          <div class="chain-note" style="color:#92400e;">
            ⚡ PHY112 alone = Soft Prereq for CSE250 (recommended background note shown).
            ENG102 + PHY112 together = full Hard Prereq → course becomes fully eligible.
          </div>
        </div>

        <!-- Legend -->
        <div class="map-legend">
          <div class="leg"><span class="mnode mnode-done">✓ DONE</span> Completed &amp; passed</div>
          <div class="leg"><span class="mnode mnode-enrolled">▶ ENRL</span> Currently enrolled</div>
          <div class="leg"><span class="mnode mnode-eligible">⚡ ELIG</span> Eligible — can enrol now</div>
          <div class="leg"><span class="mnode mnode-locked">🔒 LOCK</span> Locked — prerequisites missing</div>
        </div>

      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         STATS SUMMARY
    ═══════════════════════════════════════════════════════════════ -->
    <div class="rec-stats mb-6">
      <div class="rec-stat">
        <div class="rnum" style="color:var(--success);"><?= count($done) ?></div>
        <div class="rlbl">✅ Courses Completed</div>
      </div>
      <div class="rec-stat">
        <div class="rnum" style="color:var(--primary);"><?= count($currently_enrolled) ?></div>
        <div class="rlbl">📚 Currently Enrolled</div>
      </div>
      <div class="rec-stat">
        <div class="rnum" style="color:#b45309;"><?= count($eligible) ?></div>
        <div class="rlbl">🎯 Eligible to Enrol</div>
      </div>
      <div class="rec-stat">
        <div class="rnum" style="color:#0369a1;"><?= count($soft_matches) ?></div>
        <div class="rlbl">📡 Soft Prereq Matches</div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         CARD 2 — ACTION REQUIRED: RETAKE FAILED COURSES
    ═══════════════════════════════════════════════════════════════ -->
    <?php if (!empty($failed_to_retake)): ?>
    <div class="card mb-6" style="border: 2px solid var(--danger);">
      <div class="card-header" style="background:var(--danger-bg);">
        <div>
          <h2 style="color:var(--danger);">🚨 Action Required — Retake Failed Courses</h2>
          <p style="color:var(--danger);">These courses must be retaken; failing them blocks dependent prerequisites.</p>
        </div>
        <span class="badge badge-danger"><?= count($failed_to_retake) ?> course<?= count($failed_to_retake)!==1?'s':'' ?></span>
      </div>
      <div class="card-body">
        <?php foreach($failed_to_retake as $fc):
          $ch = getChain($fc, $CHAINS); ?>
        <div class="course-row" style="border-color:var(--danger); background:#fff8f8;">
          <div>
            <div class="fw-bold" style="font-size:15px; color:var(--danger);"><?= htmlspecialchars($fc) ?></div>
            <div style="margin-top:5px;">
              <span class="chain-tag" style="background:<?= $ch['tag_bg'] ?>; color:<?= $ch['tag_col'] ?>;">
                <?= $ch['emoji'] ?> <?= $ch['label'] ?>
              </span>
            </div>
            <div class="text-muted" style="margin-top:5px; font-size:12px;">
              Grade recorded: <strong>F</strong> — must retake to clear the prerequisite chain.
            </div>
          </div>
          <a href="enrollment.php" class="btn btn-sm btn-danger" style="flex-shrink:0;">Retake Now</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         CARD 3 — ELIGIBLE COURSES  (All HPs satisfied)
    ═══════════════════════════════════════════════════════════════ -->
    <div class="card mb-6">
      <div class="card-header">
        <div>
          <h2>🎯 Courses You Can Enrol In</h2>
          <p>All hard prerequisites (HP) satisfied — ready to register</p>
        </div>
        <span class="badge badge-success"><?= count($eligible) ?> available</span>
      </div>
      <div class="card-body">
        <?php if (!empty($eligible)):

          // Group by chain for cleaner display
          $grouped = ['white'=>[],'blue'=>[],'yellow'=>[],'other'=>[]];
          foreach($eligible as $item) {
              $found = false;
              foreach(['white','blue','yellow'] as $key) {
                  if (in_array($item['code'], $CHAINS[$key]['courses'])) {
                      $grouped[$key][] = $item; $found = true; break;
                  }
              }
              if (!$found) $grouped['other'][] = $item;
          }

          $section_labels = [
              'white'  => '⚙️ Program Core',
              'blue'   => '📐 Math & Science Core',
              'yellow' => '📖 English & GenEd',
              'other'  => '📚 Other Courses',
          ];

          foreach($grouped as $key => $items):
            if (empty($items)) continue; ?>
          <div class="section-label"><?= $section_labels[$key] ?></div>
          <?php foreach($items as $item):
            $ch = $item['chain']; ?>
          <div class="course-row">
            <div style="flex:1;">
              <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span class="fw-bold" style="font-size:15px;"><?= htmlspecialchars($item['code']) ?></span>
                <span class="chain-tag" style="background:<?= $ch['tag_bg'] ?>; color:<?= $ch['tag_col'] ?>;">
                  <?= $ch['emoji'] ?> <?= $ch['label'] ?>
                </span>
              </div>

              <?php if (!empty($item['hp_list'])): ?>
              <div class="hp-chips">
                <span style="font-size:11px; color:var(--text-muted); margin-right:3px;">✓ HP met:</span>
                <?php foreach($item['hp_list'] as $hp): ?>
                <span class="hp-chip"><?= htmlspecialchars($hp) ?></span>
                <?php endforeach; ?>
              </div>
              <?php else: ?>
              <div class="text-muted" style="font-size:12px; margin-top:5px;">No prerequisites required.</div>
              <?php endif; ?>

              <?php foreach($item['sp_note'] as $note): ?>
              <div class="sp-note-box">💡 <?= htmlspecialchars($note) ?></div>
              <?php endforeach; ?>
            </div>
            <a href="enrollment.php" class="btn btn-primary btn-sm" style="flex-shrink:0; margin-top:2px;">Enroll Now</a>
          </div>
          <?php endforeach; ?>
          <?php endforeach; ?>

        <?php else: ?>
        <div class="empty-state">
          <div class="empty-icon">🎓</div>
          <p>You've completed all currently eligible courses or are already enrolled.<br>
             Keep working through the prerequisite chains to unlock more!</p>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         CARD 4 — SOFT PREREQUISITE MATCHES
         Rule: Student passed [SP course] but HP chain not fully complete.
               Show course with "Recommended background: [SP course]" note.
    ═══════════════════════════════════════════════════════════════ -->
    <?php if (!empty($soft_matches)): ?>
    <div class="card mb-6" style="border-color:#fcd34d;">
      <div class="card-header" style="background:#fffbeb;">
        <div>
          <h2 style="color:#b45309;">📡 Upcoming Courses — Soft Prerequisite Match</h2>
          <p>You have the recommended background, but some hard prerequisites are still needed.</p>
        </div>
        <span class="badge" style="background:#fef3c7; color:#92400e;">
          <?= count($soft_matches) ?> course<?= count($soft_matches)!==1?'s':'' ?>
        </span>
      </div>
      <div class="card-body">
        <?php foreach($soft_matches as $item):
          $ch = $item['chain']; ?>
        <div class="course-row" style="border-color:#fcd34d; background:#fffdf5;">
          <div style="flex:1;">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
              <span class="fw-bold" style="font-size:15px;"><?= htmlspecialchars($item['code']) ?></span>
              <span class="chain-tag" style="background:<?= $ch['tag_bg'] ?>; color:<?= $ch['tag_col'] ?>;">
                <?= $ch['emoji'] ?> <?= $ch['label'] ?>
              </span>
              <span class="badge" style="background:#fef3c7; color:#92400e; font-size:11px;">📡 Soft Prereq Match</span>
            </div>

            <?php foreach($item['sp_notes'] as $note): ?>
            <div class="sp-note-box" style="margin-top:8px;">
              💡 <?= htmlspecialchars($note) ?>
            </div>
            <?php endforeach; ?>

            <?php if (!empty($item['missing_hp'])): ?>
            <div style="margin-top:8px; display:flex; flex-wrap:wrap; align-items:center; gap:5px;">
              <span style="font-size:11px; color:var(--text-muted);">🔒 Still need (HP):</span>
              <?php foreach($item['missing_hp'] as $mhp): ?>
              <span class="missing-chip"><?= htmlspecialchars($mhp) ?></span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
          <span class="badge badge-warning" style="flex-shrink:0; align-self:center;">Not yet eligible</span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         CARD 5 — CURRENTLY ENROLLED
    ═══════════════════════════════════════════════════════════════ -->
    <?php if (!empty($currently_enrolled)): ?>
    <div class="card mb-6">
      <div class="card-header">
        <div><h2>📚 Currently Enrolled This Semester</h2></div>
        <span class="badge badge-primary"><?= count($currently_enrolled) ?> course<?= count($currently_enrolled)!==1?'s':'' ?></span>
      </div>
      <div class="card-body">
        <?php foreach($currently_enrolled as $cc):
          $ch = getChain($cc, $CHAINS); ?>
        <span class="course-chip" style="background:<?= $ch['tag_bg'] ?>; color:<?= $ch['tag_col'] ?>; border:1px solid <?= $ch['border'] ?>;">
          <?= $ch['emoji'] ?> <?= htmlspecialchars($cc) ?>
        </span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         CARD 6 — COMPLETED COURSES
    ═══════════════════════════════════════════════════════════════ -->
    <div class="card">
      <div class="card-header">
        <div>
          <h2>✅ Your Completed Courses</h2>
          <p>Courses with a passing grade on record — these satisfy prerequisite checks.</p>
        </div>
        <span class="badge badge-success"><?= count($done) ?> passed</span>
      </div>
      <div class="card-body">
        <?php if (!empty($done)):
          foreach($done as $cc):
            $ch = getChain($cc, $CHAINS); ?>
          <span class="course-chip chip-done" title="<?= $ch['label'] ?>"><?= htmlspecialchars($cc) ?></span>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-muted">No completed courses found yet. Start your journey!</div>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /page-content -->
</div><!-- /main -->
</div><!-- /layout -->
</body>
</html>
