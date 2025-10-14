(function() {
  'use strict';

  const API_URL = '/backend/api/sims.php';

  const tbody = document.getElementById('tbody');
  const form = document.getElementById('form-add');
  const formMsg = document.getElementById('form-msg');
  const searchInput = document.getElementById('search');
  const btnRefresh = document.getElementById('btn-refresh');

  let allSims = [];

  async function fetchSims() {
    const res = await fetch(API_URL);
    const json = await res.json();
    if (json.success) {
      allSims = json.data || [];
      renderTable();
    }
  }

  function formatCurrency(value) {
    try {
      return new Intl.NumberFormat('vi-VN').format(value);
    } catch (_) {
      return value;
    }
  }

  function renderTable() {
    const keyword = (searchInput.value || '').toLowerCase().trim();
    const filtered = !keyword ? allSims : allSims.filter(s => {
      return (s.so + '').toLowerCase().includes(keyword) || (s.nha_mang + '').toLowerCase().includes(keyword);
    });

    tbody.innerHTML = '';
    for (const s of filtered) {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${escapeHtml(s.so)}</td>
        <td>${escapeHtml(s.nha_mang)}</td>
        <td>${formatCurrency(s.gia)}</td>
        <td>
          ${s.trang_thai === 'con_hang' ? '<span class="badge badge-green">Con hang</span>' : '<span class="badge badge-gray">Het hang</span>'}
        </td>
        <td class="actions"><button data-id="${s.id}">Xoa</button></td>
      `;
      tbody.appendChild(tr);
    }
  }

  function escapeHtml(s) {
    return (s + '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#39;');
  }

  async function addSim(evt) {
    evt.preventDefault();
    formMsg.textContent = '';

    const so = document.getElementById('so').value.trim();
    const nha_mang = document.getElementById('nha_mang').value;
    const gia = Number(document.getElementById('gia').value);
    const trang_thai = document.getElementById('trang_thai').value;

    if (!so || !nha_mang || !(gia > 0)) {
      formMsg.textContent = 'Vui long nhap du lieu hop le';
      return;
    }

    const res = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ so, nha_mang, gia, trang_thai })
    });

    const json = await res.json();
    if (!json.success) {
      formMsg.textContent = json.message || 'Them that bai';
      return;
    }

    // Reset form and refresh
    form.reset();
    await fetchSims();
  }

  async function onTableClick(evt) {
    const btn = evt.target.closest('button[data-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    if (!confirm('Ban chac chan muon xoa sim nay?')) return;

    const res = await fetch(API_URL, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const json = await res.json();
    if (json.success) {
      await fetchSims();
    } else {
      alert(json.message || 'Xoa that bai');
    }
  }

  function init() {
    fetchSims();
    form.addEventListener('submit', addSim);
    tbody.addEventListener('click', onTableClick);
    btnRefresh.addEventListener('click', fetchSims);
    searchInput.addEventListener('input', renderTable);
  }

  document.addEventListener('DOMContentLoaded', init);
})();
