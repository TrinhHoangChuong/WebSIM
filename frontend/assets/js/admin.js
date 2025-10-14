(function(){
  'use strict';
  const auth = API.getAuth();
  if (!auth || auth.user.role !== 'admin') { location.href = './login.html'; }

  const tbody = document.getElementById('tbody');
  const tbodyOrders = document.getElementById('tbody-orders');
  const search = document.getElementById('search');
  const btnRefresh = document.getElementById('btn-refresh');
  const btnLogout = document.getElementById('btn-logout');
  const form = document.getElementById('form-add');
  const formMsg = document.getElementById('form-msg');

  let sims = [];
  let orders = [];

  function renderSims(){
    const kw = (search.value || '').toLowerCase().trim();
    const filtered = !kw ? sims : sims.filter(s => (s.so+"").toLowerCase().includes(kw) || (s.nha_mang+"").toLowerCase().includes(kw));
    tbody.innerHTML = '';
    for (const s of filtered) {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${Utils.escapeHtml(s.so)}</td>
        <td>${Utils.escapeHtml(s.nha_mang)}</td>
        <td>${Utils.currency(s.gia)}</td>
        <td>${s.trang_thai === 'con_hang' ? '<span class="badge badge-green">Con hang</span>' : '<span class="badge badge-gray">Het hang</span>'}</td>
        <td class="actions"><button data-id="${s.id}">Xoa</button></td>`;
      tbody.appendChild(tr);
    }
  }

  function renderOrders(){
    tbodyOrders.innerHTML = '';
    for (const o of orders) {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${Utils.escapeHtml(o.id)}</td>
        <td>${Utils.escapeHtml(o.so)}</td>
        <td>${Utils.currency(o.gia)}</td>
        <td>${Utils.escapeHtml(o.customer_id)}<br><small>${Utils.escapeHtml(o.contact_name||'')} | ${Utils.escapeHtml(o.contact_phone||'')}<br>${Utils.escapeHtml(o.contact_address||'')}</small></td>
        <td>
          <select data-id="${o.id}" class="order-status">
            ${['cho_xu_ly','da_xac_nhan','da_giao','da_huy'].map(s => `<option value="${s}" ${o.status===s?'selected':''}>${s}</option>`).join('')}
          </select>
        </td>
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

  async function onDelete(e){
    const btn = e.target.closest('button[data-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    if (!confirm('Xoa sim nay?')) return;
    try { await API.deleteSim(id); await load(); } catch(err){ alert(err.message); }
  }

  async function onAdd(e){
    e.preventDefault();
    formMsg.textContent = '';
    const so = document.getElementById('so').value.trim();
    const nha_mang = document.getElementById('nha_mang').value;
    const gia = Number(document.getElementById('gia').value);
    const trang_thai = document.getElementById('trang_thai').value;
    if (!so || !nha_mang || !(gia>0)) { formMsg.textContent = 'Nhap du lieu hop le'; return; }
    try {
      await API.addSim({ so, nha_mang, gia, trang_thai });
      form.reset();
      await load();
    } catch(err){ formMsg.textContent = err.message; }
  }

  function init(){
    load();
    tbody.addEventListener('click', onDelete);
    form.addEventListener('submit', onAdd);
    btnRefresh.addEventListener('click', load);
    search.addEventListener('input', renderSims);
    btnLogout.addEventListener('click', async ()=>{ await API.logout(); location.href='./login.html'; });
    tbodyOrders.addEventListener('change', async (e)=>{
      const select = e.target.closest('select.order-status');
      if (!select) return;
      const id = select.getAttribute('data-id');
      const status = select.value;
      try { await API.updateOrderStatus(id, status); } catch(err){ alert(err.message); }
    });
  }

  document.addEventListener('DOMContentLoaded', init);
})();
