(function(){
  'use strict';
  const auth = API.getAuth();
  if (!auth || auth.user.role !== 'customer') { location.href = './login.html'; }

  const tbody = document.getElementById('tbody');
  const tbodyOrders = document.getElementById('tbody-orders');
  const search = document.getElementById('search');
  const btnRefresh = document.getElementById('btn-refresh');
  const btnLogout = document.getElementById('btn-logout');

  let sims = [];
  let orders = [];

  function renderSims(){
    const kw = (search.value || '').toLowerCase().trim();
    const filtered = !kw ? sims : sims.filter(s => (s.so+"").toLowerCase().includes(kw) || (s.nha_mang+"").toLowerCase().includes(kw));
    tbody.innerHTML = '';
    for (const s of filtered) {
      const tr = document.createElement('tr');
      const disabled = s.trang_thai !== 'con_hang';
      tr.innerHTML = `
        <td>${Utils.escapeHtml(s.so)}</td>
        <td>${Utils.escapeHtml(s.nha_mang)}</td>
        <td>${Utils.currency(s.gia)}</td>
        <td>${disabled ? '<span class="badge badge-gray">Het hang</span>' : '<span class="badge badge-green">Con hang</span>'}</td>
        <td class="actions"><button data-id="${s.id}" ${disabled ? 'disabled' : ''}>Dat mua</button></td>`;
      tbody.appendChild(tr);
    }
  }

  function renderOrders(){
    tbodyOrders.innerHTML='';
    for (const o of orders) {
      const tr = document.createElement('tr');
      const canCancel = o.status === 'cho_xu_ly';
      tr.innerHTML = `
        <td>${Utils.escapeHtml(o.id)}</td>
        <td>${Utils.escapeHtml(o.so)}</td>
        <td>${Utils.currency(o.gia)}</td>
        <td>${Utils.escapeHtml(o.status)} ${canCancel ? `<button data-cancel="${o.id}" class="inline-btn">Huy</button>` : ''}</td>
        <td>${Utils.escapeHtml(o.created_at)}</td>`;
      tbodyOrders.appendChild(tr);
    }
  }

  async function load(){
    sims = await API.listSims();
    try { orders = await API.listOrders(); } catch(_) { orders = []; }
    renderSims();
    renderOrders();
  }

  async function onOrder(e){
    const btn = e.target.closest('button[data-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    try {
      await API.createOrder(id);
      await load();
      alert('Dat hang thanh cong');
    } catch(err){ alert(err.message); }
  }

  async function onCancel(e){
    const btn = e.target.closest('button[data-cancel]');
    if (!btn) return;
    const id = btn.getAttribute('data-cancel');
    if (!confirm('Ban co chac muon huy don nay?')) return;
    try { await API.cancelOrder(id); await load(); } catch(err){ alert(err.message); }
  }

  function init(){
    load();
    tbody.addEventListener('click', onOrder);
    tbodyOrders.addEventListener('click', onCancel);
    btnRefresh.addEventListener('click', load);
    search.addEventListener('input', renderSims);
    btnLogout.addEventListener('click', async ()=>{ await API.logout(); location.href='./login.html'; });
  }

  document.addEventListener('DOMContentLoaded', init);
})();
