(function(){
  'use strict';
  const form = document.getElementById('form-login');
  const msg = document.getElementById('msg');

  async function onSubmit(e){
    e.preventDefault();
    msg.textContent = '';
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    try {
      const data = await API.login(username, password);
      const role = data.user.role;
      if (role === 'admin') location.href = './admin.html';
      else location.href = './customer.html';
    } catch (err) {
      msg.textContent = err.message;
    }
  }

  form.addEventListener('submit', onSubmit);
})();
