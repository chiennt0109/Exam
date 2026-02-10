<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Đăng nhập</title><link rel="stylesheet" href="assets/style.css"></head>
<body><div class="container" style="max-width:520px"><h2>Đăng nhập hệ thống</h2>
<input id="u" placeholder="Tên đăng nhập"><input id="p" type="password" placeholder="Mật khẩu">
<button onclick="login()">Đăng nhập</button>
<p>Tài khoản mẫu: admin/admin123, qlthi/manager123, nhapdiem/input123</p>
<p>Kiểm tra nhanh CSDL: <a href="api/auth.php?action=health" target="_blank">api/auth.php?action=health</a></p>
<p id="msg" style="white-space:pre-wrap;color:#b00020"></p></div>
<script>
async function login(){
  msg.textContent='';
  try{
    const r=await fetch('api/auth.php?action=login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({username:u.value,password:p.value})});
    const raw=await r.text();
    let d={};
    try{d=JSON.parse(raw);}catch{}
    if(!r.ok){
      msg.textContent=(d.error||raw||('HTTP '+r.status));
      return;
    }
    location='dashboard.php';
  }catch(err){
    msg.textContent='Không kết nối được API: '+err.message;
  }
}
</script></body></html>
