(function(){
  'use strict';
  function renderHeader(){
    var wrap = document.createElement('div');
    wrap.innerHTML = '<nav class="topbar">'
      + '<div class="brand"><a href="./index.html">SimMobifone</a></div>'
      + '<div class="links">'
      +   '<a href="./public.html">Danh sach</a>'
      +   '<a href="./deal.html">Deal</a>'
      +   '<a href="./login.html" id="nav-login">Dang nhap</a>'
      +   '<a href="./admin.html" id="nav-admin" style="display:none">Admin</a>'
      +   '<a href="./customer.html" id="nav-customer" style="display:none">Khach</a>'
      +   '<button id="nav-logout" class="link" style="display:none">Dang xuat</button>'
      + '</div>'
    + '</nav>';
    document.body.insertBefore(wrap.firstChild, document.body.firstChild);

    try {
      var auth = window.API ? API.getAuth() : null;
      var isAdmin = auth && auth.user && auth.user.role === 'admin';
      var isCustomer = auth && auth.user && auth.user.role === 'customer';
      document.getElementById('nav-login').style.display = auth ? 'none' : '';
      document.getElementById('nav-admin').style.display = isAdmin ? '' : 'none';
      document.getElementById('nav-customer').style.display = isCustomer ? '' : 'none';
      document.getElementById('nav-logout').style.display = auth ? '' : 'none';
      var logoutBtn = document.getElementById('nav-logout');
      if (logoutBtn) logoutBtn.addEventListener('click', async function(){ await API.logout(); location.href = './login.html'; });
    } catch(_) {}
  }

  document.addEventListener('DOMContentLoaded', renderHeader);
})();


