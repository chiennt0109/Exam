<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Tài khoản</title><link rel="stylesheet" href="assets/style.css"></head><body><div id="nav" class="nav"></div><div class="container"><h2>Tài khoản và phân quyền</h2>
<input id="username" placeholder="Username"><input id="password" placeholder="Password"><input id="full_name" placeholder="Họ tên"><select id="role"><option value="admin">admin</option><option value="exam_manager">exam_manager</option><option value="score_input">score_input</option></select><button onclick="createUser()">Tạo tài khoản</button>
<table id="tb"></table><h3>Gán quyền riêng</h3><div id="perm"></div></div>
<script src="assets/app.js"></script><script>
let selectedUser=0,perms=[];
(async()=>{await requireAuth(); perms=await (await fetch('api/users.php?resource=permissions')).json(); load();})();
async function load(){const d=await (await fetch('api/users.php')).json();tb.innerHTML='<tr><th>ID</th><th>User</th><th>Họ tên</th><th>Vai trò</th><th>Active</th><th></th></tr>'+d.map(x=>`<tr><td>${x.id}</td><td>${x.username}</td><td>${x.full_name}</td><td>${x.role}</td><td>${x.is_active}</td><td><button onclick='openPerm(${x.id})'>Phân quyền</button></td></tr>`).join('');}
async function createUser(){await fetch('api/users.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({username:username.value,password:password.value,full_name:full_name.value,role:role.value,is_active:1})});load();}
function openPerm(id){selectedUser=id;perm.innerHTML=perms.map(p=>`<label><input type='checkbox' value='${p.code}' checked> + ${p.code}</label><br><label><input type='checkbox' value='${p.code}' data-deny='1'> - ${p.code}</label><br>`).join('')+"<button onclick='savePerm()'>Lưu quyền</button>";}
async function savePerm(){const items=[...perm.querySelectorAll('input:checked')].map(i=>({permission_code:i.value,granted:i.dataset.deny?0:1}));await fetch('api/users.php',{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({mode:'permissions',user_id:selectedUser,items})});alert('Đã lưu quyền');}
</script></body></html>
