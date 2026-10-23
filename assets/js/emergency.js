/**
 * Emergency Roll-Call & Evacuation Muster Controller
 */

async function loadMusterData(sessionId = 0) {
  const container = document.getElementById('musterTableBody');
  if (!container) return;

  try {
    const res = await fetch(`../api/emergency.php?action=get_muster_list&session_id=${sessionId}`);
    const data = await res.json();

    if (!data.success) {
      showToast(data.message || 'Error loading muster list', 'danger');
      return;
    }

    if (data.is_active_session) {
      // Update counters
      document.getElementById('countTotal').textContent = data.counts.total;
      document.getElementById('countSafe').textContent = data.counts.safe;
      document.getElementById('countMissing').textContent = data.counts.missing;
      document.getElementById('countInjured').textContent = data.counts.injured;
      document.getElementById('countUnaccounted').textContent = data.counts.unaccounted;

      // Populate rows
      container.innerHTML = data.data.map(item => `
        <tr id="muster-row-${item.id}">
          <td><strong>${item.person_name}</strong></td>
          <td><span class="badge ${item.person_type === 'student' ? 'badge-approved' : (item.person_type === 'staff' ? 'badge-used' : 'badge-in')}">${item.person_type.toUpperCase()}</span></td>
          <td>${item.category_group}</td>
          <td>
            <span class="badge badge-${item.muster_status}" id="badge-status-${item.id}">
              ${item.muster_status.toUpperCase()}
            </span>
          </td>
          <td>
            <div style="display:flex; gap:6px;">
              <button class="btn btn-sm btn-success" onclick="updateMusterStatus(${item.id}, 'safe')">✓ Safe</button>
              <button class="btn btn-sm btn-danger" onclick="updateMusterStatus(${item.id}, 'missing')">⚠ Missing</button>
              <button class="btn btn-sm btn-warning" onclick="updateMusterStatus(${item.id}, 'injured')">🩹 Injured</button>
            </div>
          </td>
        </tr>
      `).join('');
    } else {
      // No active drill session: show live on-campus people snapshot
      container.innerHTML = data.data.map(item => `
        <tr>
          <td><strong>${item.full_name}</strong></td>
          <td><span class="badge ${item.type === 'student' ? 'badge-approved' : (item.type === 'staff' ? 'badge-used' : 'badge-in')}">${item.type.toUpperCase()}</span></td>
          <td>${item.group_name}</td>
          <td><span class="badge badge-in">ON CAMPUS</span></td>
          <td><span class="text-muted">Start Drill to Account</span></td>
        </tr>
      `).join('');
    }
  } catch(e) {
    showToast('Failed to retrieve emergency muster data', 'danger');
  }
}

async function updateMusterStatus(recordId, status) {
  const formData = new FormData();
  formData.append('record_id', recordId);
  formData.append('status', status);

  try {
    const res = await fetch('../api/emergency.php?action=update_status', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      showToast(`Status updated to ${status.toUpperCase()}`, 'success');
      loadMusterData();
    } else {
      showToast(data.message, 'danger');
    }
  } catch(e) {
    showToast('Network error', 'danger');
  }
}

async function startEmergencyDrill() {
  if (!confirm('🚨 ACTIVATE EMERGENCY ROLL-CALL MODE?\n\nThis will take an instant snapshot of everyone currently inside the school compound (Students, Staff, Visitors) and initiate live evacuation accounting.')) {
    return;
  }

  const formData = new FormData();
  formData.append('title', 'Emergency Evacuation & Fire Drill (' + new Date().toLocaleString() + ')');

  try {
    const res = await fetch('../api/emergency.php?action=start_drill', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      showToast(data.message, 'warning');
      window.location.reload();
    } else {
      showToast(data.message, 'danger');
    }
  } catch(e) {
    showToast('Failed to start emergency drill', 'danger');
  }
}

async function endEmergencyDrill() {
  if (!confirm('End Emergency Evacuation and return to normal gate operations?')) return;

  try {
    const res = await fetch('../api/emergency.php?action=end_drill', { method: 'POST' });
    const data = await res.json();
    if (data.success) {
      showToast(data.message, 'success');
      window.location.reload();
    }
  } catch(e) {
    showToast('Failed to end drill', 'danger');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('musterTableBody')) {
    loadMusterData();
  }
});
