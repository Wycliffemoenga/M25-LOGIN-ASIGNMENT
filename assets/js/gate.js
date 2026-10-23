/**
 * Gate Terminal & Barcode/QR Scanner Controller
 * Fulfills < 5-second check-in processing requirement
 */

let activeDirection = 'auto'; // 'auto', 'in', 'out'
let lastScannedPerson = null;

function setGateDirection(dir) {
  activeDirection = dir;
  document.querySelectorAll('.mode-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.dir === dir);
  });
  const input = document.getElementById('gateScanInput');
  if (input) input.focus();
}

async function handleScanSubmit(e) {
  if (e) e.preventDefault();
  const input = document.getElementById('gateScanInput');
  const query = input.value.trim();
  if (!query) return;

  const feedbackArea = document.getElementById('scanFeedbackArea');
  feedbackArea.className = 'scan-feedback-area';
  feedbackArea.innerHTML = `<div style="color:#94a3b8; font-size:16px;">Processing scan...</div>`;

  try {
    const res = await fetch('../api/scan.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        query: query,
        direction: activeDirection === 'auto' ? '' : activeDirection,
        method: query.startsWith('CARD-') ? 'card_scan' : (query.startsWith('ADM-') ? 'qr_scan' : 'manual_search')
      })
    });

    const data = await res.json();
    input.value = '';
    input.focus();

    if (data.requires_permit) {
      // Early exit without approved permit
      SoundEffects.playAlert();
      feedbackArea.className = 'scan-feedback-area warning';
      feedbackArea.innerHTML = `
        <div style="text-align:center;">
          <div style="font-size:24px; margin-bottom:4px;">⚠️ NO EXIT PERMIT FOUND</div>
          <div style="font-size:18px; font-weight:700; color:#fff;">${data.person.name} (${data.person.class})</div>
          <p style="color:#fde68a; margin:8px 0;">Attempted early exit during school hours without an approved Gate Pass!</p>
          <div style="display:flex; justify-content:center; gap:10px; margin-top:12px;">
            <button class="btn btn-warning btn-sm" onclick="overrideEarlyExit('${data.person.identifier}')">Guard Override Exit</button>
            <button class="btn btn-outline btn-sm" style="color:#fff;" onclick="resetFeedback()">Cancel</button>
          </div>
        </div>
      `;
      showToast(data.message, 'warning');
      return;
    }

    if (!data.success) {
      SoundEffects.playAlert();
      feedbackArea.className = 'scan-feedback-area warning';
      feedbackArea.innerHTML = `
        <div>
          <div style="font-size:22px; margin-bottom:6px;">❌ Scan Not Recognized</div>
          <div style="color:#f87171; font-weight:600;">${data.message}</div>
        </div>
      `;
      showToast(data.message, 'danger');
      return;
    }

    // Success response
    lastScannedPerson = data;
    if (data.is_late) {
      SoundEffects.playLate();
      feedbackArea.className = 'scan-feedback-area late';
    } else {
      SoundEffects.playSuccess();
      feedbackArea.className = 'scan-feedback-area success';
    }

    const dirBadgeClass = data.direction === 'in' ? 'badge-in' : 'badge-out';
    const dirText = data.direction === 'in' ? 'ENTRY (IN)' : 'EXIT (OUT)';

    feedbackArea.innerHTML = `
      <div style="display:flex; align-items:center; justify-content:space-between; width:100%; max-width:650px; text-align:left;">
        <div style="display:flex; align-items:center; gap:16px;">
          <div style="width:60px; height:60px; border-radius:50%; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; font-size:28px;">
            ${data.person_type === 'student' ? '🎓' : '💼'}
          </div>
          <div>
            <div style="font-size:20px; font-weight:800; color:#fff;">${data.person.name}</div>
            <div style="font-size:13px; color:#cbd5e1;">${data.person.identifier} &bull; ${data.person.group}</div>
            <div style="margin-top:6px; display:flex; gap:8px;">
              <span class="badge ${dirBadgeClass}" style="font-size:12px; padding:4px 10px;">${dirText}</span>
              ${data.is_late ? `<span class="badge badge-late" style="font-size:12px; padding:4px 10px;">⏰ LATE ARRIVAL</span>` : ''}
              ${data.permit_id ? `<span class="badge badge-approved" style="font-size:12px;">Gate Pass #${data.permit_id}</span>` : ''}
            </div>
          </div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:12px; color:#94a3b8;">${data.timestamp}</div>
          ${data.notification_sent ? `<div style="font-size:11px; color:#86efac; margin-top:4px;">📲 Parent SMS Alert Dispatched</div>` : ''}
          ${data.is_late ? `<button class="btn btn-sm btn-outline" style="color:#fff; margin-top:8px;" onclick="openLateReasonModal(${data.person.id})">Edit Reason</button>` : ''}
        </div>
      </div>
    `;

    showToast(data.message, data.is_late ? 'warning' : 'success');

    // Prepend new row to the gate live activity table if present
    prependGateLogRow(data);

  } catch (err) {
    SoundEffects.playAlert();
    feedbackArea.className = 'scan-feedback-area warning';
    feedbackArea.innerHTML = `<div>Error connecting to gate server.</div>`;
    showToast('Network error during scan.', 'danger');
  }
}

function resetFeedback() {
  const feedbackArea = document.getElementById('scanFeedbackArea');
  if (feedbackArea) {
    feedbackArea.className = 'scan-feedback-area';
    feedbackArea.innerHTML = `
      <div style="color: #94a3b8;">
        <div style="font-size: 24px; margin-bottom: 6px;">📷 Ready to Scan</div>
        <p style="font-size: 13px;">Scan barcode / QR code or search admission number. Response in &lt; 5 seconds.</p>
      </div>
    `;
  }
}

async function overrideEarlyExit(identifier) {
  const input = document.getElementById('gateScanInput');
  input.value = identifier;
  try {
    const res = await fetch('../api/scan.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        query: identifier,
        direction: 'out',
        override_permit: true
      })
    });
    const data = await res.json();
    handleScanDataResponse(data);
  } catch(e) {}
}

function simulateQuickScan(cardNumber) {
  const input = document.getElementById('gateScanInput');
  if (input) {
    input.value = cardNumber;
    handleScanSubmit();
  }
}

function prependGateLogRow(data) {
  const tbody = document.getElementById('gateLogsTableBody');
  if (!tbody) return;

  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><strong>${data.person.name}</strong></td>
    <td><span class="badge ${data.person_type === 'student' ? 'badge-approved' : 'badge-used'}">${data.person_type}</span></td>
    <td>${data.person.identifier}</td>
    <td>${data.person.group}</td>
    <td><span class="badge ${data.direction === 'in' ? 'badge-in' : 'badge-out'}">${data.direction.toUpperCase()}</span></td>
    <td>${data.is_late ? '<span class="badge badge-late">LATE</span>' : '<span style="color:#10b981;">On Time</span>'}</td>
    <td>${data.timestamp}</td>
  `;
  tbody.insertBefore(tr, tbody.firstChild);
}

// Attach gate scanner listeners
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('gateScanForm');
  if (form) {
    form.addEventListener('submit', handleScanSubmit);
  }
  const input = document.getElementById('gateScanInput');
  if (input) {
    input.focus();
  }
});
