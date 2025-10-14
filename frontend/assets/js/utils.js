(function(global){
  'use strict';
  function save(key, value){ localStorage.setItem(key, JSON.stringify(value)); }
  function load(key, fallback){ try { const v = JSON.parse(localStorage.getItem(key)); return v ?? fallback; } catch(_) { return fallback; } }
  function currency(v){ try { return new Intl.NumberFormat('vi-VN').format(v); } catch(_) { return v; } }
  function escapeHtml(s){ return (s+"").replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'", '&#39;'); }
  global.Utils = { save, load, currency, escapeHtml };
})(window);
