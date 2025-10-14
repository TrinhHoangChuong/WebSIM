(function(){
  'use strict';
  const countdownEl = document.getElementById('countdown');
  const numEl = document.getElementById('sim-number');
  const priceEl = document.getElementById('price');
  const priceOldEl = document.getElementById('price-old');
  const btnChange = document.getElementById('btn-change');
  const btnBuy = document.getElementById('btn-buy');
  const msg = document.getElementById('deal-msg');

  let sims = [];
  let current = null;
  let endAt = Date.now() + 60 * 60 * 1000; // 1 hour

  function tick(){
    const remain = Math.max(0, endAt - Date.now());
    const h = String(Math.floor(remain/3600000)).padStart(2,'0');
    const m = String(Math.floor((remain%3600000)/60000)).padStart(2,'0');
    const s = String(Math.floor((remain%60000)/1000)).padStart(2,'0');
    countdownEl.textContent = `${h}:${m}:${s}`;
  }

  function pick(){
    if (sims.length === 0) { current = null; render(); return; }
    const available = sims.filter(s => s.trang_thai === 'con_hang');
    const list = available.length ? available : sims;
    current = list[Math.floor(Math.random()*list.length)];
    render();
  }

  function render(){
    if (!current){
      numEl.textContent = 'Khong co sim';
      priceEl.textContent = '';
      priceOldEl.textContent = '';
      btnBuy.disabled = true;
      return;
    }
    numEl.textContent = Utils.escapeHtml(current.so);
    priceEl.textContent = Utils.currency(current.gia);
    priceOldEl.textContent = Utils.currency(Math.round(current.gia * 1.2));
    btnBuy.disabled = current.trang_thai !== 'con_hang';
  }

  async function load(){
    sims = await API.listSims();
    pick();
  }

  async function buy(){
    msg.textContent = '';
    const auth = API.getAuth();
    if (!auth) { msg.textContent = 'Vui long dang nhap de mua'; return; }
    location.href = `./checkout.html?simId=${encodeURIComponent(current.id)}`;
  }

  function init(){
    load();
    tick();
    setInterval(tick, 1000);
    btnChange.addEventListener('click', pick);
    btnBuy.addEventListener('click', buy);
  }

  document.addEventListener('DOMContentLoaded', init);
})();


