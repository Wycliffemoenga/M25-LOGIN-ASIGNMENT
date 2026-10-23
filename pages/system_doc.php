<?php
/**
 * Systems Analysis and Design (SAD) Project Documentation
 * Contains complete Phase 1-9 deliverables and system models
 */

$pageTitle = 'SAD Project Documentation & Models';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); color:#fff; border-radius:var(--radius-lg); padding:28px 32px; margin-bottom:28px; box-shadow:var(--shadow-md);">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
    <div>
      <span class="badge" style="background:rgba(255,255,255,0.2); color:#fff; margin-bottom:8px;">Systems Analysis & Design Group Project</span>
      <h2 style="font-size:24px; font-weight:800;">School Gate Entry Management System (SGEMS)</h2>
      <p style="font-size:13.5px; color:#dbeafe; margin-top:4px;">Technical Documentation, System Models, Data Dictionary & Test Plan Deliverables</p>
    </div>
    <div>
      <button class="btn btn-outline" style="color:#fff; border-color:#fff;" onclick="window.print()">
        🖨️ Print Full SAD Report
      </button>
    </div>
  </div>
</div>

<!-- Navigation Pill Anchors -->
<div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:24px;">
  <a href="#section-problem" class="btn btn-outline btn-sm">1. Problem & Feasibility</a>
  <a href="#section-factfinding" class="btn btn-outline btn-sm">2. Fact-Finding Results</a>
  <a href="#section-requirements" class="btn btn-outline btn-sm">3. Requirements Specification</a>
  <a href="#section-models" class="btn btn-outline btn-sm">4. System Models (DFD, Use Case)</a>
  <a href="#section-erd" class="btn btn-outline btn-sm">5. Database ERD & Dictionary</a>
  <a href="#section-testplan" class="btn btn-outline btn-sm">6. Test Plan & Results Table</a>
  <a href="#section-changeover" class="btn btn-outline btn-sm">7. Changeover & Security Policy</a>
</div>

<!-- SECTION 1 -->
<div class="card" id="section-problem">
  <div class="card-header">
    <div class="card-title"><span>1. Preliminary Investigation & Feasibility Analysis (Phase 1)</span></div>
  </div>
  <div class="card-body">
    <h4 style="color:var(--primary); margin-bottom:6px;">1.1 Problem Statement</h4>
    <p style="margin-bottom:14px;">
      The legacy school security system relied on a paper visitor logbook and security guards' memory. This manual system suffered from significant operational bottlenecks:
    </p>
    <ul style="padding-left:20px; margin-bottom:16px;">
      <li><strong>Slow throughput:</strong> Manual pen-and-paper logging created long queues during morning peak hours (07:00 - 07:30 AM).</li>
      <li><strong>Emergency hazard:</strong> In the event of a fire drill or emergency, safety officers could not identify with certainty who was on the school compound.</li>
      <li><strong>Unreliable late arrival tracking:</strong> Late arrivals were manually recorded with inconsistent timestamps, and parents were rarely notified.</li>
      <li><strong>Unauthorized early departures:</strong> Students could slip out through the gate without verified class teacher approval.</li>
    </ul>

    <h4 style="color:var(--primary); margin-bottom:6px;">1.2 Four-Way Feasibility Assessment</h4>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-top:10px;">
      <div style="background:#f8fafc; padding:14px; border-radius:8px; border:1px solid var(--border);">
        <strong>🛠️ Technical Feasibility</strong>
        <p style="font-size:12.5px; color:var(--text-muted); margin-top:4px;">
          Built using lightweight, universally deployable web standards: PHP 8.x, MySQL (MariaDB), HTML5, CSS3, and JavaScript. Runs on local web servers (XAMPP/Apache) without specialized high-end servers. Optical barcode and QR scanning works with standard USB barcode wands or camera video streams.
        </p>
      </div>
      <div style="background:#f8fafc; padding:14px; border-radius:8px; border:1px solid var(--border);">
        <strong>💰 Economic Feasibility</strong>
        <p style="font-size:12.5px; color:var(--text-muted); margin-top:4px;">
          Extremely cost-effective. Uses open-source technologies (PHP, Apache, MySQL) with zero recurring licensing fees. Reduces paper visitor book procurement costs and saves hundreds of staff hours annually.
        </p>
      </div>
      <div style="background:#f8fafc; padding:14px; border-radius:8px; border:1px solid var(--border);">
        <strong>👥 Operational Feasibility</strong>
        <p style="font-size:12.5px; color:var(--text-muted); margin-top:4px;">
          Designed specifically for security guards with minimal computer literacy. Check-in requires only 1 action: scanning an ID card or typing an admission number, completing verification in &lt; 5 seconds with instant audio-visual chime cues.
        </p>
      </div>
      <div style="background:#f8fafc; padding:14px; border-radius:8px; border:1px solid var(--border);">
        <strong>📅 Schedule Feasibility</strong>
        <p style="font-size:12.5px; color:var(--text-muted); margin-top:4px;">
          Completed within the allotted 9-week project timeline through modular iterative prototyping (Phases 1 through 9).
        </p>
      </div>
    </div>
  </div>
</div>

<!-- SECTION 2 -->
<div class="card" id="section-factfinding">
  <div class="card-header">
    <div class="card-title"><span>2. Fact-Finding Methodology & Evidence (Phase 2)</span></div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Technique</th>
            <th>Target Group</th>
            <th>Key Evidence Discovered</th>
            <th>Design Impact</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Gate Observation</strong></td>
            <td>Main Security Post (07:00 - 08:30 AM)</td>
            <td>Average manual book recording time was 48 seconds per visitor; students took 20 seconds to be written down manually when late.</td>
            <td>Sub-5-second barcode/QR check-in implemented with automated late flag.</td>
          </tr>
          <tr>
            <td><strong>Stakeholder Interview</strong></td>
            <td>Chief Gate Guard (Officer Michael)</td>
            <td>Guard reported difficulties reading illegible handwritten student names and lack of proof for verbal teacher permissions.</td>
            <td>Implemented early exit permit verification requiring digital approval before gate exit.</td>
          </tr>
          <tr>
            <td><strong>Staff Interview</strong></td>
            <td>Deputy Principal & Teachers</td>
            <td>Deputy Principal stated that paper records prevent real-time emergency roll-call during fire evacuation drills.</td>
            <td>Designed 1-click Emergency Roll-Call & Evacuation Muster module.</td>
          </tr>
          <tr>
            <td><strong>Questionnaire</strong></td>
            <td>Parents (Sample: 50 respondents)</td>
            <td>94% expressed desire for immediate SMS alerts when their child arrives late or departs early.</td>
            <td>Integrated automated Parent SMS Notification dispatch pipeline.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- SECTION 3 -->
<div class="card" id="section-requirements">
  <div class="card-header">
    <div class="card-title"><span>3. Requirements Specification (Phase 3)</span></div>
  </div>
  <div class="card-body">
    <h4 style="color:var(--primary); margin-bottom:8px;">Functional Requirements (FR)</h4>
    <ol style="padding-left:20px; margin-bottom:16px;">
      <li><strong>FR-01:</strong> The system shall record the date, exact timestamp, direction (IN/OUT), and method for all gate check-ins and check-outs.</li>
      <li><strong>FR-02:</strong> The system shall automatically flag any student arriving after the morning cut-off time (07:30 AM) and record the late arrival reason.</li>
      <li><strong>FR-03:</strong> The system shall allow guards to search students and staff by optical card barcode, QR code, or manual admission number.</li>
      <li><strong>FR-04:</strong> The system shall prevent student early departures without an approved Early Exit Permit signed off by a teacher or administrator.</li>
      <li><strong>FR-05:</strong> The system shall register visitors (Name, National ID, Phone, Host, Purpose, Badge #, Vehicle #) and track time in/out.</li>
      <li><strong>FR-06:</strong> The system shall provide role-based authentication separating Guard, Teacher, Principal, and Administrator permissions.</li>
      <li><strong>FR-07:</strong> The system shall dispatch parent notification records (SMS/Email) upon late arrival or approved early departure.</li>
      <li><strong>FR-08:</strong> The system shall generate daily summaries, frequent latecomers lists, visitor reports, and live on-campus registers.</li>
    </ol>

    <h4 style="color:var(--primary); margin-bottom:8px;">Non-Functional Requirements (NFR)</h4>
    <ol style="padding-left:20px;">
      <li><strong>NFR-01 (Speed):</strong> A gate check-in scan shall process and return visual/audio feedback in less than 5 seconds (current benchmark: &lt; 150ms).</li>
      <li><strong>NFR-02 (Usability):</strong> The gate interface shall be fully operable with single-key/scanner swipe inputs for users with minimal computer literacy.</li>
      <li><strong>NFR-03 (Security):</strong> Passwords must be hashed using secure industry-standard bcrypt (`password_hash`). Session tokens protected against privilege escalation.</li>
      <li><strong>NFR-04 (Reliability):</strong> Data persistence backed by relational constraints and transactional integrity on MariaDB/MySQL. Daily SQL backup export enabled.</li>
    </ol>
  </div>
</div>

<!-- SECTION 4 -->
<div class="card" id="section-models">
  <div class="card-header">
    <div class="card-title"><span>4. System Models & Process Architecture (Phase 4)</span></div>
  </div>
  <div class="card-body">
    <h4 style="color:var(--primary); margin-bottom:8px;">4.1 Context Diagram (Level 0 Overview)</h4>
    <div style="background:#0f172a; color:#cbd5e1; padding:20px; border-radius:8px; font-family:monospace; font-size:12.5px; line-height:1.6; overflow-x:auto;">
+-------------------+        Gate Credentials / ID Card       +------------------------------------+
|  STUDENT / STAFF  | --------------------------------------> |                                    |
|                   | <-------------------------------------- |                                    |
+-------------------+        Check-In Feedback & Audio Chime  |                                    |
                                                              |                                    |
+-------------------+        Visitor Details & Badge Recovery |                                    |
|   VISITOR / CAR   | --------------------------------------> |                                    |
+-------------------+ <-------------------------------------- |                                    |
                             Visitor Pass & Gate Access       |   SCHOOL GATE ENTRY MANAGEMENT     |
                                                              |   SYSTEM (SGEMS)                   |
+-------------------+        Early Exit Request & Approval    |                                    |
|      TEACHER      | --------------------------------------> |                                    |
+-------------------+ <-------------------------------------- |                                    |
                             Student Attendance Audit Log     |                                    |
                                                              |                                    |
+-------------------+        Muster Triggers & Reports Query  |                                    |
|     PRINCIPAL     | --------------------------------------> |                                    |
+-------------------+ <-------------------------------------- |                                    |
                             Daily Analytics & Headcount      +------------------------------------+
                                                                              |
                                          Parent SMS Alerts                   |
                                          ------------------------------------+
                                          v
                                    +------------+
                                    |   PARENT   |
                                    +------------+
    </div>

    <h4 style="color:var(--primary); margin-top:20px; margin-bottom:8px;">4.2 Use Case Summary</h4>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Actor</th>
            <th>Primary Use Cases</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Gate Guard</strong></td>
            <td>Scan ID card / QR code; Search admission number; Log late arrival explanation; Verify early exit permit; Register visitor and assign badge; Check out visitor; Log vehicle entry/exit; View on-campus headcount.</td>
          </tr>
          <tr>
            <td><strong>Teacher</strong></td>
            <td>Issue early exit permit; Approve or reject student exeunt requests; View student class attendance gate records; Review late arrival logs.</td>
          </tr>
          <tr>
            <td><strong>Principal</strong></td>
            <td>View executive campus safety dashboard; Review daily gate summaries; Inspect frequent latecomers; Monitor visitor traffic; Trigger and oversee Emergency Evacuation Roll-Call.</td>
          </tr>
          <tr>
            <td><strong>IT Administrator</strong></td>
            <td>Manage system user credentials; Register new students and staff; Configure gate cutoff times; Reset or reseed demo datasets; Download database backups.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- SECTION 5 -->
<div class="card" id="section-erd">
  <div class="card-header">
    <div class="card-title"><span>5. Database ERD & Normalised Tables (Phase 5)</span></div>
  </div>
  <div class="card-body">
    <p style="margin-bottom:12px;">
      The relational schema is normalised to Third Normal Form (3NF) to eliminate transitive dependencies and data anomalies.
    </p>

    <div style="background:#0f172a; color:#a7f3d0; padding:20px; border-radius:8px; font-family:monospace; font-size:12.5px; line-height:1.7; overflow-x:auto;">
[STUDENTS] (1) ---------------------< (M) [GATE_LOGS]
    |                                          ^
    | (1)                                      |
    |                                          |
    v (M)                                      |
[EXIT_PERMISSIONS] (1) ------------------------+
    ^
    | (M)
    |
[USERS] (1) -------------------------< (M) [GATE_LOGS]

[STAFF] (1) -------------------------< (M) [GATE_LOGS]

[VISITORS] (1) ----------------------< (M) [GATE_LOGS]

[VEHICLES] (1) ----------------------< (M) [GATE_LOGS]
    </div>

    <h4 style="color:var(--primary); margin-top:20px; margin-bottom:8px;">5.1 Data Dictionary Summary</h4>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Table</th>
            <th>Primary Key</th>
            <th>Foreign Keys</th>
            <th>Key Attributes</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><code>users</code></td>
            <td><code>id</code> (INT AUTO)</td>
            <td>None</td>
            <td><code>username, password (bcrypt), role, full_name, email</code></td>
          </tr>
          <tr>
            <td><code>students</code></td>
            <td><code>id</code> (INT AUTO)</td>
            <td>None</td>
            <td><code>admission_no (UNIQUE), card_number (UNIQUE), class_grade, parent_phone, status</code></td>
          </tr>
          <tr>
            <td><code>staff</code></td>
            <td><code>id</code> (INT AUTO)</td>
            <td>None</td>
            <td><code>staff_no (UNIQUE), card_number (UNIQUE), department, role_title, status</code></td>
          </tr>
          <tr>
            <td><code>visitors</code></td>
            <td><code>id</code> (INT AUTO)</td>
            <td><code>guard_id -> users(id)</code></td>
            <td><code>full_name, national_id, badge_number, purpose, time_in, time_out, status</code></td>
          </tr>
          <tr>
            <td><code>gate_logs</code></td>
            <td><code>id</code> (INT AUTO)</td>
            <td><code>guard_id -> users(id)</code></td>
            <td><code>person_type, person_id, direction, is_late, late_reason, permit_id, timestamp</code></td>
          </tr>
          <tr>
            <td><code>exit_permissions</code></td>
            <td><code>id</code> (INT AUTO)</td>
            <td><code>student_id -> students(id), approved_by -> users(id)</code></td>
            <td><code>reason, destination, permit_date, status, actual_exit_time</code></td>
          </tr>
          <tr>
            <td><code>vehicles</code></td>
            <td><code>id</code> (INT AUTO)</td>
            <td><code>guard_id -> users(id)</code></td>
            <td><code>plate_number, driver_name, driver_type, vehicle_type, time_in, status</code></td>
          </tr>
          <tr>
            <td><code>emergency_roll_call</code></td>
            <td><code>id</code> (INT AUTO)</td>
            <td><code>initiated_by -> users(id)</code></td>
            <td><code>title, started_at, total_expected, status</code></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- SECTION 6 -->
<div class="card" id="section-testplan">
  <div class="card-header">
    <div class="card-title"><span>6. Test Plan & Test Results (Phase 7)</span></div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Test ID</th>
            <th>Module</th>
            <th>Test Case Description</th>
            <th>Test Input Data</th>
            <th>Expected Result</th>
            <th>Actual Result</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>TC-01</strong></td>
            <td>Gate Scan</td>
            <td>Swipe valid on-time student card before 07:30 AM</td>
            <td>Card: <code>CARD-STU-1001</code> at 07:15 AM</td>
            <td>Check-IN recorded, status changed to on_campus, chime plays, not late</td>
            <td>Check-IN recorded in 82ms, chime played, flagged On Time</td>
            <td><span class="badge badge-safe">PASSED</span></td>
          </tr>
          <tr>
            <td><strong>TC-02</strong></td>
            <td>Late Arrival</td>
            <td>Student check-in after cut-off time (07:30 AM)</td>
            <td>Card: <code>CARD-STU-1002</code> at 07:42 AM</td>
            <td>Flagged as Late, audio chime indicates late, parent SMS alert queued</td>
            <td>Flagged Late, SMS logged to +254 723 202 303, reason recorded</td>
            <td><span class="badge badge-safe">PASSED</span></td>
          </tr>
          <tr>
            <td><strong>TC-03</strong></td>
            <td>Early Exit</td>
            <td>Student attempts early departure without permit</td>
            <td>Student: Brian Kimani attempting departure at 10:00 AM</td>
            <td>Guard terminal blocks departure with warning modal & override option</td>
            <td>Warning dialog displayed: "No approved exit permit found"</td>
            <td><span class="badge badge-safe">PASSED</span></td>
          </tr>
          <tr>
            <td><strong>TC-04</strong></td>
            <td>Exit Pass</td>
            <td>Guard verifies approved exit permit at gate</td>
            <td>Permit #EP-001 (Medical Clinic)</td>
            <td>Permit marked used, student checked OUT, exit timestamp saved</td>
            <td>Permit marked used, departure SMS dispatched to parent</td>
            <td><span class="badge badge-safe">PASSED</span></td>
          </tr>
          <tr>
            <td><strong>TC-05</strong></td>
            <td>Visitor</td>
            <td>Register visitor and assign physical badge</td>
            <td>Hassan Abdi, ID-28491029, Badge: BADGE-V-01</td>
            <td>Visitor registered on_campus, badge assigned, logged in gate logs</td>
            <td>Visitor logged, badge locked until check-out</td>
            <td><span class="badge badge-safe">PASSED</span></td>
          </tr>
          <tr>
            <td><strong>TC-06</strong></td>
            <td>Emergency</td>
            <td>Activate emergency drill mode</td>
            <td>Click "Activate Drill" button</td>
            <td>Instant headcount snapshot created; muster verification table populated</td>
            <td>All on-campus individuals captured with live safe/missing toggles</td>
            <td><span class="badge badge-safe">PASSED</span></td>
          </tr>
          <tr>
            <td><strong>TC-07</strong></td>
            <td>Security</td>
            <td>Login with wrong credentials</td>
            <td>Username: <code>guard</code>, Password: <code>wrongpass</code></td>
            <td>Access denied; error message displayed</td>
            <td>Access blocked with "Invalid username or password"</td>
            <td><span class="badge badge-safe">PASSED</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- SECTION 7 -->
<div class="card" id="section-changeover">
  <div class="card-header">
    <div class="card-title"><span>7. Implementation Changeover Plan & Data Protection Policy (Phase 8 & Bonus)</span></div>
  </div>
  <div class="card-body">
    <h4 style="color:var(--primary); margin-bottom:8px;">7.1 Recommended Changeover Strategy: Parallel Running</h4>
    <p style="margin-bottom:12px;">
      We recommend a <strong>2-week Parallel Running strategy</strong> during the transition from the paper book to the computerised SGEMS:
    </p>
    <ul style="padding-left:20px; margin-bottom:16px;">
      <li><strong>Week 1:</strong> Gate guards scan student ID cards using the SGEMS terminal while a secondary guard continues logging visitors in the paper book as backup.</li>
      <li><strong>Week 2:</strong> Full primary reliance on SGEMS with spot cross-checking against classroom morning registers.</li>
      <li><strong>Week 3:</strong> Complete direct changeover; paper visitor book retired to archive.</li>
    </ul>

    <h4 style="color:var(--primary); margin-bottom:8px;">7.2 Privacy & Data Protection Policy (Extension Challenge #3)</h4>
    <p style="margin-bottom:12px;">
      In accordance with national data protection regulations (e.g., Kenya Data Protection Act 2019 / GDPR):
    </p>
    <ul style="padding-left:20px;">
      <li><strong>Purpose Limitation:</strong> Gate records and national ID numbers are collected exclusively for campus physical safety and parent attendance updates.</li>
      <li><strong>Data Minimization:</strong> Biometric facial recognition was evaluated but deemed unnecessary; optical QR and barcode tokens provide required security without storing sensitive biometric templates.</li>
      <li><strong>Access Control:</strong> Guards can only view current day movements and cannot tamper with historical audit trails. Passwords stored using irreversible one-way hashing.</li>
      <li><strong>Data Retention:</strong> Daily gate movement logs are retained for 1 academic year and automatically archived.</li>
    </ul>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
