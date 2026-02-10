<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Tài khoản</title><link rel="stylesheet" href="assets/style.css"></head><body><div id="nav" class="nav"></div><div class="container"><h2>Tài khoản và phân quyền</h2>
<input id="username" placeholder="Username"><input id="password" placeholder="Password"><input id="full_name" placeholder="Họ tên"><select id="role"><option value="admin">admin</option><option value="exam_manager">exam_manager</option><option value="score_input">score_input</option></select><button onclick="createUser()">Tạo tài khoản</button>
<table id="tb"></table><h3>Gán quyền riêng</h3><div id="perm"></div>
<h3>Phân công tài khoản nhập điểm (theo MON.xml + thành phần)</h3>
<div>
  <select id="assign_user"></select>
  <select id="assign_mon"></select>
  <input id="assign_component" placeholder="Thành phần điểm (VD: Tong, TracNghiem...)" value="Tong">
  <button onclick="addAssignment()">Thêm phân công</button>
</div>
<table id="assign_tb"></table>
</div>
<script src="assets/app.js"></script><script>
let selectedUser=0, perms=[], subjects=[];
(async()=>{
  await requireAuth();
  perms=await (await fetch('api/users.php?resource=permissions')).json();
  subjects=await (await fetch('api/users.php?resource=subject_options')).json();
  assign_mon.innerHTML=subjects.map(s=>`<option value='${s.ma_mon}'>${s.ma_mon} - ${s.ten_mon}</option>`).join('');
  load();
})();
async function load(){
  const d=await (await fetch('api/users.php')).json();
  const scoreUsers=d.filter(x=>x.role==='score_input');
  assign_user.innerHTML=scoreUsers.map(x=>`<option value='${x.id}'>${x.username} - ${x.full_name}</option>`).join('');
  tb.innerHTML='<tr><th>ID</th><th>User</th><th>Họ tên</th><th>Vai trò</th><th>Active</th><th></th></tr>'+d.map(x=>`<tr><td>${x.id}</td><td>${x.username}</td><td>${x.full_name}</td><td>${x.role}</td><td>${x.is_active}</td><td><button onclick='openPerm(${x.id})'>Phân quyền</button></td></tr>`).join('');
  loadAssignments();
}
async function createUser(){await fetch('api/users.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({username:username.value,password:password.value,full_name:full_name.value,role:role.value,is_active:1})});load();}
function openPerm(id){selectedUser=id;perm.innerHTML=perms.map(p=>`<label><input type='checkbox' value='${p.code}' checked> + ${p.code}</label><br><label><input type='checkbox' value='${p.code}' data-deny='1'> - ${p.code}</label><br>`).join('')+"<button onclick='savePerm()'>Lưu quyền</button>";}
async function savePerm(){const items=[...perm.querySelectorAll('input:checked')].map(i=>({permission_code:i.value,granted:i.dataset.deny?0:1}));await fetch('api/users.php',{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({mode:'permissions',user_id:selectedUser,items})});alert('Đã lưu quyền');}
async function addAssignment(){
  await fetch('api/users.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({mode:'score_assignment',user_id:+assign_user.value,ma_mon:assign_mon.value,component:assign_component.value})});
  loadAssignments();
}
async function loadAssignments(){
  if(!assign_user.value){assign_tb.innerHTML='';return;}
  const d=await (await fetch('api/users.php?resource=score_assignments&user_id='+assign_user.value)).json();
  assign_tb.innerHTML='<tr><th>ID</th><th>Môn</th><th>Thành phần</th><th></th></tr>'+
    d.map(x=>`<tr><td>${x.id}</td><td>${x.ma_mon}</td><td>${x.component}</td><td><button onclick='delAssignment(${x.id})'>Xoá tạm</button></td></tr>`).join('');
}
assign_user.onchange=loadAssignments;
async function delAssignment(id){
  await fetch('api/users.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({mode:'score_assignment',id})});
  loadAssignments();
}
</script></body></html>
