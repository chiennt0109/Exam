<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Đăng nhập</title><link rel="stylesheet" href="assets/style.css"></head>
<body><div class="container" style="max-width:420px"><h2>Đăng nhập hệ thống</h2>
<input id="u" placeholder="Tên đăng nhập"><input id="p" type="password" placeholder="Mật khẩu">
<button onclick="login()">Đăng nhập</button>
<p>Tài khoản mẫu: admin/admin123, qlthi/manager123, nhapdiem/input123</p><p id="msg"></p></div>
<script>
async function login(){const r=await fetch('api/auth.php?action=login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({username:u.value,password:p.value})});const d=await r.json();if(!r.ok){msg.textContent=d.error;return;}location='dashboard.php';}
</script></body></html>
