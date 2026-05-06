<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();

/* ═══════════════════════════════════════════════════════════════════════════
 * STEP 1 — SEED WEEKLY SECTION DATA (runs once; INSERT IGNORE is a no-op
 *           on subsequent loads once the rows exist)
 *
 * Section.date is a specific Monday / Tuesday … in the week of 2026-05-03.
 * DAYNAME(date) gives the repeating weekly slot used by the matrix.
 *
 * Room → Day + Start-time mapping (matches Course.room_no assignments):
 *   07A-01C  Mon + Wed @ 14:00  (CSE220, CSE230, CSE330, PHY111 … )
 *   07A-02C  Tue + Thu @ 11:00  (CSE111, CSE422, MAT110 … )
 *   07A-03C  Sun + Tue @ 09:30  (CSE110, CSE221, MGT213 … )
 *   07A-04C  Mon + Wed @ 11:00  (CSE321, PHY112 … )
 *   07A-05C  Tue + Thu @ 14:00  (CSE340, CSE420 … )
 *   08A-01C  Mon + Wed @ 09:30  (CSE320, CSE460 … )
 *   08A-02C  Sun + Tue @ 14:00  (MAT120, CSE360, CSE470 … )
 *   08A-03C  Sun + Mon + Wed @ 08:00 (CSE370 … )
 *   08A-04C  Tue + Thu @ 15:30  (CSE423 … )
 *   08A-05C  Mon + Wed @ 12:30  (CSE440 … )
 *   09A-01C  Mon + Wed @ 12:30  (STA101 … )
 *   09A-02C  Sun + Tue @ 11:00  (MAT215, CSE421 … )
 * ═══════════════════════════════════════════════════════════════════════════ */
$seed_sql = "INSERT IGNORE INTO Section (section_no, room_no, date, time) VALUES
 -- 07A-01C: S995 (Mon@14:00) + S996 (Wed@14:00) already exist — no extra rows needed
 -- 07A-02C: Tue+Thu 11:00
 ('WB1','07A-02C','2026-05-05','11:00:00'),
 ('WB2','07A-02C','2026-05-07','11:00:00'),
 -- 07A-03C: Sun+Tue 09:30
 ('WC1','07A-03C','2026-05-03','09:30:00'),
 ('WC2','07A-03C','2026-05-05','09:30:00'),
 -- 07A-04C: Mon+Wed 11:00
 ('WD1','07A-04C','2026-05-04','11:00:00'),
 ('WD2','07A-04C','2026-05-06','11:00:00'),
 -- 07A-05C: Tue+Thu 14:00
 ('WE1','07A-05C','2026-05-05','14:00:00'),
 ('WE2','07A-05C','2026-05-07','14:00:00'),
 -- 08A-01C: Mon+Wed 09:30
 ('WF1','08A-01C','2026-05-04','09:30:00'),
 ('WF2','08A-01C','2026-05-06','09:30:00'),
 -- 08A-02C: Sun+Tue 14:00
 ('WG1','08A-02C','2026-05-03','14:00:00'),
 ('WG2','08A-02C','2026-05-05','14:00:00'),
 -- 08A-03C: Sun @ 08:00  (S991=Mon, S992=Wed already exist)
 ('WH1','08A-03C','2026-05-03','08:00:00'),
 -- 08A-04C: Tue+Thu 15:30
 ('WI1','08A-04C','2026-05-05','15:30:00'),
 ('WI2','08A-04C','2026-05-07','15:30:00'),
 -- 08A-05C: Mon+Wed 12:30
 ('WJ1','08A-05C','2026-05-04','12:30:00'),
 ('WJ2','08A-05C','2026-05-06','12:30:00'),
 -- 09A-01C: Mon+Wed 12:30  (S993=Tue, S994=Thu already exist)
 ('WK1','09A-01C','2026-05-04','12:30:00'),
 ('WK2','09A-01C','2026-05-06','12:30:00'),
 -- 09A-02C: Sun+Tue 11:00
 ('WL1','09A-02C','2026-05-03','11:00:00'),
 ('WL2','09A-02C','2026-05-05','11:00:00')";
$conn->query($seed_sql);

/* ═══════════════════════════════════════════════════════════════════════════
 * STEP 2 — MATRIX DEFINITION
 * ═══════════════════════════════════════════════════════════════════════════ */
// Canonical time slots (directive spec) — key = DB time value for nearest-match
$TIME_SLOTS = [
    '08:00:00' => '08:00 AM',
    '09:30:00' => '09:30 AM',
    '11:00:00' => '11:00 AM',
    '12:30:00' => '12:30 PM',
    '14:00:00' => '02:00 PM',
    '15:30:00' => '03:30 PM',
];
// Canonical day columns (DAYNAME values from MySQL)
$DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
$DAY_SHORT = ['Sunday'=>'Sun','Monday'=>'Mon','Tuesday'=>'Tue','Wednesday'=>'Wed','Thursday'=>'Thu'];

// Initialise empty matrix
$matrix = [];
foreach (array_keys($TIME_SLOTS) as $t) {
    foreach ($DAYS as $d) {
        $matrix[$t][$d] = [];   // will hold course entries
    }
}

/* ═══════════════════════════════════════════════════════════════════════════
 * STEP 3 — FETCH WEEKLY CLASS SCHEDULE
 * JOIN: Enrolled_In → Course (room_no) → Section (room_no + DAYNAME)
 * Filter: only sections in the seed week (2026-05-03 … 2026-05-09)
 * ═══════════════════════════════════════════════════════════════════════════ */
$rq = $conn->prepare("
    SELECT DISTINCT c.course_code, c.room_no,
           TIME_FORMAT(s.time,'%H:%i:%s') AS slot_time,
           DAYNAME(s.date)                AS day_name
    FROM   Enrolled_In e
    JOIN   Course c   ON e.course_code = c.course_code
    JOIN   Section s  ON c.room_no    = s.room_no
    WHERE  e.Student_id = ?
      AND  s.date BETWEEN '2026-05-03' AND '2026-05-09'
    ORDER  BY s.time ASC, day_name ASC
");
$rq->bind_param('s', $sid);
$rq->execute();
$rq_res = $rq->get_result();

// Helper: snap a DB time to the nearest canonical slot
function snapSlot(string $raw_time, array $slots): string {
    $raw_secs = strtotime('1970-01-01 ' . $raw_time);
    $best = null; $best_diff = PHP_INT_MAX;
    foreach (array_keys($slots) as $slot) {
        $diff = abs($raw_secs - strtotime('1970-01-01 ' . $slot));
        if ($diff < $best_diff) { $best_diff = $diff; $best = $slot; }
    }
    return $best;
}

while ($row = $rq_res->fetch_assoc()) {
    $slot = snapSlot($row['slot_time'], $TIME_SLOTS);
    $day  = $row['day_name'];
    if ($slot && isset($matrix[$slot][$day])) {
        $key = $row['course_code'];   // deduplicate per course per cell
        if (!isset($matrix[$slot][$day][$key])) {
            $matrix[$slot][$day][$key] = [
                'course_code' => $row['course_code'],
                'room_no'     => $row['room_no'],
            ];
        }
    }
}
$rq_res->free();
$rq->close();

/* ═══════════════════════════════════════════════════════════════════════════
 * STEP 4 — EXAM DATA
 * 4a. AcademicRoutine → course_code ↔ exam_date mapping
 * 4b. Exam table      → full exam schedule for footer row + cell badges
 * ═══════════════════════════════════════════════════════════════════════════ */

// 4a: course → exam date from AcademicRoutine (used for cell highlight)
$ar_q = $conn->prepare("
    SELECT Courses AS course_code, Exam_date, Exam_routine, Attribute
    FROM   AcademicRoutine
    WHERE  Student_id = ?
");
$ar_q->bind_param('s', $sid);
$ar_q->execute();
$ar_res = $ar_q->get_result();
$course_exam_map = [];   // course_code => [Exam_date, Exam_routine, Attribute]
while ($row = $ar_res->fetch_assoc()) {
    $course_exam_map[$row['course_code']] = $row;
}
$ar_res->free();
$ar_q->close();

// 4b: full exam schedule from Exam table
$ex_q = $conn->prepare("
    SELECT Exam_type, Exam_date, Start_time, Room_number
    FROM   Exam
    WHERE  Student_id = ?
    ORDER  BY Exam_date ASC, Start_time ASC
");
$ex_q->bind_param('s', $sid);
$ex_q->execute();
$ex_res = $ex_q->get_result();
$exams = [];
while ($row = $ex_res->fetch_assoc()) $exams[] = $row;
$ex_res->free();
$ex_q->close();

// Build exam-day lookup for footer row: day_abbr => [exam entries]
$exam_day_map = [];
foreach ($exams as $ex) {
    $exam_day = date('l', strtotime($ex['Exam_date']));   // e.g. 'Saturday'
    $exam_day_map[$exam_day][] = $ex;
}

// Build set of course codes that have exam records
$courses_with_exams = array_keys($course_exam_map);

// Count how many distinct courses the student is currently scheduled for
$total_courses = 0;
foreach ($matrix as $t => $days_cells) {
    foreach ($days_cells as $d => $cells) {
        foreach ($cells as $key => $entry) {
            // count unique courses
        }
    }
}
$seen_for_count = [];
foreach ($matrix as $t => $days_cells) {
    foreach ($days_cells as $d => $cells) {
        foreach ($cells as $key => $entry) {
            $seen_for_count[$entry['course_code']] = true;
        }
    }
}
$total_courses = count($seen_for_count);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Academic Routine – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* ── Routine Matrix ─────────────────────────────────────────── */
.matrix-wrap { overflow-x: auto; }

table.routine-matrix {
    width: 100%; border-collapse: collapse;
    min-width: 620px;
    font-size: 13px;
}

.routine-matrix th,
.routine-matrix td {
    border: 1px solid var(--border);
    padding: 0;
    vertical-align: top;
    text-align: center;
}

/* Day header cells */
.routine-matrix thead th {
    background: var(--primary);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .5px;
    padding: 10px 6px;
    text-align: center;
}
.routine-matrix thead th:first-child {
    background: #1a2340;
    width: 95px;
    min-width: 95px;
}

/* Time-label column */
.time-label {
    background: var(--surface2);
    color: var(--primary);
    font-weight: 700;
    font-size: 11px;
    padding: 8px 6px;
    white-space: nowrap;
    text-align: center;
    vertical-align: middle;
    min-width: 85px;
}

/* Regular slot cell */
.slot-cell {
    background: #fff;
    min-height: 64px;
    padding: 4px;
    text-align: center;
    vertical-align: middle;
}
.slot-cell.has-class {
    background: #f0f7ff;
}

/* Course box inside a cell */
.course-box {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    background: var(--primary);
    color: #fff;
    border-radius: 8px;
    padding: 7px 10px;
    margin: 3px;
    min-width: 80px;
    box-shadow: 0 2px 6px rgba(26,58,107,.2);
    transition: transform .15s;
}
.course-box:hover { transform: scale(1.04); }
.course-box .cb-code { font-weight: 700; font-size: 12px; letter-spacing:.3px; }
.course-box .cb-room { font-size: 10px; opacity: .8; }
.course-box .cb-exam { font-size: 9px; margin-top: 3px;
    background: var(--accent); color: #fff;
    border-radius: 4px; padding: 1px 5px; }

/* Exam footer row */
.exam-row td { background: #fff3d6; vertical-align: top; padding: 6px; }
.exam-row .time-label { background: #f5c45e; color: #7a4f00; }
.exam-badge {
    display: inline-flex; flex-direction: column; align-items: center;
    gap: 2px;
    background: var(--accent); color: #fff;
    border-radius: 8px; padding: 5px 8px; margin: 2px;
    font-size: 10px; line-height: 1.3;
}
.exam-badge .eb-type { font-weight: 700; font-size: 11px; }

/* Legend */
.matrix-legend {
    display: flex; flex-wrap: wrap; gap: 14px;
    margin-top: 14px; padding-top: 12px;
    border-top: 1px solid var(--border);
    font-size: 12px; color: var(--text-muted);
}
.matrix-legend .leg { display: flex; align-items: center; gap: 6px; }

/* Exam timetable card */
.exam-table-card { margin-top: 24px; }

@media(max-width:768px) {
    .routine-matrix thead th,
    .routine-matrix td { font-size: 11px; }
    .course-box { min-width: 60px; padding: 5px 6px; }
}
</style>
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">

  <div class="topbar">
    <div>
      <h1>📅 Academic Routine</h1>
      <p>2D weekly class matrix · Spring 2026</p>
    </div>
    <div class="topbar-right">
      <span class="badge badge-primary">
        <?= $total_courses ?> course<?= $total_courses !== 1 ? 's' : '' ?> scheduled
      </span>
      <span class="badge badge-gold"><?= count($exams) ?> exam<?= count($exams) !== 1 ? 's' : '' ?></span>
    </div>
  </div>

  <div class="page-content">

    <!-- ═══════════════════════════════════════════════════════
         CARD 1 — 2D WEEKLY CLASS MATRIX
    ═══════════════════════════════════════════════════════ -->
    <div class="card mb-6">
      <div class="card-header">
        <div>
          <h2>📆 Weekly Class Routine — 2D Matrix</h2>
          <p>Rows: time slots · Columns: days (Sun – Thu)</p>
        </div>
        <span class="badge badge-primary">SP26</span>
      </div>
      <div class="card-body" style="padding:16px;">

        <div class="matrix-wrap">
        <table class="routine-matrix">
          <thead>
            <tr>
              <th>⏰ Time</th>
              <?php foreach ($DAYS as $day): ?>
              <th><?= $DAY_SHORT[$day] ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>

            <?php foreach ($TIME_SLOTS as $t_key => $t_label): ?>
            <tr>
              <!-- Time-slot label -->
              <td class="time-label"><?= $t_label ?></td>

              <!-- One cell per day -->
              <?php foreach ($DAYS as $day):
                $cells = $matrix[$t_key][$day];
                $has   = !empty($cells);
              ?>
              <td class="slot-cell <?= $has ? 'has-class' : '' ?>">
                <?php if ($has): ?>
                  <?php foreach ($cells as $entry):
                    $cc  = $entry['course_code'];
                    $rm  = $entry['room_no'];
                    // Check if this course has an exam in AcademicRoutine
                    $has_exam = isset($course_exam_map[$cc]);
                    $exam_lbl = '';
                    if ($has_exam) {
                        $ex_d = $course_exam_map[$cc]['Exam_date'];
                        $exam_lbl = date('d M', strtotime($ex_d));
                    }
                  ?>
                  <div class="course-box">
                    <span class="cb-code"><?= htmlspecialchars($cc) ?></span>
                    <span class="cb-room">🚪 <?= htmlspecialchars($rm) ?></span>
                    <?php if ($has_exam): ?>
                    <span class="cb-exam">📝 <?= $exam_lbl ?></span>
                    <?php endif; ?>
                  </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span style="color:#d1d5db;font-size:18px;">·</span>
                <?php endif; ?>
              </td>
              <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>

            <!-- ── EXAM FOOTER ROW ──────────────────────────── -->
            <?php if (!empty($exams)): ?>
            <tr class="exam-row">
              <td class="time-label">📝 Exams</td>
              <?php foreach ($DAYS as $day):
                $day_exams = $exam_day_map[$day] ?? [];
              ?>
              <td>
                <?php foreach ($day_exams as $ex): ?>
                <div class="exam-badge">
                  <span class="eb-type"><?= htmlspecialchars($ex['Exam_type']) ?></span>
                  <span><?= date('d M Y', strtotime($ex['Exam_date'])) ?></span>
                  <span><?= date('h:i A', strtotime($ex['Start_time'])) ?></span>
                  <span>🚪 <?= htmlspecialchars($ex['Room_number']) ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($day_exams)): ?>
                <span style="color:#d1d5db;font-size:18px;">·</span>
                <?php endif; ?>
              </td>
              <?php endforeach; ?>
            </tr>
            <?php endif; ?>

          </tbody>
        </table>
        </div><!-- /matrix-wrap -->

        <!-- Legend -->
        <div class="matrix-legend">
          <div class="leg">
            <div style="width:16px;height:16px;background:var(--primary);border-radius:4px;"></div>
            Class slot (Course Code · Room)
          </div>
          <div class="leg">
            <div style="width:16px;height:16px;background:#f0f7ff;border:1px solid var(--border);border-radius:4px;"></div>
            Active class day
          </div>
          <div class="leg">
            <span style="font-size:9px;background:var(--accent);color:#fff;border-radius:4px;padding:1px 5px;">📝 dd Mon</span>
            Exam date (from Academic Routine)
          </div>
          <div class="leg">
            <div style="width:16px;height:16px;background:#fff3d6;border:1px solid #fcd34d;border-radius:4px;"></div>
            Exam footer row
          </div>
        </div>

      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         CARD 2 — EXAM SCHEDULE (Full Detail)
    ═══════════════════════════════════════════════════════ -->
    <div class="card exam-table-card">
      <div class="card-header">
        <div>
          <h2>📝 Exam Schedule</h2>
          <p>All upcoming and past exams fetched from the Exam table</p>
        </div>
        <span class="badge badge-gold"><?= count($exams) ?> record<?= count($exams) !== 1 ? 's' : '' ?></span>
      </div>

      <?php if (!empty($exams)): ?>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Type</th>
              <th>Date</th>
              <th>Time</th>
              <th>Room</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($exams as $i => $ex):
            $is_past   = strtotime($ex['Exam_date']) < strtotime('today');
            $is_today  = date('Y-m-d', strtotime($ex['Exam_date'])) === date('Y-m-d');
          ?>
          <tr style="<?= $is_today ? 'background:#fff3d6;' : '' ?>">
            <td style="color:var(--text-muted);"><?= $i+1 ?></td>
            <td>
              <?php if ($ex['Exam_type'] === 'Midterm'): ?>
                <span class="badge badge-gold">Midterm</span>
              <?php elseif ($ex['Exam_type'] === 'Final'): ?>
                <span class="badge badge-primary">Final</span>
              <?php else: ?>
                <span class="badge badge-warning"><?= htmlspecialchars($ex['Exam_type']) ?></span>
              <?php endif; ?>
            </td>
            <td class="fw-bold"><?= date('D, d M Y', strtotime($ex['Exam_date'])) ?></td>
            <td><?= date('h:i A', strtotime($ex['Start_time'])) ?></td>
            <td><?= htmlspecialchars($ex['Room_number']) ?></td>
            <td>
              <?php if ($is_today): ?>
                <span class="badge badge-danger">📌 Today</span>
              <?php elseif ($is_past): ?>
                <span class="badge" style="background:#e8f5ee;color:var(--success);">✓ Done</span>
              <?php else: ?>
                <span class="badge badge-warning">⏳ Upcoming</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if (!empty($course_exam_map)): ?>
      <div class="card-body" style="border-top:1px solid var(--border); padding:16px 24px;">
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-bottom:10px;">
          Course Exam Details (from Academic Routine)
        </div>
        <div style="display:flex; flex-wrap:wrap; gap:10px;">
        <?php foreach ($course_exam_map as $cc => $em): ?>
        <div style="background:#fffbeb; border:1px solid #fcd34d; border-radius:10px; padding:10px 14px; min-width:180px;">
          <div class="fw-bold" style="color:#92400e;"><?= htmlspecialchars($cc) ?></div>
          <div style="font-size:12px; color:var(--text-muted); margin-top:3px;">
            📅 <?= date('d M Y', strtotime($em['Exam_date'])) ?>
          </div>
          <div style="font-size:11px; color:#b45309; margin-top:2px;">
            <?= htmlspecialchars($em['Exam_routine'] ?? '') ?>
          </div>
          <div style="margin-top:4px;">
            <span class="badge badge-gold" style="font-size:10px;"><?= htmlspecialchars($em['Attribute'] ?? '') ?></span>
          </div>
        </div>
        <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php else: ?>
      <div class="card-body">
        <div class="empty-state">
          <div class="empty-icon">🎉</div>
          <p>No exam records found for your account.</p>
        </div>
      </div>
      <?php endif; ?>
    </div>

  </div><!-- /page-content -->
</div><!-- /main -->
</div><!-- /layout -->
</body>
</html>
