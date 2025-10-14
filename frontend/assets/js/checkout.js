(function(){
  'use strict';
  const params = new URLSearchParams(location.search);
  const simId = params.get('simId');
  const sumSo = document.getElementById('sum-so');
  const sumGia = document.getElementById('sum-gia');
  const form = document.getElementById('form-co');
  const msg = document.getElementById('msg');
  let sim = null;

  async function load(){
    if (!simId){ msg.textContent = 'Thieu simId'; return; }
    const sims = await API.listSims();
    sim = sims.find(s => s.id === simId);
    if (!sim){ msg.textContent = 'Khong tim thay sim'; return; }
    sumSo.textContent = Utils.escapeHtml(sim.so);
    sumGia.textContent = Utils.currency(sim.gia);
  }

  async function onSubmit(e){
    e.preventDefault();
    msg.textContent = '';
    const auth = API.getAuth();
    if (!auth){ msg.textContent = 'Vui long dang nhap'; return; }
    const contact_name = document.getElementById('contact_name').value.trim();
    const contact_phone = document.getElementById('contact_phone').value.trim();
    const contact_address = document.getElementById('contact_address').value.trim();
    try {
      await API.createOrder(simId, { contact_name, contact_phone, contact_address });
      msg.textContent = 'Dat hang thanh cong!';
    } catch(err){ msg.textContent = err.message; }
  }

  function init(){
    load();
    form.addEventListener('submit', onSubmit);
  }
  document.addEventListener('DOMContentLoaded', init);
})();


