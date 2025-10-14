(function(global){
  'use strict';
  // Derive project root by trimming after "/frontend/" if present
  (function ensurePath(){ if (!location.pathname.endsWith('/')) return; })();
  const parts = location.pathname.split('/frontend/');
  const rootPath = parts.length > 1 ? (parts[0].endsWith('/') ? parts[0] : parts[0] + '/') : '/';
  const BASE = rootPath + 'backend/api';

  function authHeader() {
    const auth = Utils.load('auth', null);
    return auth && auth.token ? { 'Authorization': 'Bearer ' + auth.token } : {};
  }

  async function request(path, opts={}){
    const headers = Object.assign({ 'Content-Type': 'application/json' }, authHeader(), opts.headers || {});
    const res = await fetch(BASE + path, Object.assign({}, opts, { headers }));
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Request failed');
    return json.data;
  }

  const API = {
    // Auth
    async login(username, password){
      const data = await request('/auth.php?action=login', { method: 'POST', body: JSON.stringify({ username, password }) });
      Utils.save('auth', data);
      return data;
    },
    async logout(){
      try { await request('/auth.php?action=logout', { method: 'POST' }); } catch(_) {}
      Utils.save('auth', null);
    },
    getAuth(){ return Utils.load('auth', null); },

    // Sims
    listSims(){ return request('/sims.php'); },
    addSim(payload){ return request('/sims.php', { method: 'POST', body: JSON.stringify(payload) }); },
    deleteSim(id){ return request('/sims.php', { method: 'DELETE', body: JSON.stringify({ id }) }); },

    // Orders
    listOrders(){ return request('/orders.php'); },
    createOrder(sim_id, contact){ 
      const payload = Object.assign({ sim_id }, contact || {});
      return request('/orders.php', { method: 'POST', body: JSON.stringify(payload) }); 
    },
    updateOrderStatus(id, status){ return request('/orders.php', { method: 'PUT', body: JSON.stringify({ id, status }) }); },
    cancelOrder(id){ return request('/orders.php', { method: 'DELETE', body: JSON.stringify({ id }) }); }
  };

  global.API = API;
})(window);
